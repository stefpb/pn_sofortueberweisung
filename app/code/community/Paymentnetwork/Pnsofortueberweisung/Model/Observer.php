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
class Paymentnetwork_Pnsofortueberweisung_Model_Observer
{

    /**
     * Reactivate the cart because the order isn't finished
     * 
     * @param Varien_Event_Observer $observer 
     */
    public function refillBasket(Varien_Event_Observer $observer)
    {
        if ($observer->getEvent()->getQuote()->getPayment()->getMethod() === 'paymentnetwork_pnsofortueberweisung') {
            $observer->getEvent()->getQuote()->setIsActive(true)->save();
        }
    }
}