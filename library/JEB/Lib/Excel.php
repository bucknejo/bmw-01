<?php

class JEB_Lib_Excel {

    public function writeExcel($mapper, $name)
    {

        require_once 'Spreadsheet/Excel/Writer.php';

        $rs = $mapper->getDbTable()->fetchAll();
        
        $excel = new Spreadsheet_Excel_Writer();

        $sheet =& $excel->addWorksheet($name);
        $sheet->freezePanes(array(0,0));

        $firstRow =& $excel->addFormat();
        $firstRow->setBold();
        $firstRow->setBottom(1);
        $firstRow->setBottomColor('black');

        $format = 'firstRow';

        $r = 0;
        $c = 0;

        $row = $rs->current();

        foreach ($row as $key => $value) {
            $sheet->write($r, $c, $key, $$format);
            $c++;
        }
        $r++;

        foreach($rs as $row) {
            $c = 0;
            foreach ($row as $key => $value) {
                $sheet->write($r, $c, $value);
                $c++;
            }
            $r++;
        }


        return $excel;

    }


    public function writeExcelNew($filename, $segment, $rss)
    {

        require_once 'Spreadsheet/Excel/Writer.php';
        set_time_limit(5*60);

        $excel = new Spreadsheet_Excel_Writer();
        $excel->send($filename);

        $firstRow =& $excel->addFormat();
        $firstRow->setBold();
        $firstRow->setBottom(1);
        $firstRow->setBottomColor('black');

        $format = 'firstRow';

        $s = 0;
        foreach ($rss as $rs) {

            $sheet =& $excel->addWorksheet($segment[$s]);
            $sheet->freezePanes(array(1,0,1,0));
            $s++;

            $r = 0;
            $c = 0;

            if (count($rs) > 0 ) {

                foreach ($rs[0] as $key => $value) {
                    $sheet->write($r, $c, $key, $$format);
                    $c++;
                }
                $r++;

                foreach ($rs as $row) {
                    $c = 0;
                    $sheet->writeRow($r, $c, $row);
                    $r++;
                }
                
            } else {
                $sheet->write(1, 1, 'No records found for this segment.');
            }

        }
        
        
        if ($excel->close() !== true) {
            echo "Error: Could not output spreadsheet";
        }

    }

    public function writeExcelTest($filename, $name)
    {

        require_once 'Spreadsheet/Excel/Writer.php';

        $excel = new Spreadsheet_Excel_Writer();
        $excel->send($filename);

        $sheet =& $excel->addWorksheet($name);

        $firstRow =& $excel->addFormat();
        $firstRow->setBold();
        $firstRow->setBottom(1);
        $firstRow->setBottomColor('black');

        $format = 'firstRow';

        $r = 0;
        $c = 0;

        for ($i=1;$i<2000;$i++) {
            for ($j=1;$j<10;$j++) {
                $sheet->write($i, $j, "test");
            }
        }

        if ($excel->close() !== true) {
            echo "Error: Could not output spreadsheet";
        }
//        return $excel;

    }

    public function writeExcelXml($filename, $segment, $rss) {

        $out = "";

        $i = 0;

        $auth = Zend_Auth::getInstance();
        $user = $auth->getIdentity()->USERNAME;

        $out .= $this->_writeXmlHeader($user);
        foreach ($rss as $rs) {

            $ss_name = $segment[$i];
            $ecc = $this->_getExpandedColumnCount($rs);
            $erc = count($rs) + 1;

            $out .= $this->_writeXmlWorksheet($rs, $ss_name, $ecc, $erc);

            $i++;
        }
        $out .= $this->_writeXmlFooter();

        return $out;
        
    }

    public function writeExcelXml1($filename, $segment, $rs, $user) {

        $out = "";

        $i = 0;

        $out .= $this->_writeXmlHeader($user);

        $ss_name = $segment;
        $ecc = $this->_getExpandedColumnCount($rs);
        $erc = count($rs) + 1;

        $out .= $this->_writeXmlWorksheet($rs, $ss_name, $ecc, $erc);

        $out .= $this->_writeXmlFooter();

        return $out;

    }


    public function _getExpandedColumnCount($rs) {

        $i = 0;

        if (count($rs) > 0) {
            foreach ($rs[0] as $key => $value) {
                $i++;
            }
        }

        return $i;
    }

    public function _writeXmlHeader($user) {

        $date = date('Y-m-d His');
        $out = "";

        $out .= '<?xml version="1.0"?>' . PHP_EOL;
        $out .= '<?mso-application progid="Excel.Sheet"?>' . PHP_EOL;
        $out .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . PHP_EOL;
        $out .= 'xmlns:o="urn:schemas-microsoft-com:office:office"' . PHP_EOL;
        $out .= 'xmlns:x="urn:schemas-microsoft-com:office:excel"' . PHP_EOL;
        $out .= 'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . PHP_EOL;
        $out .= 'xmlns:html="http://www.w3.org/TR/REC-html40">' . PHP_EOL;
        $out .= '<DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">' . PHP_EOL;
        $out .= '<LastAuthor>'.$user.'</LastAuthor>' . PHP_EOL;
        //$out .= '<Created>'.$date.'</Created>' . PHP_EOL;
        $out .= '<Version>11.9999</Version>' . PHP_EOL;
        $out .= '</DocumentProperties>' . PHP_EOL;
        $out .= '<OfficeDocumentSettings xmlns="urn:schemas-microsoft-com:office:office">' . PHP_EOL;
        $out .= '</OfficeDocumentSettings>' . PHP_EOL;
        $out .= '<ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">' . PHP_EOL;
        $out .= '<WindowHeight>5490</WindowHeight>' . PHP_EOL;
        $out .= '<WindowWidth>9660</WindowWidth>' . PHP_EOL;
        $out .= '<WindowTopX>0</WindowTopX>' . PHP_EOL;
        $out .= '<WindowTopY>0</WindowTopY>' . PHP_EOL;
        $out .= '<ProtectStructure>False</ProtectStructure>' . PHP_EOL;
        $out .= '<ProtectWindows>False</ProtectWindows>' . PHP_EOL;
        $out .= '</ExcelWorkbook>' . PHP_EOL;
        $out .= '<Styles>' . PHP_EOL;
        $out .= '<Style ss:ID="Default" ss:Name="Normal">' . PHP_EOL;
        $out .= '<Alignment ss:Vertical="Bottom"/>' . PHP_EOL;
        $out .= '<Borders/>' . PHP_EOL;
        $out .= '<Font/>' . PHP_EOL;
        $out .= '<Interior/>' . PHP_EOL;
        $out .= '<NumberFormat/>' . PHP_EOL;
        $out .= '<Protection/>' . PHP_EOL;
        $out .= '</Style>' . PHP_EOL;
        $out .= '<Style ss:ID="s17">' . PHP_EOL;
        $out .= '<Borders>' . PHP_EOL;
        $out .= '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"' . PHP_EOL;
        $out .= 'ss:Color="#000000"/>' . PHP_EOL;
        $out .= '</Borders>' . PHP_EOL;
        $out .= '<Font ss:Bold="1"/>' . PHP_EOL;
        $out .= '</Style>' . PHP_EOL;
        $out .= '</Styles>' . PHP_EOL;
        
        return $out;
    }

    public function _writeXmlFooter() {
        $out = "";
        $out .= "</Workbook>" . PHP_EOL;
        return $out;
    }

    public function _writeXmlWorksheet($rs, $ss_name, $ecc, $erc) {

        $out = "";

        $out .= '<Worksheet ss:Name="'.$ss_name.'">' . PHP_EOL;
        $out .= '<Table ss:ExpandedColumnCount="'.$ecc.'" ss:ExpandedRowCount="'.$erc.'" x:FullColumns="1" x:FullRows="1">' . PHP_EOL;

//        $out .= '<Column ss:Width="100.00"/>';
//        $out .= '<Column ss:Width="100.00"/>';
//        $out .= '<Column ss:Width="100.00"/>';

        $r = 0;
        $c = 0;

        if (count($rs) > 0 ) {

            // row header
            $out .= "<Row>" . PHP_EOL;
            foreach ($rs[0] as $key => $value) {
                $out .= '<Cell ss:StyleID="s17"><Data ss:Type="String">'.$key.'</Data></Cell>' . PHP_EOL;
            }
            $out .= "</Row>" . PHP_EOL;

            // body
            foreach ($rs as $row) {
                $out .= "<Row>" . PHP_EOL;
                foreach ($row as $key => $value) {
                    $out .= '<Cell><Data ss:Type="String">'.$value.'</Data></Cell>' . PHP_EOL;
                }
                $out .= "</Row>" . PHP_EOL;
            }

        } else {
            $out .= "<Row>" . PHP_EOL;
            $out .= '<Cell><Data ss:Type="String">No records found for this segment.</Data></Cell>' . PHP_EOL;
            $out .= "</Row>" . PHP_EOL;
        }

        $out .= '</Table>' . PHP_EOL;
        $out .= $this->_writeXmlWorksheetOptions();
        $out .= '</Worksheet>' . PHP_EOL;
        
        return $out;

    }

    public function _writeXmlWorksheetOptions() {
        $out = "";

        $out .= '<WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">' . PHP_EOL;
        $out .= '<Print>' . PHP_EOL;
        $out .= '<FitWidth>0</FitWidth>' . PHP_EOL;
        $out .= '<FitHeight>0</FitHeight>' . PHP_EOL;
        $out .= '<ValidPrinterInfo/>' . PHP_EOL;
        $out .= '<PaperSizeIndex>0</PaperSizeIndex>' . PHP_EOL;
        $out .= '<HorizontalResolution>600</HorizontalResolution>' . PHP_EOL;
        $out .= '<VerticalResolution>600</VerticalResolution>' . PHP_EOL;
        $out .= '<Gridlines/>' . PHP_EOL;
        $out .= '</Print>' . PHP_EOL;
        $out .= '<Selected/>' . PHP_EOL;
        $out .= '<FreezePanes/>' . PHP_EOL;
        $out .= '<SplitHorizontal>1</SplitHorizontal>' . PHP_EOL;
        $out .= '<TopRowBottomPane>1</TopRowBottomPane>' . PHP_EOL;
        $out .= '<ActivePane>2</ActivePane>' . PHP_EOL;
        $out .= '<Panes>' . PHP_EOL;
        $out .= '<Pane>' . PHP_EOL;
        $out .= '<Number>3</Number>' . PHP_EOL;
        $out .= '</Pane>' . PHP_EOL;
        $out .= '<Pane>' . PHP_EOL;
        $out .= '<Number>2</Number>' . PHP_EOL;
        $out .= '<ActiveRow>0</ActiveRow>' . PHP_EOL;
        $out .= '</Pane>' . PHP_EOL;
        $out .= '</Panes>' . PHP_EOL;
        $out .= '<ProtectObjects>False</ProtectObjects>' . PHP_EOL;
        $out .= '<ProtectScenarios>False</ProtectScenarios>' . PHP_EOL;
        $out .= '</WorksheetOptions>' . PHP_EOL;

        return $out;
    }


}
?>
