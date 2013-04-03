<?php

class JEB_ACL_Factory {

    public static function get() {
        
        $session = new Zend_Session_Namespace('ACL');
        
        if(isset($session->acl)) {
            return $session->acl;
        } else {
            return self::load();
        }
        
    }
    
    public static function load() {
        
        $acl = new Zend_Acl();
        $mapper = new Application_Model_TableMapper();
        $log = JEB_Lib_Log::get();

        try {

            $wheres = array();
            $wheres[] = "active=1";
            $roles = $mapper->getAll('roles', $wheres);
            $resources = $mapper->getAll('resources', $wheres);
            $role_resources = $mapper->getAll('role_resources', $wheres);

            $log->logItDb(6, "Roles Count: " . count($roles));
            $log->logItDb(6, "Resources Count: " . count($resources));
            $log->logItDb(6, "Role Resources Count: " . count($role_resources));

            // ADD ROLES TO ACL
            foreach($roles as $role) {
                $acl->addRole($role['id']);            
            }

            $log->logItDb(6, "Roles Added Successfully");

            // ADD RESOURCES TO ACL
            foreach($resources as $resource) {
                $acl->addResource($resource["module"]."::".$resource["controller"]."::".$resource["action"]);
            }

            $log->logItDb(6, "Resources Added Successfully");
            
            foreach($role_resources as $role_resource) {
                $role_id = $role_resource['role_id'];
                $resource = self::getResouceString($role_resource['resource_id'], $resources);                
                $acl->allow($role_id, $resource);
            }

            $log->logItDb(6, "Role Resources Allowed Successfully");
            
            self::save($acl);

            return $acl;
            
        } catch (Exception $e) {
            
            new JEB_Lib_Exception($e, true, false);
            
        }
        
        
        
    }
    
    public static function save($acl) {
        $session = new Zend_Session_Namespace("ACL");
        $session->acl = $acl;        
    }
    
    public static function getResouceString($id, $rs) {
        
        $temp = "";
        
        foreach($rs as $r) {
            if ($id == $r['id']) {
                $temp = $r["module"]."::".$r["controller"]."::".$r["action"];
            }
        }
        
        return $temp;
        
    }

}

?>
