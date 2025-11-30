<?php

final class Language {

    private $db;
    private $data;
    private $language_id;

    public function __construct($registry) {
        $this->db = $registry->get('db');
        $setting = $registry->get('setting');
        $language_id = $setting['language_id'];
        $this->language_id = $language_id;

        $sql = "SELECT sv.variable, lvv.value";
        $sql .= " FROM `" . DB_PREFIX . "system_variable` sv";
        $sql .= " INNER JOIN `" . DB_PREFIX . "language_variable_value` lvv ON lvv.system_variable_id = sv.system_variable_id AND lvv.language_id='$language_id' AND route =''";
        $query = $this->db->query($sql);
        $rows = $query->rows;
        foreach($rows as $row) {
            $this->data[$row['variable']] = $row['value'];
        }
    }

    public function load($route) {
        $language_id = $this->language_id;
        $sql = "SELECT sv.variable, lvv.value";
        $sql .= " FROM `" . DB_PREFIX . "system_variable` sv";
        $sql .= " INNER JOIN `" . DB_PREFIX . "language_variable_value` lvv ON lvv.system_variable_id = sv.system_variable_id AND lvv.language_id='$language_id' AND lvv.route='$route'";
        $query = $this->db->query($sql);
        $rows = $query->rows;
        foreach($rows as $row) {
            $this->data[$row['variable']] = $row['value'];
        }

        return $this->data;
    }

    public function get($key) {
        return (isset($this->data[$key]) ? $this->data[$key] : $key);
    }
}

?>