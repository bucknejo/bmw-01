<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AdminController
 *
 * @author jb197342
 */
class AdminController extends Zend_Controller_Action {
    //put your code here
    
    public function init() {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('read', 'html');
        $ajaxContext->addActionContext('detail', 'html');
        $ajaxContext->initContext();        
    }
    
    public function indexAction() {
        $this->view->excel = "/admin/excel";        
    }
    
    public function readAction()
    {

        $page = $this->_getParam('page', 1);
        $rows = $this->_getParam('rows', 5);
        $table_name = 'users';

        $grid = new JEB_Lib_Grid();
        $wheres = $grid->parseFilters();

        $session = new Zend_Session_Namespace();
        $session->__set("wheres", $wheres);

        $config = Zend_Registry::get('config');
        $columns = explode("|", $config->tablename->$table_name->columns);

        $service = new Application_Service_TableService();
        $entries = $service->fetchOutstanding($page, $wheres, $table_name, $columns, $rows);

        $this->view->response = $grid->writeXml($page, $rows, $entries);
        
    }
    
    public function excelAction()
    {

        $session = new Zend_Session_Namespace();
        $wheres = $session->__get("wheres");

        $table_name = 'users';
        $mapper = new Application_Model_TableMapper();

        $this->_helper->layout->disableLayout();
        $this->getResponse()->clearAllHeaders();

        $filename = 'bmw_'.date('YmdHis');
        $segment = $table_name;
        $rs = $mapper->getAll($table_name, $wheres);
        $user = JEB_Lib_Lib::getUser();

        $response = $this->getResponse();
        $name = "Content-Disposition";
        $value = "attachment; filename=\"".$filename."\"";
        $response->setHeader($name, $value);
        $name = "Content-Type";
        $value = "application/vnd.ms-excel";
        $response->setHeader($name, $value);
        
        $excel = new JEB_Lib_Excel();

        $content = $excel->writeExcelXml1($filename, $segment, $rs, $user);
        $response->setBody($content);
    }   
    
    public function detailAction() {
        
        $id = $this->_getParam("id");
        
        $table_name = "users";
        $mapper = new Application_Model_TableMapper();
        $config = Zend_Registry::get('config');
        $columns = explode("|", $config->tablename->$table_name->columns);
        
        $users = $mapper->getItemById($table_name, $id);
        
        $this->view->users = $users[0];
        
    }
    
    public function createAction() {
        
    }
    
    public function updateAction() {
        
    }
        
}

?>
