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
class Paymentnetwork_Pnsofortueberweisung_Model_Source_Order_Invoice
{
    /**
     * Define which Creditcard Logos are shown for payment
     *
     * @return array
     */
    public function toOptionArray()
    {
        $presentationMethods = array(
            array(
                'label' => Mage::helper('core')->__('After finishing the payment'),
                'value' => 'after_order'
            ),
            array(
                'label' => Mage::helper('core')->__('The receipt of the money ( can be only detected with an account with SOFORT Bank)'),
                'value' => 'after_credited'
            ),
            array(
                'label' => Mage::helper('core')->__('Not create invoice'),
                'value' => 'no_invoice'
            ),
        );
        
        return $presentationMethods;
    }
}