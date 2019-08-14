<?php
class Lister_CookieThemer_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * @param cookiename
     * @return cookie value
     */
    public function getCookieValue($cookie_name){
        $cookie_value =  Mage::getModel('core/cookie')->get($cookie_name);
        return $cookie_value;
    }
    
    
   
}





