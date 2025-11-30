<?php

class ModelReportCollectionRegister extends HModel {
    public function getReports($where, $sort_order) {
        $sql = "";
        $sql .= " SELECT `member_receipt_id` AS id, `receipt_date`, 'Member Receipt' AS receipt_type, `receipt_identity`, `receipt_amount`";
        $sql .= " , 'Member' AS partner_type, `member_id` AS partner_id, `member_name` AS partner_name";
        $sql .= " , `sport_id`, `sport_name`";
        $sql .= " , `created_at`";
        $sql .= " FROM `vw_member_receipt_detail`";
        if($where != '') {
            $sql .= ' HAVING ' . $where;
        }
        $sql .= " UNION ALL";
        $sql .= " SELECT `donor_receipt_id` AS id, `receipt_date`, 'Donor Receipt' AS receipt_type, `receipt_identity`, `receipt_amount`";
        $sql .= " , 'Donor' AS partner_type, `member_id` AS partner_id, `member_name` AS partner_name";
        $sql .= " , `sport_id`, `sport_name`";
        $sql .= " , `created_at`";
        $sql .= " FROM `vw_donor_receipt_detail`";
        if($where != '') {
            $sql .= ' HAVING ' . $where;
        }
        $sql .= " UNION ALL";
        $sql .= " SELECT `general_receipt_id` AS id, `receipt_date`, 'Donor Receipt' AS receipt_type, `receipt_identity`, `receipt_amount`";
        $sql .= " , 'Donor' AS partner_type, `member_id` AS partner_id, `member_name` AS partner_name";
        $sql .= " , `sport_id`, `sport_name`";
        $sql .= " , `created_at`";
        $sql .= " FROM `vw_general_receipt`";
        if($where != '') {
            $sql .= ' HAVING ' . $where;
        }
        $sql .= " UNION ALL";
        $sql .= " SELECT `ground_receipt_id` AS id, `receipt_date`, 'Ground Receipt' AS receipt_type, `receipt_identity`, `receipt_amount`";
        $sql .= " , 'Member' AS partner_type, `member_id` AS partner_id, `member_name` AS partner_name";
        $sql .= " , '' AS `sport_id`, '' AS `sport_name`";
        $sql .= " , `created_at`";
        $sql .= " FROM `vw_ground_receipt`";
        if($where != '') {
            $sql .= ' HAVING ' . $where;
        }

        if($sort_order) {
            $sql .= " ORDER BY " . implode(',', $sort_order);
        }

        $query = $this->conn->query($sql);
        return $query->rows;
    }

}

?>