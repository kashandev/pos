<?php

class HModel extends Model {
    protected $isAdmin = false;
    protected $canAudit = true;
    protected $conn;
    public function __construct($registry) {
        $this->registry = $registry;
        if($this->isAdmin) {
            $this->conn = $this->db2;
        } else {
            $this->db->select_db($this->session->data['db_name']);
            $this->conn = $this->db;
        }
    }

    protected function getView() {
        return $this->getTable();
    }
    public function where_eq($column, $value) {
        $this->arrWhere[] = "$column = '$value'";
        return $this;
    }

    public function where_not_eq($column, $value) {
        $this->arrWhere[] = "$column <> '$value'";
        return $this;
    }

    public function where_gt($column, $value) {
        $this->arrWhere[] = "$column > '$value'";
        return $this;
    }

    public function where_gte($column, $value) {
        $this->arrWhere[] = "$column >= '$value'";
        return $this;
    }

    public function where_lt($column, $value) {
        $this->arrWhere[] = "$column < '$value'";
        return $this;
    }

    public function where_lte($column, $value) {
        $this->arrWhere[] = "$column <= '$value'";
        return $this;
    }

    protected function getPrimaryKey() {
        $column = $this->getPrimaryKeyColumn($this->getTable());
        return $column['column'];
    }

    public function executeMultipleQuery($sql) {
        $this->conn->multi_query($sql);
    }

    public function getCount($filter=array()) {
        $sql = "SELECT count(*) as total_rows";
        $sql .= " FROM `" . DB_PREFIX . $this->getTable() . "`";
        if($filter) {
            if(is_array($filter)) {
                //$table_columns = $this->getTableColumns($this->getTable());
                $implode = array();
                foreach($filter as $column => $value) {
                    //if(in_array($column,$table_columns)) {
                    $implode[] = "`$column`='$value'";
                    //}
                }
                if($implode)
                    $sql .= " WHERE " . implode(" AND ", $implode);
            } else {
                $sql .= " WHERE " . $filter;
            }
        }

        $query = $this->conn->query($sql);
        $row = $query->row;
        return $row['total_rows'];
    }

    public function getSQL($filter=array(), $sort_order=array()) {
        $sql = "SELECT *";
        $sql .= " FROM " . DB_PREFIX . $this->getView();
        if($filter) {
            if(is_array($filter)) {
                //$table_columns = $this->getTableColumns($this->getTable());
                $implode = array();
                foreach($filter as $column => $value) {
                    //if(in_array($column,$table_columns)) {
                    $implode[] = "`$column`='$value'";
                    //}
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

        return $sql;
    }

    public function getRow($filter=array(), $sort_order=array()) {
        $sql = "SELECT *";
        $sql .= " FROM " . DB_PREFIX . $this->getView();
        if($filter) {
            if(is_array($filter)) {
                //$table_columns = $this->getTableColumns($this->getTable());
                $implode = array();
                foreach($filter as $column => $value) {
                    //if(in_array($column,$table_columns)) {
                    $implode[] = "`$column`='$value'";
                    //}
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

        $query = $this->conn->query($sql);
        return $query->row;
    }

    public function getRows($filter=array(), $sort_order=array()) {
        $sql = "SELECT *";
        $sql .= " FROM " . DB_PREFIX . $this->getView();
        if($filter) {
            if(is_array($filter)) {
                //$table_columns = $this->getTableColumns($this->getTable());
                $implode = array();
                foreach($filter as $column => $value) {
                    //if(in_array($column,$table_columns)) {
                    $implode[] = "`$column`='$value'";
                    //}
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
        $query = $this->conn->query($sql);
        return $query->rows;
    }

    public function getDistinctRows($fields = array(), $filter=array(), $sort_order=array()) {
        $sql = "SELECT DISTINCT " . implode(',',$fields);
        $sql .= " FROM " . DB_PREFIX . $this->getView();
        if($filter) {
            if(is_array($filter)) {
                //$table_columns = $this->getTableColumns($this->getTable());
                $implode = array();
                foreach($filter as $column => $value) {
                    //if(in_array($column,$table_columns)) {
                    $implode[] = "`$column`='$value'";
                    //}
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

        $query = $this->conn->query($sql);
        return $query->rows;
    }

    public function getArrays($field, $value, $filter=array(), $sort_order=array(), $value_separator = '') {
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

    public function add($document, $data) {
        return $this->hinsert($document, $this->getTable(), $data);
    }

    protected function hinsert($document, $table, $data) {
        if(!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        if(!isset($data['created_by_id'])) {
            $data['created_by_id'] = $this->session->data['user_id'];
        }
        $table_column = $this->getTableColumns($table);
        $primary_column = $this->getPrimaryKeyColumn($table);
        if(!$primary_column['is_auto_increment']) {
//            $data[$table . '_id'] = getGUID();
            if(!isset($data[$primary_column['column']])) {
                $data[$primary_column['column']] = getGUID();
            }
        }
        $sql = "INSERT INTO `" . DB_PREFIX . $table . "` SET ";
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

//        d($sql);
        $this->conn->query($sql);
        if($primary_column['is_auto_increment']) {
            $insert_id = $this->conn->getLastId();
        } else {
            $insert_id = $data[$primary_column['column']];
        }
        //d(array($data, $sql, $insert_id), true);
        if($this->canAudit) {
            $this->audit("INSERT",$document, $table, $insert_id, $data, $sql);
        }

        return $insert_id;
    }

    public function edit($document, $id, $data) {
        return $this->hupdate($document, $this->getTable(), $id, $data);
    }

    protected function hupdate($document, $table, $id, $data) {
        $table_column = $this->getTableColumns($table);
        $sql = "UPDATE `" . DB_PREFIX . $table . "` SET";
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
        $sql .= " WHERE `" . $this->getPrimaryKey() . "` = '" . $id  . "'";
      //  d(array($data, $sql), true);
        $this->conn->query($sql);

        if($this->canAudit) {
            $this->audit("UPDATE",$document, $table, $id, $data, $sql);
        }
        return $id;
    }

    public function delete($document, $id) {
        $this->hdelete($document, $this->getTable(), $id);
    }

    public function deleteBulk($document, $filter) {
        if($filter) {
            $rows = $this->getRows($filter);
            foreach($rows as $row) {
                $this->hdelete($document, $this->getTable(), $row[$this->getPrimaryKey()]);
            }
        }
    }

    public function truncate($document) {
        $table = $this->getTable();
        $sql = "TRUNCATE TABLE `" . DB_PREFIX . $table . "`";
        $this->conn->query($sql);

        if($this->canAudit) {
            $this->audit("TRUNCATE",$document, $table,'',array(), $sql);
        }
    }

    protected function hdelete($document, $table, $id) {
        $row = $this->getRow(array($this->getPrimaryKey() => $id));

        $sql = "DELETE FROM `" . DB_PREFIX . $table . "` WHERE `" . $this->getPrimaryKey() . "` = '" . $id  . "'";
        $this->conn->query($sql);

        if($this->canAudit) {
            $this->audit("DELETE",$document, $table, $id, $row, $sql);
        }
    }

    public function getLists($data) {
        if(!isset($data['filter'])) {
            $data['filter'] = array();
        }
        if(!isset($data['criteria'])) {
            $data['criteria'] = array();
        }
        $filterSQL = $this->getFilterString($data['filter']);
        $criteriaSQL = $this->getCriteriaString($data['criteria']);

        $sql = "SELECT count(*) as total";
        $sql .= " FROM " . DB_PREFIX . $this->getView();
        if($filterSQL) {
            $sql .= " WHERE " . $filterSQL;
        }
        $query = $this->conn->query($sql);
        $table_total = $query->row['total'];

        $sql = "SELECT count(*) as total";
        $sql .= " FROM " . DB_PREFIX . $this->getView();
        if($filterSQL) {
            $sql .= " WHERE " . $filterSQL;
        }
        $query = $this->conn->query($sql);
        $total = $query->row['total'];

        $sql = "SELECT *";
        $sql .= " FROM " . DB_PREFIX . $this->getView();
        if($filterSQL) {
            $sql .= " WHERE " . $filterSQL;
        }
        if($criteriaSQL) {
            $sql .= $criteriaSQL;
        }

        $query = $this->conn->query($sql);
        $lists = $query->rows;

        return array('table_total' => $table_total, 'total' => $total, 'lists' => $lists);

    }

    protected function getCriteriaString($criteria) {
        $sql = '';
        if (isset($criteria['orderby']) && $criteria['orderby']) {
            $sql .= $criteria['orderby'];
        } elseif (isset($criteria['order']) && $criteria['order']) {
            $sql .= " ORDER BY " . $criteria['order'];
            if(isset($criteria['sort']) && $criteria['sort']) {
                $sql .= " " . $criteria['sort'];
            } else {
                $sql .= " DESC";
            }
        }

        if (isset($criteria['start']) || isset($criteria['limit'])) {
            if ($criteria['start'] < 0) {
                $criteria['start'] = 0;
            }

            if ($criteria['limit'] < 1) {
                $criteria['limit'] = 20;
            }

            $sql .= " LIMIT " . (int) $criteria['start'] . "," . (int) $criteria['limit'];
        }

        return $sql;
    }

    protected function getFilterString($filter) {
        $cond = array();
        if(isset($filter['RAW']) && $filter['RAW']) {
            return $filter['RAW'];
        } else {
            if(isset($filter['EQ'])) {
                $cond = array_merge($cond,$this->getFilterEQ($filter['EQ']));
            }
            if(isset($filter['LT'])) {
                $cond = array_merge($cond,$this->getFilterLT($filter['LT']));
            }
            if(isset($filter['LTE'])) {
                $cond = array_merge($cond,$this->getFilterLTE($filter['LTE']));
            }
            if(isset($filter['GT'])) {
                $cond = array_merge($cond,$this->getFilterGT($filter['GT']));
            }
            if(isset($filter['GTE'])) {
                $cond = array_merge($cond,$this->getFilterGTE($filter['GTE']));
            }
            if(isset($filter['LKB'])) {
                $cond = array_merge($cond,$this->getFilterLKB($filter['LKB']));
            }
            if(isset($filter['LKF'])) {
                $cond = array_merge($cond,$this->getFilterLKF($filter['LKF']));
            }
            if(isset($filter['LKE'])) {
                $cond = array_merge($cond,$this->getFilterLKE($filter['LKE']));
            }
            return implode(' AND ', $cond);

        }
    }

    private function getFilterEQ($data) {
        $cond = array();
        foreach($data as $column => $value) {
            if(!empty($value)) {
                $cond[] = $column . "='" . addslashes($value) . "'";
            }
        }
        return $cond;
    }

    private function getFilterGT($data) {
        $cond = array();
        foreach($data as $column => $value) {
            if(!empty($value)) {
                $cond[] = $column . ">'" . addslashes($value) . "'";
            }
        }
        return $cond;
    }

    private function getFilterGTE($data) {
        $cond = array();
        foreach($data as $column => $value) {
            if(!empty($value)) {
                $cond[] = $column . ">='" . addslashes($value) . "'";
            }
        }
        return $cond;
    }

    private function getFilterLT($data) {
        $cond = array();
        foreach($data as $column => $value) {
            if(!empty($value)) {
                $cond[] = $column . " < '" . addslashes($value) . "'";
            }
        }
        return $cond;
    }

    private function getFilterLTE($data) {
        $cond = array();
        foreach($data as $column => $value) {
            if(!empty($value)) {
                $cond[] = $column . "<='" . addslashes($value) . "'";
            }
        }
        return $cond;
    }

    private function getFilterLKB($data) {
        $cond = array();
        foreach($data as $column => $value) {
            if(!empty($value)) {
                $cond[] = $column . " LIKE '%" . addslashes($value) . "%'";
            }
        }
        return $cond;
    }

    private function getFilterLKF($data) {
        $cond = array();
        foreach($data as $column => $value) {
            if(!empty($value)) {
                $cond[] = $column . " LIKE '%" . addslashes($value) . "'";
            }
        }
        return $cond;
    }

    private function getFilterLKE($data) {
        $cond = array();
        foreach($data as $column => $value) {
            if(!empty($value)) {
                $cond[] = $column . " LIKE '" . addslashes($value) . "%'";
            }
        }
        return $cond;
    }

    private function audit($transaction_type, $document, $transaction_table, $transaction_id, $data = array(), $sql_query='') {
        $sql = "SELECT CONNECTION_ID() as connection_id";
        $query = $this->conn->query($sql);
        $connection_id = $query->row['connection_id'];

        $sql = "INSERT INTO `" . DB_PREFIX . "core_audit` SET";
        $sql .= " batch_identity = '" . $connection_id . "'";
        $sql .= ", company_id = '" . $this->session->data['company_id'] . "'";
        $sql .= ", company_branch_id = '" . $this->session->data['company_branch_id'] . "'";
        $sql .= ", fiscal_year_id = '" . $this->session->data['fiscal_year_id'] . "'";
        $sql .= ", document = '" . $document . "'";
        $sql .= ", transaction_type = '" . $transaction_type . "'";
        $sql .= ", transaction_table = '" . $transaction_table . "'";
        $sql .= ", transaction_id = '" . $transaction_id . "'";
        $sql .= ", query = '" . $this->conn->escape($sql_query) . "'";
        $sql .= ", created_by_id = '" . $this->session->data['user_id'] . "'";
        $sql .= ", created_at = '" . date('Y-m-d H:i:s') . "'";
        $this->conn->query($sql);
        $audit_id = $this->conn->getLastId();

        if(!empty($data)) {
            foreach($data as $column => $value) {
                $sql = "INSERT INTO `" . DB_PREFIX . "core_audit_detail` SET";
                $sql .= " audit_id = '" . $audit_id . "'";
                $sql .= ", field = '" . $column . "'";
                $sql .= ", value = '" . $this->conn->escape($value) . "'";

                $this->conn->query($sql);
            }
        }
    }

    protected function getTableColumns($table) {
        $table_column = array();
        $sql = "SHOW COLUMNS FROM `" . DB_PREFIX . $table . "`";
        $query = $this->conn->query($sql);
        $rows = $query->rows;
        foreach($rows as $row) {
            $table_column[] = $row['Field'];
        }
        return $table_column;
    }

    private function getPrimaryKeyColumn($table) {
        $column = array();
        $sql = "SHOW COLUMNS FROM `" . DB_PREFIX . $table . "`";
        $query = $this->conn->query($sql);
        $rows = $query->rows;
        foreach($rows as $row) {
            if($row['Key'] == 'PRI') {
                if($row['Extra'] == 'auto_increment') {
                    $is_auto_increment = 1;
                } else {
                    $is_auto_increment = 0;
                }
                $column = array(
                    'column' => $row['Field'],
                    'is_auto_increment' => $is_auto_increment
                );
            }
        }
        return $column;
    }

}

?>