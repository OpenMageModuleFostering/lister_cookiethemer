<?php
class Lister_CookieThemer_Block_Adminhtml_System_Config_Form_Field_Cookieexceptions extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn('cookienv', array(
            'label' => Mage::helper('adminhtml')->__('Cookie Name Value'),
            'style' => 'width:120px',
        ));
        $this->addColumn('value', array(
            'label' => Mage::helper('adminhtml')->__('Theme Name'),
            'style' => 'width:120px',
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Cookie Exception');
        parent::__construct();
    }
}
