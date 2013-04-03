<?php

class JEB_SSL_Plugin_SSL extends Zend_Controller_Plugin_Abstract {
    
    public function preDispatch(Zend_Controller_Request_Abstract $request) {
        
        try {
            
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

                    $server = $request->getServer();
                    $hostname = $server['HTTP_HOST'];

                    $url = Zend_Controller_Request_Http::SCHEME_HTTPS . '://' . $hostname . $request->getPathInfo();

                    $log->logItDb(6, "SSL: $server");
                    $log->logItDb(6, "SSL: $hostname");
                    $log->logItDb(6, "SSL: $url");
                    
                    $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
                    $redirector->setGoToUrl($url);
                    $redirector->redirectAndExit();

                };
                
            }
                        
        } catch (Exception $e) {

        }
                
    }
    

}

?>
