<?php

class ModelInventorySaleDiscountPolicy extends HModel {

    protected function getTable() {
        return 'sale_discount_policy';
    }

    protected function getView() {
        return 'vw_sale_discount_policy';
    }

    public function getDiscountPolicy($filter) {
        $sql = "";
        $sql .= "SELECT `partner_type_id`, `partner_id`, `start_date`, `end_date`, 'Product' as type,product_id AS id, discount_percent";
        $sql .= " FROM `sale_discount_policy` sdp";
        $sql .= " INNER JOIN `sale_discount_policy_detail` sdpd ON sdp.`sale_discount_policy_id` = sdpd.`sale_discount_policy_id`";
        $sql .= " WHERE `product_id` != ''";
        $sql .= " AND `company_id` = '".$filter['company_id']."'";
        $sql .= " AND `company_branch_id` = '".$filter['company_branch_id']."'";
        $sql .= " AND '".$filter['document_date']."' >= `start_date` AND '".$filter['document_date']."' <= `end_date`";
        $sql .= " AND `partner_id` = '".$filter['partner_id']."'";
        $sql .= " UNION";
        $sql .= " SELECT `partner_type_id`, `partner_id`, `start_date`, `end_date`, 'Category' as type,product_category_id AS id, discount_percent";
        $sql .= " FROM `sale_discount_policy` sdp";
        $sql .= " INNER JOIN `sale_discount_policy_detail` sdpd ON sdp.`sale_discount_policy_id` = sdpd.`sale_discount_policy_id`";
        $sql .= " WHERE `product_category_id` != '' AND `product_id` = ''";
        $sql .= " AND `company_id` = '".$filter['company_id']."'";
        $sql .= " AND `company_branch_id` = '".$filter['company_branch_id']."'";
        $sql .= " AND '".$filter['document_date']."' >= `start_date` AND '".$filter['document_date']."' <= `end_date`";
        $sql .= " AND `partner_id` = '".$filter['partner_id']."'";
        $sql .= " UNION";
        $sql .= " SELECT `partner_type_id`, `partner_id`, `start_date`, `end_date`, 'General' as type,'' AS id, discount_percent";
        $sql .= " FROM `sale_discount_policy` sdp";
        $sql .= " INNER JOIN `sale_discount_policy_detail` sdpd ON sdp.`sale_discount_policy_id` = sdpd.`sale_discount_policy_id`";
        $sql .= " WHERE `product_category_id` = '' AND `product_id` = ''";
        $sql .= " AND `company_id` = '".$filter['company_id']."'";
        $sql .= " AND `company_branch_id` = '".$filter['company_branch_id']."'";
        $sql .= " AND '".$filter['document_date']."' >= `start_date` AND '".$filter['document_date']."' <= `end_date`";
        $sql .= " AND `partner_id` = '".$filter['partner_id']."'";

        $query = $this->conn->query($sql);
        return $query->rows;

    }
}

?>