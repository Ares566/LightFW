<?php

/**
 * Class View
 *
 * create view from template
 * User: Renat Abaidulin
 */
class View {
    
    private $_tvars = array();
    function __construct() {
        //DO nothing
    }

    public function  __set($name, $value) {
        $this->_tvars[$name] = $value;
    }

    public function  __get($name) {
        return array_key_exists($name, $this->_tvars)?$this->_tvars[$name]:'';
    }

    public function render($tpl){
        if($tpl == '')return;
        if(!file_exists($tpl))return;
        ob_start(); 
        include $tpl;
        return ob_get_clean();
        
    }
}
?>
