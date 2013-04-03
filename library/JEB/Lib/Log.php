<?php

/*

EMERG   = 0;  // Emergency: system is unusable
ALERT   = 1;  // Alert: action must be taken immediately
CRIT    = 2;  // Critical: critical conditions
ERR     = 3;  // Error: error conditions
WARN    = 4;  // Warning: warning conditions
NOTICE  = 5;  // Notice: normal but significant condition
INFO    = 6;  // Informational: informational messages
DEBUG   = 7;  // Debug: debug messages

 */

class JEB_Lib_Log {
    
    private static $instance;
    
    private function __construct() {
        
    }
    
    public static function get() {
        
        if(!isset(self::$instance)) {
            self::$instance = new JEB_Lib_Log();
        }
        
        return self::$instance;
    }

    public function logItStream($file_type, $priority, $message) {

        $stream = fopen('test.log', $file_type, false);

        $writer = new Zend_Log_Writer_Stream($stream);
        $logger = new Zend_Log($writer);
        $logger->log($message, $priority);
        $logger = null;

        return $lines = file('test.log');
        
    }

    public function logItDb($priority, $message) {

        $exception = '';
        $j = 0;
        try {

            $front = Zend_Controller_Front::getInstance();
            $request = $front->getRequest();

            $action = $request->getParam('action');
            $controller =  $request->getParam('controller');
            $module =  $request->getParam('module');
            
            $auth = Zend_Auth::getInstance();
            if ($auth->hasIdentity()) {
                $user = $auth->getIdentity()->user_id;
            } else {
                $user = "system";
            }

            $db = Zend_Db_Table::getDefaultAdapter();
            $table = 'log';
            $columns = array(
                'date_created' => 'date_created',
                'last_updated' => 'last_updated',
                'user' => 'user',
                'active' => 'active',
                'message' => 'message',
                'level_code' => 'priority',
                'level_name' => 'priorityName',
                'action' => 'action',
                'controller' => 'controller',
                'module' => 'module'
            );

            $writer = new Zend_Log_Writer_Db($db, $table, $columns);
            $logger = new Zend_Log($writer);
            $logger->setEventItem('date_created', new Zend_Db_Expr('CURDATE()'));
            $logger->setEventItem('last_updated', new Zend_Db_Expr('NOW()'));
            $logger->setEventItem('user', 'system');
            $logger->setEventItem('active', 1);
            $logger->setEventItem('action', $action);
            $logger->setEventItem('controller', $controller);
            $logger->setEventItem('module', $module);
            $logger->log($message, $priority);
            $logger = null;


        } catch (Exception $e) {
            $exception = $e->getMessage();
            $j = 1;
        }

        return $j;

    }


    protected function _getWriter($type) {

        $writer = array();

        

    }

    protected function _getTimestampFormat() {

    }

    protected function _showArray($config) {

        $file = $config->logger->file;

        return $file;

    }
    
    public function __clone() {
        // NOT ALLOWED IN SINGLETON
    }
    
    public function __wakeup() {
        // NOT ALLOWED IN SINGLETON (Unserialize)
    }


    
}
?>
