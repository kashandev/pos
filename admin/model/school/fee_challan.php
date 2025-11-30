<?php

class ModelSchoolFeeChallan extends HModel {

    protected function getTable() {
        return 'sch_fee_challan';
    }

    public function getMaxChallanNo($filter = array()) {
        $sql = "SELECT CASE WHEN MAX(challan_no)+1 IS NULL THEN 1 ELSE MAX(challan_no)+1 END AS max_challan_no";
        $sql .= " FROM `".$this->getTable()."`";
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

        $query = $this->db->query($sql);
        $row = $query->row;

        return array(
            'challan_no' => $row['max_challan_no'],
            'challan_identity' => str_pad($row['max_challan_no'],4,'0',STR_PAD_LEFT)
        );
    }
}

?>