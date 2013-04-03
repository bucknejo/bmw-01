<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SOAPServer
 *
 * @author jb197342
 */
class JEB_SOAP_SOAPServer {
    //put your code here
    
    public function getImages() {       
        
        $table_name = 'images';
        $wheres = array();
        
        $mapper = new Application_Model_TableMapper();
        $images = $mapper->getAll($table_name, $wheres);
        
        return $images;
        
    }
}

?>
