<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Zip
 *
 * @author bucknejo-1
 */
class JEB_Lib_Zip {

    public function unzip($dir, $file, $destination = "") {

        $dir .= DIRECTORY_SEPARATOR;
        $path_file = $dir . $file;
        $zip = zip_open($path_file);
        $_tmp = array();
        $count=0;
        if ($zip)
        {
            while ($zip_entry = zip_read($zip))
            {
                $_tmp[$count]["filename"] = zip_entry_name($zip_entry);
                $_tmp[$count]["stored_filename"] = zip_entry_name($zip_entry);
                $_tmp[$count]["size"] = zip_entry_filesize($zip_entry);
                $_tmp[$count]["compressed_size"] = zip_entry_compressedsize($zip_entry);
                $_tmp[$count]["mtime"] = "";
                $_tmp[$count]["comment"] = "";
                $_tmp[$count]["folder"] = dirname(zip_entry_name($zip_entry));
                $_tmp[$count]["index"] = $count;
                $_tmp[$count]["status"] = "ok";
                $_tmp[$count]["method"] = zip_entry_compressionmethod($zip_entry);

                if (zip_entry_open($zip, $zip_entry, "r"))
                {
                    $buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                    if($destiny)
                    {
                        $path_file = str_replace("/",DIRECTORY_SEPARATOR, $destiny . zip_entry_name($zip_entry));
                    }
                    else
                    {
                        $path_file = str_replace("/",DIRECTORY_SEPARATOR, $dir . zip_entry_name($zip_entry));
                    }
                    $new_dir = dirname($path_file);

                    // Create Recursive Directory
                    mkdirr($new_dir);


                    $fp = fopen($dir . zip_entry_name($zip_entry), "w");
                    fwrite($fp, $buf);
                    fclose($fp);

                    zip_entry_close($zip_entry);
                }
                $count++;
            }

            zip_close($zip);

        }

        return $count;

    }

    public function unzipIt($source, $file, $destination, $new_file_name_base) {

        $source .= DIRECTORY_SEPARATOR;
        $destination .= DIRECTORY_SEPARATOR;
        $zip = zip_open($file);
        $_tmp = array();
        $count=0;
        if ($zip)
        {
            while ($zip_entry = zip_read($zip))
            {
                $_tmp[$count]["filename"] = zip_entry_name($zip_entry);
                $_tmp[$count]["stored_filename"] = zip_entry_name($zip_entry);
                $_tmp[$count]["size"] = zip_entry_filesize($zip_entry);
                $_tmp[$count]["compressed_size"] = zip_entry_compressedsize($zip_entry);
                $_tmp[$count]["mtime"] = "";
                $_tmp[$count]["comment"] = "";
                $_tmp[$count]["folder"] = dirname(zip_entry_name($zip_entry));
                $_tmp[$count]["index"] = $count;
                $_tmp[$count]["status"] = "ok";
                $_tmp[$count]["method"] = zip_entry_compressionmethod($zip_entry);

                $fileparts = pathinfo($_tmp[$count]["filename"]);
                $extension = $fileparts["extension"];
                
                $count++;
                if (zip_entry_open($zip, $zip_entry, "r"))
                {
                    $buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

                    $name = $new_file_name_base . "_" . str_pad($count, 2, "0", STR_PAD_LEFT) . "." . $extension;

                    $fp = fopen($destination . $name, "w");
                    fwrite($fp, $buf);
                    fclose($fp);

                    zip_entry_close($zip_entry);

                }
            }

            zip_close($zip);

        }

        return $count;

    }
    
    public static function unpack($file, $destination, $base, $pad, $start) {

        $log = JEB_Lib_Log::get();
        
        $zip = zip_open($file);
        $_tmp = array();
        $files = array();
        $count=$start;
        if ($zip)
        {
            $log->logItDb(6, 'ZIP: 1');
            while ($zip_entry = zip_read($zip))
            {
                $_tmp[$count]["filename"] = zip_entry_name($zip_entry);
                $_tmp[$count]["stored_filename"] = zip_entry_name($zip_entry);
                $_tmp[$count]["size"] = zip_entry_filesize($zip_entry);
                $_tmp[$count]["compressed_size"] = zip_entry_compressedsize($zip_entry);
                $_tmp[$count]["mtime"] = "";
                $_tmp[$count]["comment"] = "";
                $_tmp[$count]["folder"] = dirname(zip_entry_name($zip_entry));
                $_tmp[$count]["index"] = $count;
                $_tmp[$count]["status"] = "ok";
                $_tmp[$count]["method"] = zip_entry_compressionmethod($zip_entry);
                
                $fileparts = pathinfo($_tmp[$count]["filename"]);
                $extension = $fileparts["extension"];
                
                if (zip_entry_open($zip, $zip_entry, "r"))
                {
                    $buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

                    $name = $base . "_" . str_pad($count, $pad, "0", STR_PAD_LEFT) . "." .$extension;

                    $fp = fopen($destination . $name, "w");
                    fwrite($fp, $buf);
                    fclose($fp);

                    zip_entry_close($zip_entry);
                    $files[$count] = $name;

                }
                $count++;
                
            }

            $log->logItDb(6, 'ZIP: 2');
            zip_close($zip);

        }

        return $files;

    }    

}
?>
