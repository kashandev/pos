<?php

class ModelCommonSetting extends HModel {

    protected function getTable() {
        return 'core_setting';
    }

    protected function getPrimaryKey() {
        return 'setting_id';
    }

    public function updateSetting($filter, $data) {
        return $this->update($filter, $data);
    }

    protected function update($filter,$data) {
        $table_column = $this->getTableColumns($this->getTable());
        $sql = "UPDATE `" . DB_PREFIX . $this->getTable() . "` SET";
        foreach($data as $column => $value) {
            if(in_array($column, $table_column)) {
                if($value === NULL) {
                    $sql .= " `" . $column . "` = NULL,";
                } else {
                    $sql .= " `" . $column . "` = '" . html_entity_decode($this->conn->escape($value)) . "',";
                }
            }
        }
        $sql = substr($sql,0, strlen($sql)-1);
        if($filter) {
            if(is_array($filter)) {
                $implode = array();
                foreach($filter as $column => $value) {
                    $implode[] = "`$column`='$value'";
                }
                if($implode)
                    $sql .= " WHERE " . implode(" AND ", $implode);
            } else {
                $sql .= " WHERE " . $filter;
            }
        }
        $query = $this->conn->query($sql);
        $setting = $query->rows;
    }

}

?>