<?php

class ModelProductionExpense extends HModel {

    protected function getTable() {
        return 'production_expense';
    }

    public function getRow($filter=array(), $sort_order=array()) {
        $sql = "SELECT *";
        $sql .= " FROM `" . DB_PREFIX . "vw_production_expense`";
        if($filter) {
            if(is_array($filter)) {
                $table_columns = $this->getTableColumns($this->getTable());
                $implode = array();
                foreach($filter as $column => $value) {
                    if(in_array($column,$table_columns)) {
                        $implode[] = "`$column`='$value'";
                    }
                }
                if($implode)
                    $sql .= " WHERE " . implode(" AND ", $implode);
            } else {
                $sql .= " WHERE " . $filter;
            }
        }

        if($sort_order) {
            $sql .= " ORDER BY " . implode(',',$sort_order);
        }

        $query = $this->db->query($sql);
//               d(array($sql),true);

        return $query->row;
    }

    public function getRows($filter=array(), $sort_order=array()) {
        $sql = "SELECT *";
        $sql .= " FROM `" . DB_PREFIX .  "vw_production_expense`";
        if($filter) {
            if(is_array($filter)) {
                $table_columns = $this->getTableColumns($this->getTable());
                $implode = array();
                foreach($filter as $column => $value) {
                    if(in_array($column,$table_columns)) {
                        $implode[] = "`$column`='$value'";
                    }
                }
                if($implode)
                    $sql .= " WHERE " . implode(" AND ", $implode);
            } else {
                $sql .= " WHERE " . $filter;
            }
        }

        if($sort_order) {
            $sql .= " ORDER BY " . implode(',',$sort_order);
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getArrays($field, $value, $filter=array(), $sort_order=array(), $value_separator) {
        $rows = $this->getRows($filter,$sort_order);
        $arrRows = array();
        foreach($rows as $row) {
            $strValue = '';
            $implode = array();
            if(is_array($value)) {
                foreach($value as $c => $v) {
                    $implode[] = $row[$v];
                }
                if($implode) {
                    $strValue = implode($value_separator,$implode);
                }
            } else {
                $strValue = $row[$value];
            }

            $arrRows[$row[$field]] = $strValue;
        }

        return $arrRows;
    }

    public function getLists($data) {
        $filterSQL = $this->getFilterString($data['filter']);
        $criteriaSQL = $this->getCriteriaString($data['criteria']);

        $sql = "SELECT count(*) as total";
        $sql .= " FROM `" . DB_PREFIX . "vw_production_expense`";

        $query = $this->db->query($sql);
        $table_total = $query->row['total'];

        $sql = "SELECT count(*) as total";
        $sql .= " FROM `" . DB_PREFIX . "vw_production_expense`";
        if($filterSQL) {
            $sql .= " WHERE " . $filterSQL;
        }

        $query = $this->db->query($sql);
        $total = $query->row['total'];

        $sql = "SELECT *";
        $sql .= " FROM `" . DB_PREFIX .  "vw_production_expense`";
        if($filterSQL) {
            $sql .= " WHERE " . $filterSQL;
        }
        if($criteriaSQL) {
            $sql .= $criteriaSQL;
        }

        $query = $this->db->query($sql);
        $lists = $query->rows;

        return array('table_total' => $table_total, 'total' => $total, 'lists' => $lists);

    }
}

?>