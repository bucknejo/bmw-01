<?php
class JEB_Lib_Lib  {


    public function getImages($path)
    {

        $app = realpath(APPLICATION_PATH . '/../public');

        $path = $app . $path;

        $files = array_diff(scandir($path), array('..', '.', 'Thumbs.db'));

        if (!$files) $files = array();

        return $files;
    }

    public function checkFileInfo($path) {

        $files = array();

        try {

            $priority = 6;
            $log = $this->getActionController()->getHelper('log');
            $app = realpath(APPLICATION_PATH . '/../public');

            $path = $app . $path;

            // get array of files on file system excluding .., ., and Thumbs.db
            $files = array_diff(scandir($path), array('..', '.', 'Thumbs.db'));

            if (!empty($files)) {
                // scroll through the file array
                foreach($files as $file) {

                    if (exif_imagetype($path.$file) != IMAGETYPE_JPEG) {

                        unlink($path.$file);
                        // log it
                        $message = "Deleting: " .$path.$file;
                        $j = $log->logItDb($priority, $message);

                    }

//                    $mime_type = mime_content_type($path.$file);
//
//                    if ($mime_type != 'image/jpeg') {
//                        unlink($path.$file);
//                        // log it
//                        $message = "Deleting: " .$path.$file;
//                        $j = $log->logItDb($priority, $message);
//                    }

                }
            }

        } catch (Exception $e) {
            $message = $e->getMessage();
        }

        return count($files);
        
    }
    
    public static function getMimeType($extension) {
        
        $mime = "";
        
        if ($extension == 'pdf') {
            $mime = "application/pdf";
        }
        
        if ($extension == 'txt') {
            $mime = "text/txt";
        }
        
        return $mime;
    }
    
    public static function getUser() {
        
        $user = "";
        
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $user = $auth->getIdentity()->user_id;
        }
        
        return $user;
                
    }
    
    public static function getClientIdByUser() {
        $user = self::getUser();
        
        $mapper = new Application_Model_TableMapper();
        $table_name = 'clients';
        $column = 'id';
        $where = "user_id='$user'";
        $id = $mapper->getSingleColumnValue($table_name, $column, $where);
        
        return $id;
    }
    
    public function getApplicationResources() {
        
        $front = Zend_Controller_Front::getInstance();
        
        $resources = array();
        
        $modules = $front->getControllerDirectory();
        
        foreach($modules as $module => $path) {
            
            $paths = array_diff(scandir($path), array(".", ".."));
            
            foreach($paths as $file) {
                
                if (strstr($file, "Controller.php") !== false) {
                    
                    include_once $path . DIRECTORY_SEPARATOR . $file;
                    
                    $classes = get_declared_classes();
                    
                    foreach($classes as $class) {
                        
                        if(is_subclass_of($class, 'Zend_Controller_Action')) {
                            
                            $controller = substr($class, 0, strpos($class, "Controller"));
                            $controller = substr($controller, strpos($controller, "_"));
                            $controller = str_replace("_", "", $controller);
                            $controller = lcfirst($controller);
                            $actions = array();
                            
                            foreach(get_class_methods($class) as $action) {
                                if(strstr($action, "Action") !== false) {
                                    $action = substr($action, 0, strpos($action, "Action"));
                                    $actions[] = $action;
                                }
                            }
                                                        
                        }
                        
                        
                    }
                    
                    $resources[$module][$controller] = $actions;
                    
                }
                
            }
            
        }
                
        return $resources;
        
    }
    
    public function getAppResources() {
        
        try {

            $front = Zend_Controller_Front::getInstance();
            

            $resources = array();

            // list modules paths
            $modules = $front->getControllerDirectory();

            foreach($modules as $module => $path) {

                // get files in each path
                $files = array_diff(scandir($path), array(".", ".."));

                foreach($files as $file) {

                    // check if file is a Controller
                    if (strstr($file, "Controller.php") !== false) { 

                        $controller = substr($file, 0, strpos($file, "Controller"));
                        $controller = substr($controller, strpos($controller, "_"));
                        $controller = str_replace("_", "", $controller);
                        $controller = lcfirst($controller);
                        
                        $fileparts = explode(".", $file);
                        $baseClass = $fileparts[0];

                        include_once $path . DIRECTORY_SEPARATOR . $file; 
                        
                        $classes = get_declared_classes();
                        
                        foreach($classes as $class) {
                            if(strstr($class, $baseClass)) {
                                $obj = $class;
                            }
                        }
                        
                        
                        
                        //$class = array_pop($classes);
                        
    
                        //$fileparts = explode(".", $file);
                        //$class = $fileparts[0]; 
                        
                        $reflector = new ReflectionClass($obj);                    
                        $methods = $reflector->getMethods();

                        $actions = array();
                        foreach($methods as $method) {
                            foreach($method as $key => $value) {
                                if ($key == 'name') {
                                    if(strstr($value, "Action") !== false) {
                                        $action = substr($value, 0, strpos($value, "Action"));
                                        $actions[] = $action;
                                    }
                                }
                            }
                        }

                        if (count($actions)>0) 
                            $resources[$module][$controller] = $actions;                    

                    }


                }

            }

            return $resources;
            
            
            
        } catch (Exception $e) {

        }
        
        
    }
    
    public function loadApplicationResources($resources) {
                        
        try {
            
            $mapper = new Application_Model_TableMapper();
            $table_name = 'role_resources';
            $int = $mapper->truncateTable($table_name);
            $table_name = 'resources';
            $int = $mapper->truncateTable($table_name);
            
            $data = array();
            foreach($resources as $module => $controllers) {
                foreach($controllers as $controller => $actions) {
                    foreach($actions as $action) {
                        $data = array(
                            'date_created' => new Zend_Db_Expr('CURDATE()'),
                            'last_updated' => new Zend_Db_Expr('NOW()'),
                            'user' => JEB_Lib_Lib::getUser(),
                            'active' => 1,
                            'module' => $module,
                            'controller' => $controller,
                            'action' => $action,
                            'name' => ucfirst($controller),
                            'route_name' => ucfirst($action)
                        );
                        $int = $mapper->insertItem($table_name, $data);
                    }
                }
            }
                        
            return $int;
            
            
        } catch (Exception $e) {

        }        
        
    }
    
    public function loadApplicationRoleResources() {
        
        try {
            
            $mapper = new Application_Model_TableMapper();
            $wheres = array();
            $resources = $mapper->getAll('resources', $wheres);
            $roles = $mapper->getAll('roles', $wheres);
            
            foreach($roles as $role) {
                foreach($resources as $resource) {
                    $data = array(
                        'date_created' => new Zend_Db_Expr('CURDATE()'),
                        'last_updated' => new Zend_Db_Expr('NOW()'),
                        'user' => self::getUser(),
                        'active' => 1,
                        'role_id' => $role["id"],
                        'resource_id' => $resource["id"]                      
                    );
                    $int = $mapper->insertItem('role_resources', $data);
                }
            }
            
            return $int;
            
            
        } catch (Exception $e) {

        }
    }
    
    public static function roleDetector() {

        $role_id = 9999;
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $role_id = $auth->getIdentity()->role_id;
        }
        return $role_id;

    }
    
    public static function loggedInAs()
    {

        $auth = Zend_Auth::getInstance();
                
        if ($auth->hasIdentity()) {
            $firstname = $auth->getIdentity()->first_name;
            $role_id = $auth->getIdentity()->role_id;
                        
            $url = self::sslUrl('/admin/auth/logout', false);
            
            $logoutUrl = "<a href='$url'>Log Out</a>";
            
            switch($role_id) {
                case 0:
                    $homeUrl = "<a href='/admin/auth/welcome'>Home</a>";
                    break;
                case 1:
                    $homeUrl = "<a href='/admin/auth/welcome'>Home</a>";
                    break;
                case 2:
                    $homeUrl = "<a href='/admin/auth/welcome'>Home</a>";
                    break;
                case 3:
                    $homeUrl = "<a href='/admin/auth/welcome'>Home</a>";
                    break;
                case 4:
                    $homeUrl = "<a href='/client/home'>Home</a>";
                    break;
                default:
                    $homeUrl = "<a href='/'>Home</a>";
                    break;
                    
            }
            
            return "Welcome back, $firstname&nbsp;&nbsp;&nbsp;$homeUrl&nbsp|&nbsp;$logoutUrl&nbsp;&nbsp;&nbsp;";
        } else {
            $url = self::sslUrl('/admin/auth/logon', true);
            return "<a href='$url'>Site Logon</a>";
        }

    }
    
    public static function sslUrl($url, $on) {
        
        $config = Zend_Registry::get('config');            
        $ssl = $config->ssl->switch;        

        if (APPLICATION_ENV == 'production' && $ssl && $on) {            
            $url = $config->ssl->domain . $url;            
        } else {
            //$url = $config->http->domain . $url;
        }        
        
        return $url;
        
    }
    
    public static function getArchiveArray($data) {
        
        $years = array();
        
        // get last ten years
        for($i=0; $i<10; $i++) {
            $years[] = date('Y') - $i;
        }
        
        foreach($years as $year) {
            
            foreach($data as $item) {
                if($item["archive_year"] == $year) {
                    $years[$year][] = $item;
                }
            }
            
        }
        
        return $years;
        
        
    }
    
    


}
?>
