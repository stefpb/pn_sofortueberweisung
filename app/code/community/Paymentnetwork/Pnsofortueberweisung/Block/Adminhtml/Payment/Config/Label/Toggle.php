<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Toggle
 *
 * @author Christopher
 */
class Paymentnetwork_Pnsofortueberweisung_Block_Adminhtml_Payment_Config_Label_Toggle extends Paymentnetwork_Pnsofortueberweisung_Block_Adminhtml_Payment_Config_Label
{
    /**
     * Render element html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {   
        $script = '$("row_payment_paymentnetwork_pnsofortueberweisung_display_settings_heading").toggle();
                   $("row_payment_paymentnetwork_pnsofortueberweisung_checkout_presentation").toggle();
                   $("row_payment_paymentnetwork_pnsofortueberweisung_customer_protection").toggle();
                   $("row_payment_paymentnetwork_pnsofortueberweisung_allowspecific").toggle();
                   $("row_payment_paymentnetwork_pnsofortueberweisung_specificcountry").toggle();
                   $("row_payment_paymentnetwork_pnsofortueberweisung_sort_order").toggle();
                   $("row_payment_paymentnetwork_pnsofortueberweisung_usage_settings_heading").toggle();
                   $("row_payment_paymentnetwork_pnsofortueberweisung_usage_text_one").toggle();
                   $("row_payment_paymentnetwork_pnsofortueberweisung_usage_text_two").toggle();
                   $("row_payment_paymentnetwork_pnsofortueberweisung_status_settings_heading").toggle();
                   $("row_payment_paymentnetwork_pnsofortueberweisung_order_status").toggle();
                   $("row_payment_paymentnetwork_pnsofortueberweisung_order_status_pending_not_credited_yet").toggle();
                   $("row_payment_paymentnetwork_pnsofortueberweisung_order_status_loss_not_credited").toggle();
                   $("row_payment_paymentnetwork_pnsofortueberweisung_order_status_received_credited").toggle();
                   $("row_payment_paymentnetwork_pnsofortueberweisung_create_invoice").toggle();
                   $("row_payment_paymentnetwork_pnsofortueberweisung_status_config_text_one").toggle();
                   $("row_payment_paymentnetwork_pnsofortueberweisung_status_config_text_two").toggle();
                   $("row_payment_paymentnetwork_pnsofortueberweisung_create_creditmemo").toggle();
                   $("row_payment_paymentnetwork_pnsofortueberweisung_send_order_confirmation").toggle();
                   $("row_payment_paymentnetwork_pnsofortueberweisung_send_mail").toggle();';
        
        $labelText = Mage::helper('sofort')->__("Adjust the SOFORT Banking module properties here. <a onclick='%s'>(Click to open)</a>");
        
        $label = sprintf($labelText, $script);
        
        return sprintf('<tr class="system-fieldset-sub-head" id="row_%s"><td colspan="5"><div id="%s">%s</div></td></tr>',
            $element->getHtmlId(), $element->getHtmlId(), $label . $element->getLabel()
        );
    }
}