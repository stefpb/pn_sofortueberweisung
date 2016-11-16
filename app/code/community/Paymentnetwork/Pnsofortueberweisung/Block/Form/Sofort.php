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
class Paymentnetwork_Pnsofortueberweisung_Block_Form_Sofort extends Mage_Payment_Block_Form
{
    /**
     * Construct
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('paymentnetwork/sofort/form/sofort.phtml');
    }
    
    /**
     * Check if presentation method is banner
     * 
     * @return boolean
     */
    public function isBanner()
    {
        return (Mage::getStoreConfig('payment/paymentnetwork_pnsofortueberweisung/checkout_presentation') === 'banner');
    }
    
    /**
     * Is customer protection enabled
     * 
     * @return boolean
     */
    public function isCustomerProtectionEnabled()
    {
        return (boolean) Mage::getStoreConfig('payment/paymentnetwork_pnsofortueberweisung/customer_protection');
    }
}
