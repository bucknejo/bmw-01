<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Error
 *
 * @author jb197342
 */
class JEB_Lib_Exception {
    //put your code here
    
    protected $_html;
    
    public function getHtml() {
        return $this->_html;
    }
    
    public function setHtml($html) {
        $this->_html = $html;
    }
    
    public function __construct($exception, $send=false, $redirect=false) {
        
        $log = JEB_Lib_Log::get();
        $priority = 2;
        $message = "";
        
        try {
            
            // get exception details and log them
            $message = $exception->getMessage();
            $code = $exception->getCode();
            $file = $exception->getFile();
            $line = $exception->getLine();
            
            $log->logItDb($priority, "Exception Message: $message");            
            $log->logItDb($priority, "Exception Code: $code");
            $log->logItDb($priority, "Exception File: $file");
            $log->logItDb($priority, "Exception Line: $line");
                        
            // email common properties            
            if ($send) {
                
                $config = Zend_Registry::get('config');            
                $mail = new JEB_Lib_Mail();

                $mail->setFrom($config->smtp->from);
                $mail->setSubject($config->mail->template->error->subject);

                $mail->message = $exception->getMessage();
                $mail->code = $exception->getCode();
                $mail->file = $exception->getFile();
                $mail->line = $exception->getLine();
                $mail->stack_trace = $exception->getTraceAsString();

                // send client            
                $mail->setTo($config->client->contact->email);            
                $mail->setTemplate($config->mail->template->error->client);
                $bool = $mail->send();
                $this->setHtml($mail->getHtml()); // debug

                // send support
                $mail->setTo($config->smtp->to->support);
                $mail->setTemplate($config->mail->template->error->support);
                $bool = $mail->send();
                $this->setHtml($mail->getHtml()); // debug                
                
            }
                                    
            // redirect (parse file name to get base)                                    
            if($redirect) {                
                
                $path_parts = pathinfo($file);            
                $filename = $path_parts["filename"];   
                
                $params = array(
                        'message' => $message,
                        'code' => $code,
                        'line' => $line,
                        'file' => $filename
                    );
                
                $r = Zend_Controller_Action_HelperBroker::getHelper('redirector');
                $r->goToSimple('index','error','default', $params);
                
            }
                        
            
        } catch (Exception $e) {
            
        }
        
    }
}

?>
