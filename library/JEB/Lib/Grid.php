<?php

class JEB_Lib_Grid {
    
    public function parseFilters() {

        $front = Zend_Controller_Front::getInstance();
        $request = $front->getRequest();

        $filters = json_decode($request->getParam('filters'));

        $wheres = array();

        if (count($filters)>0) {
            $type = $filters->groupOp . " ";

            $items = $filters->rules;

            foreach($items as $item) {
                $where = $item->field . " " . $this->replaceOperand($item->op)
                        . " '" . $item->data . "' ";
                $wheres[] = $where;
            }
        }

        return $wheres;

        
    }

    public function parseFiltersNew() {

        $wheres = array();

        $front = Zend_Controller_Front::getInstance();
        $request = $front->getRequest();

        $f = $request->getParam('filters', '');

        // case 1:  if there is no filters parameter, just return empty array
        if ($f == '') return $wheres;

        // case 2:  if there is a filters parameter, do the magic shizzat

        // {"groupOp":"AND","rules":[{"field":"HA_INPUT_EXTERNAL_ACCOUNT","op":"eq","data":"368828"}]}
        $object = json_decode($f);
               
        for($i=0; $i < count($object->rules); $i++) {
            $where = $object->rules["field"] . " " .
                    $this->replaceOperand($object->rules["op"]) . " '" .
                    $object->rules["data"] . "' ";
            $wheres[] = $where;
        }

        return $wheres;

    }

    public function replaceOperand($operand) {

        $operands = array(
            'eq' => '=',
            'ne' => '!=',
            'lt' => '<',
            'le' => '<=',
            'gt' => '>',
            'ge' => '>=',
            'bw' => 'LIKE',
            'bn' => 'NOT LIKE',
            'in' => 'IN',
            'ni' => 'NOT IN',
            'ew' => '',
            'en' => '',
            'cn' => '',
            'nc' => '',
        );

        foreach ($operands as $key => $value) {
            if ($key == $operand) return $value;
        }

        return '=';

    }

    public function testHelper() {

        $front = Zend_Controller_Front::getInstance();
        $request = $front->getRequest();

        $filters = json_decode($request->getParam('filters'));

        $path = realpath(APPLICATION_PATH . '/../docs');
        $name = 'log_' . str_replace("-", "", date('Y-m-d')) . '.log';
        //$name = 'test_log.log';
        $file = $path . "/" . $name;
        $stream = fopen($file, 'a', false);

        $message = "Filters: " . count($filters) . PHP_EOL;

        //fwrite($stream, $message);

        $params = $request->getParams();

        foreach ($params as $key => $value) {
            $message = "Key: $key\t\tValue: $value" . PHP_EOL;
            //fwrite($stream, $message);
        }

        $wheres = array();

        if (count($filters)>0) {
            $type = $filters->groupOp . " ";

            $items = $filters->rules;

            foreach($items as $item) {
                $where = $item->field . " " . $this->replaceOperand($item->op)
                        . " '" . $item->data . "' ";
                fwrite($stream, $where);
                $wheres[] = $where;
            }
        }

        return $wheres;
        
    }

    public function writeXml($page, $rows, $entries)
    {

        $xml = '';

        $xml .= "<?xml version='1.0' encoding='utf-8'?>";
        $xml .= "<rows>";
        $xml .= "<page>" . $page . "</page>";
        $xml .= "<total>" . $entries->count() . "</total>";
        $xml .= "<records>" . $entries->getTotalItemCount() . "</records>";

        $i = (($page - 1) * $rows) + 1;

        foreach($entries as $line) {
            $xml .= "<row id='".$i."'>";
            $xml .= "<cell>".$i."</cell>";
            foreach($line as $item) {
                $xml .= "<cell>".$item."</cell>";
            }
            $xml .= "</row>";
            $i++;
        }
        $xml .= "</rows>";

        return $xml;

    }

    
    
}

?>
