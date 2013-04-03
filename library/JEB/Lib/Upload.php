<?php

/*******************************************************************************
 * 
 *  Class is designed to take and upload, destination path, project_id, id
 *  
 *  sets ready flag
 *  sets messages
 *  sets files array of items
 *  sets success flag
 * 
 ******************************************************************************/

class JEB_Lib_Upload {
    
    protected $_isReady = false;
    protected $_isSuccess = false;
    
    protected $_uploadPath;
    protected $_destinationPath;
    protected $_projectId;
    protected $_id;
    
    protected $_start;
    protected $_base;
    protected $_pad;
    
    protected $_messages;
    
    protected $_files;

    public function __construct() {
        $this->_messages = array();
        $this->_files = array();
    }
    
    public function upload()
    {
                
        try {
            
            $log = JEB_Lib_Log::get();
            
            if($this->_isReady()) {
                
                // initialize the file upload adapter
                $adapter = new Zend_File_Transfer_Adapter_Http();            
                $adapter->setDestination($this->getUploadPath());   
                $adapter->addValidator('Count',false,1);
                $adapter->addValidator('Extension',false,array('zip', 'jpg', 'pdf'));

                // initiate file upload (calls isValid intrinsically)
                if(!$adapter->receive()) {
                    $messages = $adapter->getMessages();
                    foreach($messages as $message) {
                        $this->_messages[] = $message;
                    }
                    $log->logItDb(6, 'LIB UPLOAD: 1');
                    
                } else {                
                    // file upload success, triggers next sequential items
                    $file_size = $adapter->getFileSize();
                    $file_info = $adapter->getFileInfo();
                    $mime_type = $adapter->getMimeType();
                    $file_name = $adapter->getFileName();
                                        
                    $log->logItDb(6, 'LIB UPLOAD: 2');

                    // log it
                    $this->_messages[] = "File Size: $file_size";
                    $this->_messages[] = "File Info: $file_info";
                    $this->_messages[] = "MIME Type: $mime_type";
                    $this->_messages[] = "File Name: $file_name";

                    $log->logItDb(6, 'LIB UPLOAD: 3');
                    
                    if ($mime_type == "application/zip" || $mime_type == 'application/octet-stream') {
                        $log->logItDb(6, 'LIB UPLOAD: 4');
                        $this->_files = JEB_Lib_Zip::unpack($file_name, 
                                $this->getDestinationPath(), 
                                $this->getBase(), 
                                $this->getPad(),
                                $this->getStart());
                        
                        $log->logItDb(6, 'LIB UPLOAD: 5');
                        
                        if ($this->_files > 0) {
                            $this->setIsSuccess(true);
                        } else {
                            $this->setIsSuccess(false);                            
                        }
                    } else {
                        $parts = pathinfo($file_name);
                        $from = $file_name;
                        $name = $this->getBase() . "_" . str_pad($this->getStart(), $this->getPad(), "0", STR_PAD_LEFT) . "." .$parts["extension"];
                        $to = $this->getDestinationPath() . $name;
                        $copied = rename($from, $to);
                        if ($copied) {
                            $this->_files[] = $name;
                            $this->setIsSuccess(true);
                        } else {                            
                            $this->setIsSuccess(false);
                        }
                        
                        $log->logItDb(6, 'LIB UPLOAD: 7');
                        
                        
                    }
                }
                
                
            } else {
                $this->setIsSuccess(false);
            }
            
        } catch (Exception $e) {
                        
        }
        
    }
    
    public function _isReady() {
        
        $ready = false;
        
        $clear = array();
        
        $clear[] = $this->_isValidPath($this->getUploadPath());
        $clear[] = $this->_isValidPath($this->getDestinationPath());
                
        $p = $this->getProjectId();
        $i = $this->getId();
        
        $s = $this->getStart();
        $x = $this->getPad();
        $y = $this->getBase();
        
        if($clear[0]) {
            array_push($this->_messages, "upload path is valid: " . $this->getUploadPath());
        } else {
            array_push($this->_messages, "upload path is not valid: " . $this->getUploadPath());
        }
        
        if($clear[1]) {
            array_push($this->_messages, "destination path is valid: " . $this->getDestinationPath());
        } else {
            array_push($this->_messages, "destination path not is valid: " . $this->getDestinationPath());
        }
        
        
        if($p != null && trim($p) != "") {
            $clear[] = true;
            array_push($this->_messages, "project id is set: " . $this->getProjectId());
        } else {
            $clear[] = false;
            array_push($this->_messages, "project id is not set: " . $this->getProjectId());
        }
        
        if($s != null && trim($s) != "") {
            $clear[] = true;
            array_push($this->_messages[],"start is set: " . $this->getStart());
        } else {
            $clear[] = false;
            array_push($this->_messages[],"start is not set: " . $this->getStart());
        }
        
        if($x != null && trim($x) != "") {
            $clear[] = true;
            array_push($this->_messages,"pad is set: " . $this->getPad());
        } else {
            $clear[] = false;
            array_push($this->_messages,"pad is not set: " . $this->getPad());
        }
        
        if($y != null && trim($y) != "") {
            $clear[] = true;
            array_push($this->_messages,"base is set: " . $this->getBase());
        } else {            
            $clear[] = false;
            array_push($this->_messages,"base is not set: " . $this->getBase());
        }
        
        $ready = !in_array(false, $clear);
        
        return $ready;
        
    }
    
    protected function _isValidPath($path) {        
        return (!realpath($path)) ? false : true;                
        //return true;
    }
    
    // getters & setters for private items
    
    public function getIsReady() {
        return $this->_isReady;
    }

    public function setIsReady($isReady) {
        $this->_isReady = $isReady;
    }

    public function getIsSuccess() {
        return $this->_isSuccess;
    }

    public function setIsSuccess($isSuccess) {
        $this->_isSuccess = $isSuccess;
    }

    public function getUploadPath() {
        return $this->_uploadPath;
    }

    public function setUploadPath($uploadPath) {
        $this->_uploadPath = $uploadPath;
    }

    public function getDestinationPath() {
        return $this->_destinationPath;
    }

    public function setDestinationPath($destinationPath) {
        $this->_destinationPath = $destinationPath;
    }

    public function getProjectId() {
        return $this->_projectId;
    }

    public function setProjectId($projectId) {
        $this->_projectId = $projectId;
    }

    public function getId() {
        return $this->_id;
    }

    public function setId($id) {
        $this->_id = $id;
    }
    
    public function getStart() {
        return $this->_start;
    }

    public function setStart($start) {
        $this->_start = $start;
    }
        
    public function getBase() {
        return $this->_base;
    }

    public function setBase($base) {
        $this->_base = $base;
    }

    public function getPad() {
        return $this->_pad;
    }

    public function setPad($pad) {
        $this->_pad = $pad;
    }
    
    public function getMessages() {
        return $this->_messages;
    }

    public function setMessages($messages) {
        $this->_messages = $messages;
    }

    public function getFiles() {
        return $this->_files;
    }

    public function setFiles($files) {
        $this->_files = $files;
    }




}

?>
