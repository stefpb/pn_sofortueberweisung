<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category PayIntelligent
 * @package Paintelligent_Sofort
 * @copyright Copyright (c) 2014 PayIntelligent GmbH (http://www.payintelligent.de)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
require_once(Mage::getBaseDir('lib') . '/Sofort/payment/sofortLibSofortueberweisung.inc.php');
require_once(Mage::getBaseDir('lib') . '/Sofort/core/sofortLibNotification.inc.php');
require_once(Mage::getBaseDir('lib') . '/Sofort/core/sofortLibTransactionData.inc.php');

class Paymentnetwork_Pnsofortueberweisung_Model_Service_Communication
{

    /**
     * Sofort sdk instance
     * 
     * @var SofortLib_SofortueberweisungClassic 
     */
    protected $_sofortSdk;

    /**
     * Magento quote instance
     * 
     * @var Mage_Sales_Model_Quote
     */
    protected $_quote;
    
    /**
     * Sofort transaction id
     * 
     * @var string
     */
    protected $_transactionId;

    /**
     * Initialize dependecys (sofort sdk)
     */
    public function __construct()
    {
        $this->_sofortSdk = new Sofortueberweisung(
            Mage::getStoreConfig(
                'payment/paymentnetwork_pnsofortueberweisung/cofiguration_key', 
                Mage::app()->getStore()->getStoreId()
            )
        );
    }

    /**
     * Get payment url
     * 
     * @throws Mage_Core_Exception
     * @return string
     */
    public function getUrl()
    {
        if ($this->_sofortSdk->isError()) {
            Mage::throwException($this->_sofortSdk->getError());
        }
        
        return $this->_sofortSdk->getPaymentUrl();
    }
    
    /**
     * Get sofort transaction id
     * 
     * @return string
     */
    public function getTransactionId()
    {
        if (is_null($this->_transactionId)) {
            $this->_transactionId = $this->_sofortSdk->getTransactionId();
        }
        
        return $this->_transactionId;
    }
    
    /**
     * Get sofort status with reason
     * 
     * @return array
     */
    public function getStatusData($rawBody)
    {
        $transactionData = array('status' => 'undefined', 'reason' => 'undefined');
        
        $notificationSdk = new SofortLibNotification();
        
        $transactionId = $notificationSdk->getNotification($rawBody);
        
        if ($transactionId) {
            $transactionDataSdk = new SofortLibTransactionData(            
                Mage::getStoreConfig(
                    'payment/paymentnetwork_pnsofortueberweisung/cofiguration_key', 
                    Mage::app()->getStore()->getStoreId()
                )
            );
            
            $transactionDataSdk->addTransaction($transactionId)->sendRequest();
            
            $transactionData['status'] = $transactionDataSdk->getStatus();
            $transactionData['reason'] = $transactionDataSdk->getStatusReason();
            $transactionData['amount_refunded'] = $transactionDataSdk->getAmountRefunded();
            $transactionData['transaction_id'] = $transactionId;
        }
        
        return $transactionData;
    }

    /**
     * Executes the pay request to sofort
     */
    public function paymentRequest()
    {
        $orderId = $this->_getQuote()->getReservedOrderId();
        
        $this->_sofortSdk->setVersion('magento_3.0.7');
        $this->_sofortSdk->setAmount(Mage::app()->getStore()->roundPrice($this->_getQuote()->getGrandTotal()));
        $this->_sofortSdk->setCurrencyCode($this->_getQuote()->getBaseCurrencyCode());
        $this->_sofortSdk->setReason($this->_getReasonOne(), $this->_getReasonTwo());
        $this->_sofortSdk->setSuccessUrl(Mage::getUrl('pisofort/payment/success', array('orderId' => $orderId)), true);
        $this->_sofortSdk->setAbortUrl(Mage::getUrl('pisofort/payment/abort', array('orderId' => $orderId)));
        $this->_sofortSdk->setNotificationUrl(Mage::getUrl('pisofort/payment/notification', array('orderId' => $orderId)));
        $this->_sofortSdk->setCustomerprotection(
            (boolean) Mage::getStoreConfig(
                'payment/paymentnetwork_pnsofortueberweisung/customer_protection', 
                Mage::app()->getStore()->getStoreId()
            )
        );
        
        $this->_sofortSdk->sendRequest();
    }
    
    /**
     * Get reason one
     * 
     * @retun string
     */
    private function _getReasonOne()
    {
        return $this->_replaceReasonPlaceHolder(Mage::getStoreConfig(
            'payment/paymentnetwork_pnsofortueberweisung/usage_text_one', 
            Mage::app()->getStore()->getStoreId()
        ));
    }
    
    /**
     * Get reason two
     * 
     * @return string
     */
    private function _getReasonTwo()
    {
        return $this->_replaceReasonPlaceHolder(Mage::getStoreConfig(
            'payment/paymentnetwork_pnsofortueberweisung/usage_text_two', 
            Mage::app()->getStore()->getStoreId()
        ));
    }
    
    /**
     * Replace the placeholders and get reason
     * 
     * @param string $reason
     * @return string
     */
    private function _replaceReasonPlaceHolder($reason)
    {
        $replaceData = array(
            '{{orderid}}' => $this->_getQuote()->getReservedOrderId(),
            '{{name}}' => $this->_getQuote()->getBillingAddress()->getFirstname() . ' ' . $this->_getQuote()->getBillingAddress()->getLastname(),
            '{{date}}' => date('d.m.Y H:i:s', Mage::getModel('core/date')->timestamp(time())),
            '{{shopname}}' => Mage::app()->getStore()->getName(),
            '{{transaction}}' => '-TRANSACTION-'
            
        ); 
        
        $reason = str_replace('{{orderid}}', $replaceData['{{orderid}}'], $reason);
        $reason = str_replace('{{name}}', $replaceData['{{name}}'], $reason);
        $reason = str_replace('{{date}}', $replaceData['{{date}}'], $reason);
        $reason = str_replace('{{shopname}}', $replaceData['{{shopname}}'], $reason);
        $reason = str_replace('{{transaction}}', $replaceData['{{transaction}}'], $reason);
        
        return $reason;
    }
    
    /**
     * Get quote instance
     * 
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        if (is_null($this->_quote)) {
            $this->_quote = Mage::getSingleton('checkout/session')->getQuote();
        }
        
        return $this->_quote;
    }

}
