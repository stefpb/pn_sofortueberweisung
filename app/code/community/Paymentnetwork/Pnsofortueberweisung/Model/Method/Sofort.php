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
class Paymentnetwork_Pnsofortueberweisung_Model_Method_Sofort extends Mage_Payment_Model_Method_Abstract
{

    /**
     * Is gateway
     * 
     * @var boolean 
     */
    protected $_isGateway = true;

    /**
     * Can use the Refund method to refund less than the full amount
     *
     * @var boolean
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * Can use the partial capture method
     *
     * @var boolean
     */
    protected $_canCapturePartial = false;

    /**
     * Can this method use for checkout
     *
     * @var boolean
     */
    protected $_canUseCheckout = true;

    /**
     * Can this method use for multishipping
     *
     * @var boolean
     */
    protected $_canUseForMultishipping = false;

    /**
     * Can use for internal payments
     * 
     * @var boolean
     */
    protected $_canUseInternal = false;
    
    /**
     *
     * @var type 
     */
    protected $_isInitializeNeeded = true;

    /**
     * Magento method code
     *
     * @var string
     */
    protected $_code = 'paymentnetwork_pnsofortueberweisung';

    /**
     * Form block identifier
     *
     * @var string
     */
    protected $_formBlockType = 'Paymentnetwork_Pnsofortueberweisung_Block_Form_Sofort';
    
    /**
     * Info block identifier
     *
     * @var string
     */
    protected $_infoBlockType = 'Paymentnetwork_Pnsofortueberweisung_Block_Info_Sofort';
    
    /**
     * Initulaize the sofort payment and set the redirect url
     * 
     * @param string $paymentAction
     * @param Varien_Object $stateObject
     * @return \Paymentnetwork_Pnsofortueberweisung_Model_Method_Sofort
     */
    public function initialize($paymentAction, $stateObject)
    {
        parent::initialize($paymentAction, $stateObject);
        
        $communication = Mage::getModel(
            'Paymentnetwork_Pnsofortueberweisung_Model_Service_Communication'
        );
        
        $communication->paymentRequest();
        
        $url = $communication->getUrl();
        
        Mage::getSingleton('customer/session')->setPaymentUrl($url);
        
        $this->getInfoInstance()->setAdditionalInformation(
            'sofort_transaction_id', 
            $communication->getTransactionId()
        );
        
        return $this;
    }
    
    /**
     * Retrieve the order place URL
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('pisofort/payment/redirect', array(
                'orderId' => Mage::getSingleton('checkout/session')->getQuote()->getReservedOrderId()
            )
        );
    }

    /**
     * Get payment title
     * 
     * @return string
     */
    public function getTitle()
    {
        return Mage::helper('sofort')->__(parent::getTitle());
    }
    
}
