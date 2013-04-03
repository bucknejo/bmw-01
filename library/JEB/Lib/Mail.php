<?php

class JEB_Lib_Mail {
    
    protected $_subject;
    protected $_body;
    protected $_mail;
    protected $_recipient;
    protected $_template;
    protected $_vars = array();
    
    protected $_html;
    
    public function __construct() {
        $this->_mail = new Zend_Mail();
        $this->_body = new Zend_View();
    }
    
    public function __set($name, $value) {
        $this->_vars[$name] = $value;
    }
    
    public function setRecipient($recipient) {
        $this->_recipient = $recipient;
    }
    
    public function setTemplate($template) {
        $this->_template = $template;
    }
    
    public function getHtml() {
        return $this->_html;
    }
    
    public function setHtml($html) {
        $this->_html = $html;
    }
    
    public function setTo($to) {
        $this->_mail->addTo($to);
    }
    
    public function setTos($tos) {
        foreach($tos as $to) {
            $this->_mail->addTo($to);
        }
    }
    
    public function setFrom($from) {
        $this->_mail->setFrom($from);
    }
    
    public function setSubject($subject) {
        $this->_mail->setSubject($subject);
    }
    
    public function send($cc=null, $bcc=null, $attachment=null) {

        // get the config object
        $config = Zend_Registry::get('config');
        
        // set the template path & template script
        $template_path = $config->mail->template_path;        
        
        // use default template if not set
        if($this->_template == null) {
            $this->setTemplate($config->mail->template->default);
        }        
        
        // items set on the object are added to the template (view)
        foreach($this->_vars as $key => $value) {
            $this->_body->{$key} = $value;
        }
        
        // render the mail template view into a variable & set the body
        $this->_body->setScriptPath($template_path);
        $html = $this->_body->render($this->_template);
        $this->_mail->setBodyHtml($html);
        
        // hold in storage for debug
        $this->setHtml($html);
                
        if ($cc != null) $this->_mail->addCc ($cc);
        if ($bcc != null) $this->_mail->addBcc ($bcc);

        if ($attachment != null) {
            $attachment = file_get_contents($attachment);
            $this->_mail->createAttachment($attachment);
        }
        
        // bombs away
        return $this->_mail->send();        
        
    }
    
    
}

?>
