<?php

class ModelSchoolPreregistration extends HModel {

    protected function getTable() {
        return 'sch_preregistration';
    }

    protected function getView() {
        return 'vw_sch_preregistration';
    }

    public function getMaxRegistrationNo($filter = array()) {
        $sql = "SELECT CASE WHEN MAX(preregistration_no)+1 IS NULL THEN 1 ELSE MAX(preregistration_no)+1 END AS max_preregistration_no";
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
            'preregistration_no' => $row['max_preregistration_no'],
            'preregistration_identity' => 'PREG-' . str_pad($row['max_preregistration_no'],4,'0',STR_PAD_LEFT)
        );
    }


    public function unallottedPreregistration($filter) {
        $sql = "";
        $sql .= " SELECT pr.*";
        $sql .= " FROM `".$this->getView()."` pr ";
        $sql .= " WHERE pr.`student_id` NOT IN (";
        $sql .= " SELECT DISTINCT partner_id";
        $sql .= " FROM `sch_fee_challan` c   ";
        $sql .= " WHERE c.`company_id` = '".$filter['company_id']."'";
        $sql .= " AND c.`company_branch_id` = '".$filter['company_branch_id']."'";
        $sql .= " AND c.`fiscal_year_id` = '".$filter['fiscal_year_id']."'";
        $sql .= " AND c.`challan_type` = 'Admission Challan'";
        $sql .= " AND c.`partner_type_id` = '4'";
        $sql .= " AND c.`fee_challan_id` != '".$filter['fee_challan_id']."' ";
        $sql .= " ) ";
        $sql .= " AND pr.`company_id` = '".$filter['company_id']."' AND pr.`company_branch_id` = '".$filter['company_branch_id']."' AND pr.`fiscal_year_id` = '".$filter['fiscal_year_id']."'";

        $query = $this->db->query($sql);
        return $query->rows;

    }

}

?>