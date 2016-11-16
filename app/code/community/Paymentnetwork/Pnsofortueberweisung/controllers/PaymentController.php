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
class Paymentnetwork_Pnsofortueberweisung_PaymentController extends Mage_Core_Controller_Front_Action
{
    /**
     * Order instance
     * @var Mage_Sales_Model_Order
     */
    private $_order;
    
    /**
     * Order comments
     * @var string
     */
    private $_commentMessages = array(
        'redirect' => 'Redirection to SOFORT Banking. Transaction not finished. Transaction ID: [[transaction_id]]. Time: [[date]]',
        'abort' => 'Payment aborted. Time: %s',
        'pending_not_credited_yet' => 'SOFORT Banking has been completed successfully - Transaction ID: [[transaction_id]]. Time: [[date]]',
        'untraceable_sofort_bank_account_needed' => 'SOFORT Banking has been completed successfully - Transaction ID: [[transaction_id]]. Time: [[date]]',
        'loss_not_credited' => 'The payment has not been received on your SOFORT Bank account. Please verify the payment. Time: [[date]]',
        'received_credited' => 'The payment has been received on your SOFORT Bank account. Time: [[date]]',
        'received_partially_credited' => 'An amount differing from the order has been received on your SOFORT Bank account. Please verify the payment. Time: [[date]]',
        'received_overpayment' => 'An amount differing from the order has been received on your SOFORT Bank account. Please verify the payment. Time: [[date]]',
        'refunded_compensation' => 'Partial amount will be refunded - [[refunded_amount]]. Time: [[date]]',
        'refunded_refunded' => 'Amount will be refunded. Time: [[date]]'
    );
    
    public function redirectAction()
    {
        
        $comment = Mage::helper('sofort')->__($this->_commentMessages['redirect']);
        $comment = str_replace('[[date]]', date('d.m.Y H:i:s', Mage::getModel('core/date')->timestamp(time())), $comment);
        $comment = str_replace(
            '[[transaction_id]]', 
            $this->_getOrder()->getPayment()->getAdditionalInformation('sofort_transaction_id'), 
            $comment
        );
        
        $this->_getOrder()->addStatusHistoryComment($comment);
        
        $this->_getOrder()->save();
        
        $this->_redirectUrl(Mage::getSingleton('customer/session')->getPaymentUrl());
    }
    
    /**
     * Sofort success url
     */
    public function successAction()
    {
        Mage::getModel('sales/quote')->load($this->_getOrder()->getQuoteId())->setIsActive(false)->save();
        
        if($this->_getSendOrderConfirmationOption()) {
            try {
                $this->_getOrder()->sendNewOrderEmail();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        
        $this->_redirect('checkout/onepage/success/');
    }

    /**
     * Sofort abort url
     */
    public function abortAction()
    {
        $this->_getOrder()->cancel();
        $this->_getOrder()->addStatusHistoryComment(
            sprintf(Mage::helper('sofort')->__($this->_commentMessages['abort']), date('d.m.Y H:i:s', Mage::getModel('core/date')->timestamp(time())))
        );
        
        $this->_getOrder()->save();
        
        $this->_redirect('checkout/cart/');
    }

    /**
     * Sofort notification url
     */
    public function notificationAction()
    {
        $communication = Mage::getModel(
            'Paymentnetwork_Pnsofortueberweisung_Model_Service_Communication'
        );
                
        $statusData = $communication->getStatusData(file_get_contents('php://input'));
        
        $this->_handleSofortStatusUpdate($statusData);
    }
    
    /**
     * Get create invoice option
     * @return string
     */
    private function _getCreateInvoiceOption()
    {
        return Mage::getStoreConfig('payment/paymentnetwork_pnsofortueberweisung/create_invoice', Mage::app()->getStore()->getStoreId());
    }
    
    /**
     * Get send order confirmation option
     * @return boolean
     */
    private function _getSendOrderConfirmationOption()
    {
        return (boolean) Mage::getStoreConfig('payment/paymentnetwork_pnsofortueberweisung/send_order_confirmation', Mage::app()->getStore()->getStoreId());
    }
    
    /**
     * Get create creditmemo flag
     * 
     * @return boolean
     */
    private function _getCreateCreditmemoOption()
    {
        return (boolean) Mage::getStoreConfig('payment/paymentnetwork_pnsofortueberweisung/create_creditmemo', Mage::app()->getStore()->getStoreId());
    }
    
    /**
     * Generates the invoice for the current order
     */
    private function _generateInvoice()
    {
        $order = $this->_getOrder();
        if ($order->canInvoice()) {
            $invoice = $order->prepareInvoice();

            $invoice->register();
            Mage::getModel('core/resource_transaction')
               ->addObject($invoice)
               ->addObject($invoice->getOrder())
               ->save();
            
            $invoice->pay()->save();

            $order->save();

            $invoice->sendEmail(
                (boolean) Mage::getStoreConfig(
                    'payment/paymentnetwork_pnsofortueberweisung/send_mail', 
                    Mage::app()->getStore()->getStoreId()
                ), ''
            );
        }
    }
    
    /**
     * Generates the creditmemo for the current order
     */
    private function _generateCreditmemo()
    {
        $order = $this->_getOrder();
        
        $this->_generateInvoice();
        
        if ($order->canCreditmemo()) {
            foreach ($order->getInvoiceCollection() as $invoice) {
                $creditmemo = Mage::getModel('sales/service_order', $order)->prepareInvoiceCreditmemo($invoice);
                $creditmemo->register();
                
                Mage::getModel('core/resource_transaction')
                   ->addObject($creditmemo)
                   ->addObject($creditmemo->getOrder())
                   ->save();
                
                $creditmemo->save();
                
                $creditmemo->sendEmail(
                    (boolean) Mage::getStoreConfig(
                        'payment/paymentnetwork_pnsofortueberweisung/send_mail', 
                        Mage::app()->getStore()->getStoreId()
                    ), ''
                );
            }
        }
    }
    
    /**
     * Create the invoice when configured
     * 
     * @param array $statusData
     */
    private function _handleInvoiceCreation(array $statusData)
    {
        if ($statusData['status'] === 'pending' 
                && $statusData['reason'] === 'not_credited_yet' 
                && $this->_getCreateInvoiceOption() === 'after_order') {
            $this->_generateInvoice();
        }

        if ($statusData['status'] === 'received' 
                && $statusData['reason'] === 'credited' 
                && $this->_getCreateInvoiceOption() === 'after_credited') {
            $this->_generateInvoice();
        }
    }
    
    /**
     * Create the creditmemo when configured
     * 
     * @param array $statusData
     */
    private function _handleCreditmemoCreation(array $statusData)
    {
        if ($statusData['status'] === 'refunded' 
                && $statusData['reason'] === 'refunded' 
                && $this->_getCreateCreditmemoOption()) {
            $this->_generateCreditmemo();
        }
    }
    
    /**
     * Create the creditmemo or invoice when configured
     * 
     * @param array $statusData
     */
    private function _handleDocumentCreation(array $statusData)
    {
        $this->_handleInvoiceCreation($statusData);
        $this->_handleCreditmemoCreation($statusData);
    }


    /**
     * Update magento order status
     * 
     * @param array $statusData
     */
    private function _handleSofortStatusUpdate(array $statusData)
    {
                
        $allowedStates = array(
            'loss' => array('not_credited'), 
            'pending' => array('not_credited_yet'), 
            'received' => array('credited'), 
            'refunded' => array('refunded'), 
            'untraceable' => array('sofort_bank_account_needed')
        );
        
        if (array_key_exists($statusData['status'], $allowedStates) 
            && in_array($statusData['reason'], $allowedStates[$statusData['status']])) {
            
            if ($statusData['status'] === 'untraceable' && $statusData['reason'] === 'sofort_bank_account_needed') {
                $statusData['status'] = 'pending';
                $statusData['reason'] = 'not_credited_yet';
            }
            
            $status = $statusData['status'];
            $reason = $statusData['reason'];
            
            $selectedStatus = Mage::getStoreConfig(
                'payment/paymentnetwork_pnsofortueberweisung/order_status_' . $status . '_' . $reason, 
                Mage::app()->getStore()->getStoreId()
            );
                        
            if (!empty($selectedStatus)) {
                $this->_handleMagentoStatusUpdate($statusData, $selectedStatus);
            } else {
                $this->_getOrder()->addStatusHistoryComment($this->_getHistoryComment($statusData))->save();
            }
            
            $this->_handleDocumentCreation($statusData);
        } else {
            $this->_getOrder()->addStatusHistoryComment($this->_getHistoryComment($statusData))->save();
        }
    }
    
    /**
     * Set magento state status and history comment
     * 
     * @param array $statusData
     * @param string $status
     */
    private function _handleMagentoStatusUpdate(array $statusData, $status)
    {        
        $comment = $this->_getHistoryComment($statusData);
        
        $state = Mage::getResourceModel('sales/order_status_collection')
                                ->joinStates()->addStatusFilter($status)
                                ->getFirstItem()
                                ->getData('state');
                
        $this->_getOrder()->setStatus($status);
        $this->_getOrder()->setState(
            $state, 
            false,
            $comment, 
            false
        );
        
        $this->_getOrder()->addStatusHistoryComment($this->_getHistoryComment($statusData));
        
        $this->_getOrder()->save();
    }
    
    /**
     * Get dynamic sofort history comment
     * 
     * @param array $statusData
     * @return string
     */
    private function _getHistoryComment(array $statusData)
    {
        $comment = 'Status message not defined.';
        $commentKey = $statusData['status'] . '_' . $statusData['reason'];
        
        if (array_key_exists($commentKey, $this->_commentMessages)) {
            $comment = Mage::helper('sofort')->__($this->_commentMessages[$commentKey]);
        }
        
        $comment = str_replace('[[date]]', date('d.m.Y H:i:s', Mage::getModel('core/date')->timestamp(time())), $comment);
        $comment = str_replace('[[transaction_id]]', $statusData['transaction_id'], $comment);
        
        if (array_key_exists('refunded_amount', $statusData)) {
            $comment = str_replace('[[refunded_amount]]', $statusData['refunded_amount'], $comment);
        }
        
        return $comment;
    }
    
    /**
     * Get the magento order
     * 
     * @return Mage_Sales_Model_Order
     * @throws Mage_Core_Exception
     */
    private function _getOrder()
    {
        if (!is_null($this->_order)) {
            return $this->_order;
        }
        
        $orderId = $this->getRequest()->getParam('orderId');
        if (!empty($orderId)) {
            $this->_order = Mage::getSingleton('sales/order')->loadByIncrementId($orderId);
            return $this->_order;
        }
        
        Mage::throwException('Forbidden');
    }

}
