<?php

final class ImportCSV {

    var $table_name; //where to import to
    var $file_name;  //where to import from
    var $use_csv_header; //use first line of file OR generated columns names
    var $field_separate_char; //character to separate fields
    var $field_enclose_char; //character to enclose fields, which contain separator char into content
    var $field_escape_char;  //char to escape special symbols
    var $error; //error message
    var $arr_csv_columns; //array of columns
    var $table_exists; //flag: does table for import exist
    var $encoding; //encoding table, used to parse the incoming file. Added in 1.5 version

    public function __construct($registry) {
        $this->db = $registry->get('db');
        $this->table_name = "tempproject";
        $this->arr_csv_columns = array();
        $this->use_csv_header = true;
        $this->field_separate_char = ",";
        $this->field_enclose_char  = "\"";
        $this->field_escape_char   = "\\";
        $this->table_exists = false;
    }

    public function setFilename($file_name) {
        $this->file_name = $file_name;
    }

    public function setCSVHeader($option = False) {
        $this->use_csv_header = $option;
    }

    public function setFieldSeparator($character) {
        $this->field_separate_char = $character;
    }

    public function setEncloseCharacter($character) {
        $this->field_enclose_char = $character;
    }

    public function setEscapeCharacter($character) {
        $this->field_escape_char = $character;
    }

    public function import() {
        $return = array();
        //$this->create_import_table();
        if($this->error) {
            $return = array(
                'status' => false,
                'error' => $this->error
            );
        } else {
            if(empty($this->arr_csv_columns))
                $this->get_csv_header_fields();

//        /* change start. Added in 1.5 version */
//        if("" != $this->encoding && "default" != $this->encoding)
//            $this->set_encoding();
//        /* change end */


            if(!$this->error)
            {
                $sql = "TRUNCATE TABLE `tempproject`";
                $this->db->query($sql);
                $sql = " LOAD DATA INFILE '".$this->db->escape($this->file_name).
                    "' INTO TABLE `".$this->table_name.
                    "` FIELDS TERMINATED BY '".$this->db->escape($this->field_separate_char).
                    "' OPTIONALLY ENCLOSED BY '".$this->db->escape($this->field_enclose_char).
                    "' ESCAPED BY '".$this->db->escape($this->field_escape_char).
                    "' ".
                    ($this->use_csv_header ? " IGNORE 1 LINES " : "");
                //$sql .= "(`".implode("`,`", $this->arr_csv_columns)."`)";
                $this->db->query($sql);
//                d($sql, true);
//                $sql = "ALTER TABLE `".$this->table_name."` ADD COLUMN `sr` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY(`sr`)";
//                $this->db->query($sql);

                $return = array(
                    'status' => true,
                    'table_name' => $this->table_name
                );
            }
        }
        return $return;
    }


    //returns array of CSV file columns
    public function get_csv_header_fields()
    {
        $this->arr_csv_columns = array();
        $fpointer = fopen($this->file_name, "r");
        if ($fpointer)
        {
            $arr = fgetcsv($fpointer, 10*1024, $this->field_separate_char);
            if(is_array($arr) && !empty($arr))
            {
                if($this->use_csv_header)
                {
                    foreach($arr as $val)
                        if(trim($val)!="")
                            $this->arr_csv_columns[] = $val;
                }
                else
                {
                    $i = 1;
                    foreach($arr as $val)
                        if(trim($val)!="")
                            $this->arr_csv_columns[] = "column".$i++;
                }
            }
            unset($arr);
            fclose($fpointer);
        } else
            $this->error = "file cannot be opened: ".($this->file_name=="" ? "[empty]" : $this->db->escape($this->file_name));
        return $this->arr_csv_columns;
    }


    public function create_import_table()
    {
        $sql = "CREATE TABLE IF NOT EXISTS ".$this->table_name." (";

        if(empty($this->arr_csv_columns))
            $this->get_csv_header_fields();

        if(!empty($this->arr_csv_columns))
        {
            $arr = array();
            for($i=0; $i<sizeof($this->arr_csv_columns); $i++)
                $arr[] = "`".$this->arr_csv_columns[$i]."` TEXT";
            $sql .= implode(",", $arr);
            $sql .= ")";
            $res = $this->db->query($sql);
            $this->error = $this->db->getError();
        }
    }

}

?>