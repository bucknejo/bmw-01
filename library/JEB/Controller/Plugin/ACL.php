<?php

class JEB_Controller_Plugin_ACL extends Zend_Controller_Plugin_Abstract {
    
    

    public function preDispatch(Zend_Controller_Request_Abstract $request) {
            
        try {
                        
            $log = JEB_Lib_Log::get();
            
            $role_id = 5; // set to guest (probably will take this out
            $auth = Zend_Auth::getInstance();
            if ($auth->hasIdentity()) {
                $role_id = $auth->getIdentity()->role_id;
            }
                        
            $acl = JEB_ACL_Factory::get();
            
            if(!$acl->isAllowed($role_id, self::_getResource($request))) {
                $log->logItDb(6, 'Request denied!');
                $log->logItDb(6, 'Role ID: ' . $role_id);
                $log->logItDb(6, self::_getResource($request));
                                    
                $request->setModuleName('admin');
                $request->setControllerName('auth');
                $request->setActionName('logon');                       
                
            } else {                                
                $log->logItDb(6, 'These aren\'t the droids you\'re looking for.  Move along.');                                                
            }
            
        } catch (Exception $e) {
            
            $message = $e->getMessage();
                        
        }
    }
        
    public static function _getResource($request) {
        
        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        
        return $module."::".$controller."::".$action;
                
    }
    
    public static function _checkSSL($request) {
        
        $isSSL = false;
        
        $config = Zend_Registry::get('config');            
        $ssl = $config->ssl->switch;        

        $log = JEB_Lib_Log::get();

        if (APPLICATION_ENV == 'production' && $ssl) {

            $log->logItDb(6, "SSL 1");

            $modules = explode("|", $config->ssl->modules);            
            $module = $request->getModuleName();

            $log->logItDb(6, "SSL 2");
            $log->logItDb(6, "SSL: $module");

            if (in_array($module, $modules)) {
                $isSSL = true;
            };

        }        
        
        return $isSSL;
                
    }

}

?>
