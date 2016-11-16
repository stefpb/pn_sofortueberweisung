<?php


class Paymentnetwork_Pnsofortueberweisung_Block_Adminhtml_Payment_Config_Heading_Big
    extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Render element html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $useContainerId = $element->getData('use_container_id');
        return sprintf('<tr class="system-fieldset-sub-head" id="row_%s"><td colspan="5"><h2 id="%s">%s</h2></td></tr>',
            $element->getHtmlId(), $element->getHtmlId(), $element->getLabel()
        );
    }
}
