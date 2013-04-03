<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AccommodationsAction
 *
 * @author jb197342
 */
class AccommodationsController extends Zend_Controller_Action {
    //put your code here
    
    public function init() {
        
    }
    
    public function indexAction() {
        
        $auth = Zend_Auth::getInstance();
        
        if ($auth->hasIdentity()) {
            
            $id = $auth->getIdentity()->id;        

            $table_name = 'users';
            $mapper = new Application_Model_TableMapper();

            if ($this->getRequest()->isPost()) {

                $checkInDate = $this->_getParam("checkInDate");
                $checkOutDate = $this->_getParam("checkOutDate");
                                                
                $ci = explode("-", $checkInDate);
                $co = explode("-", $checkOutDate);
                
                $checkInDate = $ci[2] ."-". $ci[0] ."-".$ci[1];
                $checkOutDate = $co[2] ."-". $co[0] ."-".$co[1];                                

                $earlyCheckIn = $this->_getParam("earlyCheckIn", "No");
                $lateCheckOut = $this->_getParam("lateCheckOut", "No");
                
                $transportation = $this->_getParam("transportation", "");

                ($earlyCheckIn == "on") ? $earlyCheckIn = "Yes" : $earlyCheckIn = "No";
                ($lateCheckOut == "on") ? $lateCheckOut = "Yes" : $lateCheckOut = "No";

                // new items (2012-08-28)
                $arrival_flight_info = $this->_getParam("arrival_flight_info");
                $arrival_flight_time = $this->_getParam("arrival_flight_time");

                $departure_flight_info = $this->_getParam("departure_flight_info");
                $departure_flight_time = $this->_getParam("departure_flight_time");
                
                 $data = array(
                    'check_in_date' => $checkInDate,
                    'check_out_date' => $checkOutDate,
                    'early_check_in' => $earlyCheckIn,
                    'late_check_out' => $lateCheckOut,
                    'transportation' => $transportation,
                    'arrival_flight_info' => $arrival_flight_info,
                    'arrival_flight_time' => $arrival_flight_time,
                    'departure_flight_info' => $departure_flight_info,
                    'departure_flight_time' => $departure_flight_time,
                    'registered' => 'Y'
                );

                $int = $mapper->updateItem($table_name, $data, $id);
                
                // redirect to results review
                $this->_helper->redirector('confirmation', 'accommodations', 'default'); 
                

            }

            $users = $mapper->getItemById($table_name, $id);                
            $this->view->users = $users;
            
            $config = Zend_Registry::get('config');
            $transOpts = explode("|", $config->transportation->options);
            $this->view->transOpts = $transOpts;
            
                        
        } else {
            $this->_helper->redirector('index', 'index', 'default');            
        }
        
    }
    
    public function confirmationAction() {
        
        $auth = Zend_Auth::getInstance();
        
        if ($auth->hasIdentity()) {
            
            $id = $auth->getIdentity()->id;
            
            $table_name = 'users';
            $mapper = new Application_Model_TableMapper();

            if ($this->getRequest()->isPost()) {
            }
            
            $users = $mapper->getItemById($table_name, $id);            
            $this->view->users = $users;
                        
            
        } else {
            
        }
        
    }
        
}

?>
