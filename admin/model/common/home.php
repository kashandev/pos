<?php

class ModelCommonHome extends HModel {

    public function getSales($data) {
        $sql = "SELECT date, sum(amount) as amount";
        $sql .= " FROM vw_receipt_service r";
        $sql .= " WHERE TRUE";
        if(isset($data['date_from']) && $data['date_from']) {
            $sql .= " AND r.date >= '" . $data['date_from'] . "'";
        }

        if(isset($data['date_to']) && $data['date_to']) {
            $sql .= " AND r.date <= '" . $data['date_to'] . "'";
        }
        $sql .= " GROUP BY date";

        $query = $this->conn->query($sql);
        return $query->rows;
    }

    public function getTopCustomer($filter) {
        $sql = "SELECT si.partner_name name,SUM(net_amount) total";
        $sql .= " FROM vw_ins_sale_invoice si";
        $sql .= " WHERE si.company_id = '".$filter['company_id']."' AND si.fiscal_year_id = '".$filter['fiscal_year_id']."' AND si.company_branch_id = '".$filter['company_branch_id']."' ";
        $sql .= " AND DATE_FORMAT(si.document_date,'%Y-%m') = '".date('Y-m')."'";
        $sql .= " GROUP BY si.partner_id";
        $sql .= " ORDER BY SUM(net_amount) DESC";
        $sql .= " LIMIT 5";


        $query = $this->conn->query($sql);
//        d(array($query,$sql),true);
        return $query->rows;
    }
    public function getSale($filter) {
        $sql = "SELECT si.company_id,si.fiscal_year_id,si.company_branch_id,DATE_FORMAT(si.document_date,'%Y-%m') as date,SUM(net_amount) total ";
        $sql .= " FROM vw_sale_invoice si ";
        $sql .= " WHERE si.company_id = '".$filter['company_id']."' AND si.fiscal_year_id = '".$filter['fiscal_year_id']."' AND si.company_branch_id = '".$filter['company_branch_id']."' ";
//        $sql .= " AND DATE_FORMAT(si.document_date,'%Y-%m') = '".date('Y-m')."'";
//        $sql .= " GROUP BY si.document_date";
        $sql .= " GROUP BY DATE_FORMAT(si.document_date,'%Y-%m')";
        $sql .= " ORDER BY si.document_date ASC ";
        $sql .= " LIMIT 31";


        $query = $this->conn->query($sql);
//        d(array($query,$sql),true);
        return $query->rows;
    }

}

?>