<?php

class ModelReportEntityProductLedger extends HModel {

    protected function getTable() {
        return 'temp_balance_sheet';
    }

    private function fillPartyLedger($filter=array()) {
        $sql = '';
        $sql .= " DROP TABLE IF EXISTS `temp_party_ledger`;" . "\n";
        $this->conn->query($sql);

        $sql = '';
        $sql .= " CREATE TABLE `temp_party_ledger`( `people_type_id` INT NOT NULL, `people_id` CHAR(40) NOT NULL, `document_type_id` INT NOT NULL, `document_id` CHAR(40) NOT NULL, `document_identity` VARCHAR(255), `document_date` DATE, `remarks` VARCHAR(255), `manual_ref_no` VARCHAR(255), `product_id` CHAR(40), `qty` INT DEFAULT 0, `rate` DECIMAL(11,2) DEFAULT 0.00, `debit` DECIMAL(17,2) DEFAULT 0.00, `credit` DECIMAL(17,2) DEFAULT 0.00, `created_at` DATETIME NULL, INDEX `people_type_id` (`people_type_id`), INDEX `people_id` (`people_id`), INDEX `document_type_id` (`document_type_id`), INDEX `document_id` (`document_id`), INDEX `document_date` (`document_date`), INDEX `product_id` (`product_id`) ) ENGINE=INNODB;" . "\n";
        $this->conn->query($sql);

        $sql = '';
        $sql .= " INSERT INTO `temp_party_ledger`" . "\n";
        $sql .= " SELECT d.people_type_id, d.people_id" . "\n";
        $sql .= " , d.ref_document_type_id AS document_type_id, d.opening_account_detail_id AS document_id, d.ref_document_identity AS ducument_identity, m.`document_date`" . "\n";
        $sql .= " , m.remarks, '' as manual_ref_no" . "\n";
        $sql .= " ,'' AS product_id, 0 AS qty, 0 AS rate" . "\n";
        $sql .= " , (m.`conversion_rate` * d.debit) AS debit, (m.`conversion_rate` * d.credit) AS credit" . "\n";
        $sql .= " , m.created_at" . "\n";
        $sql .= " FROM `opening_account` m" . "\n";
        $sql .= " INNER JOIN `opening_account_detail` d ON d.opening_account_id = m.`opening_account_id`" . "\n";
        $sql .= " WHERE TRUE" . "\n";
        $sql .= " AND m.company_id = " . $filter['company_id'] . "\n";
        $sql .= " AND m.company_branch_id = '" . $filter['company_branch_id'] . "'" . "\n";
        $sql .= " AND m.fiscal_year_id = " . $filter['fiscal_year_id'] . "" . "\n";
        if(isset($filter['people_type_id'])) {
            $sql .= " AND d.people_type_id = " . $filter['people_type_id'] . "" . "\n";
        }
        if(isset($filter['people_id'])) {
            $sql .= " AND d.people_id = '" . $filter['people_id'] . "'" . "\n";
        }
        if(isset($filter['date_to'])) {
            $sql .= " AND m.document_date <= '" . $filter['date_to'] . "'" . "\n";
        }
        $sql .= " UNION ALL" . "\n";
        $sql .= " SELECT 1 AS people_type_id, m.supplier_id AS people_id" . "\n";
        $sql .= " , m.document_type_id, m.purchase_invoice_id AS document_id, m.invoice_no AS document_identity, m.invoice_date AS document_date" . "\n";
        $sql .= " , m.remarks, '' as manual_ref_no" . "\n";
        $sql .= " , d.product_id, d.qty, (d.rate * m.conversion_rate) AS rate" . "\n";
        $sql .= " , 0 AS debit, (d.total * m.conversion_rate) AS credit" . "\n";
        $sql .= " , m.created_at" . "\n";
        $sql .= " FROM purchase_invoice m" . "\n";
        $sql .= " INNER JOIN `purchase_invoice_detail` d ON d.purchase_invoice_id = m.purchase_invoice_id" . "\n";
        $sql .= " WHERE TRUE" . "\n";
        $sql .= " AND m.company_id = " . $filter['company_id'] . "\n";
        $sql .= " AND m.company_branch_id = '" . $filter['company_branch_id'] . "'" . "\n";
        $sql .= " AND m.fiscal_year_id = " . $filter['fiscal_year_id'] . "" . "\n";
        if(isset($filter['people_type_id'])) {
            $sql .= " AND 1 = " . $filter['people_type_id'] . "" . "\n";
        }
        if(isset($filter['people_id'])) {
            $sql .= " AND m.supplier_id = '" . $filter['people_id'] . "'" . "\n";
        }
        if(isset($filter['date_to'])) {
            $sql .= " AND m.invoice_date <= '" . $filter['date_to'] . "'" . "\n";
        }
        $sql .= " UNION ALL" . "\n";
        $sql .= " SELECT 1 AS people_type_id, m.supplier_id AS people_id" . "\n";
        $sql .= " , m.document_type_id, m.purchase_return_id AS document_id, m.document_identity, m.document_date" . "\n";
        $sql .= " , m.remarks, '' as manual_ref_no" . "\n";
        $sql .= " , d.product_id, d.qty, (d.rate * m.conversion_rate) AS rate" . "\n";
        $sql .= " , (d.total * m.conversion_rate) AS debit, 0 AS credit" . "\n";
        $sql .= " , m.created_at" . "\n";
        $sql .= " FROM purchase_return m" . "\n";
        $sql .= " INNER JOIN `purchase_return_detail` d ON d.purchase_return_id = m.purchase_return_id" . "\n";
        $sql .= " WHERE TRUE" . "\n";
        $sql .= " AND m.company_id = " . $filter['company_id'] . "\n";
        $sql .= " AND m.company_branch_id = '" . $filter['company_branch_id'] . "'" . "\n";
        $sql .= " AND m.fiscal_year_id = " . $filter['fiscal_year_id'] . "" . "\n";
        if(isset($filter['people_type_id'])) {
            $sql .= " AND 1 = " . $filter['people_type_id'] . "" . "\n";
        }
        if(isset($filter['people_id'])) {
            $sql .= " AND m.supplier_id = '" . $filter['people_id'] . "'" . "\n";
        }
        if(isset($filter['date_to'])) {
            $sql .= " AND m.document_date <= '" . $filter['date_to'] . "'" . "\n";
        }
        $sql .= " UNION ALL" . "\n";
        $sql .= " SELECT m.people_type_id, m.people_id" . "\n";
        $sql .= " , m.document_type_id, m.cash_payment_id AS document_id, m.voucher_no AS document_identity, m.voucher_date AS document_date" . "\n";
        $sql .= " , m.remarks, '' as manual_ref_no" . "\n";
        $sql .= " , '' AS product_id, 0 AS qty, 0 AS rate" . "\n";
        $sql .= " , (d.amount * m.conversion_rate) AS debit, 0 AS credit" . "\n";
        $sql .= " , m.created_at" . "\n";
        $sql .= " FROM cash_payment m" . "\n";
        $sql .= " INNER JOIN `cash_payment_detail` d ON d.cash_payment_id = m.cash_payment_id" . "\n";
        $sql .= " WHERE TRUE" . "\n";
        $sql .= " AND m.company_id = " . $filter['company_id'] . "\n";
        $sql .= " AND m.company_branch_id = '" . $filter['company_branch_id'] . "'" . "\n";
        $sql .= " AND m.fiscal_year_id = " . $filter['fiscal_year_id'] . "" . "\n";
        if(isset($filter['people_type_id'])) {
            $sql .= " AND m.people_type_id = " . $filter['people_type_id'] . "" . "\n";
        }
        if(isset($filter['people_id'])) {
            $sql .= " AND m.people_id = '" . $filter['people_id'] . "'" . "\n";
        }
        if(isset($filter['date_to'])) {
            $sql .= " AND m.voucher_date <= '" . $filter['date_to'] . "'" . "\n";
        }
        $sql .= " UNION ALL" . "\n";
        $sql .= " SELECT m.people_type_id, m.people_id" . "\n";
        $sql .= " , m.document_type_id, m.bank_payment_id AS document_id, m.voucher_no AS document_identity, m.voucher_date AS document_date" . "\n";
        $sql .= " , m.remarks, '' as manual_ref_no" . "\n";
        $sql .= " , '' AS product_id, 0 AS qty, 0 AS rate" . "\n";
        $sql .= " , (d.amount * m.conversion_rate) AS debit, 0 AS credit" . "\n";
        $sql .= " , m.created_at" . "\n";
        $sql .= " FROM bank_payment m" . "\n";
        $sql .= " INNER JOIN `bank_payment_detail` d ON d.bank_payment_id = m.bank_payment_id" . "\n";
        $sql .= " WHERE TRUE" . "\n";
        $sql .= " AND m.company_id = " . $filter['company_id'] . "\n";
        $sql .= " AND m.company_branch_id = '" . $filter['company_branch_id'] . "'" . "\n";
        $sql .= " AND m.fiscal_year_id = " . $filter['fiscal_year_id'] . "" . "\n";
        if(isset($filter['people_type_id'])) {
            $sql .= " AND m.people_type_id = " . $filter['people_type_id'] . "" . "\n";
        }
        if(isset($filter['people_id'])) {
            $sql .= " AND m.people_id = '" . $filter['people_id'] . "'" . "\n";
        }
        if(isset($filter['date_to'])) {
            $sql .= " AND m.voucher_date <= '" . $filter['date_to'] . "'" . "\n";
        }
        $sql .= " UNION ALL" . "\n";
        $sql .= " SELECT 2 AS people_type_id, m.customer_id AS people_id" . "\n";
        $sql .= " , m.document_type_id, m.sale_invoice_id AS document_id, m.invoice_no AS document_identity, m.invoice_date AS document_date" . "\n";
        $sql .= " , d.remarks, m.manual_ref_no" . "\n";
        $sql .= " , d.product_id, d.qty, (d.rate * m.conversion_rate) AS rate" . "\n";
        $sql .= " , (d.total * m.conversion_rate) AS debit, 0 AS credit" . "\n";
        $sql .= " , m.created_at" . "\n";
        $sql .= " FROM sale_invoice m" . "\n";
        $sql .= " INNER JOIN `sale_invoice_detail` d ON d.sale_invoice_id = m.sale_invoice_id" . "\n";
        $sql .= " WHERE TRUE" . "\n";
        $sql .= " AND m.company_id = " . $filter['company_id'] . "\n";
        $sql .= " AND m.company_branch_id = '" . $filter['company_branch_id'] . "'" . "\n";
        $sql .= " AND m.fiscal_year_id = " . $filter['fiscal_year_id'] . "" . "\n";
        if(isset($filter['people_type_id'])) {
            $sql .= " AND 2 = " . $filter['people_type_id'] . "" . "\n";
        }
        if(isset($filter['people_id'])) {
            $sql .= " AND m.customer_id = '" . $filter['people_id'] . "'" . "\n";
        }
        if(isset($filter['date_to'])) {
            $sql .= " AND m.invoice_date <= '" . $filter['date_to'] . "'" . "\n";
        }
        $sql .= " UNION ALL" . "\n";
        $sql .= " SELECT 2 AS people_type_id, m.customer_id AS people_id" . "\n";
        $sql .= " , m.document_type_id, m.sale_return_id AS document_id, m.invoice_no AS document_identity, m.invoice_date AS document_date" . "\n";
        $sql .= " , m.remarks, '' as manual_ref_no" . "\n";
        $sql .= " , d.product_id, d.qty, (d.rate * m.conversion_rate) AS rate" . "\n";
        $sql .= " , 0 as debit, (d.total * m.conversion_rate) AS credit" . "\n";
        $sql .= " , m.created_at" . "\n";
        $sql .= " FROM sale_return m" . "\n";
        $sql .= " INNER JOIN `sale_return_detail` d ON d.sale_return_id = m.sale_return_id" . "\n";
        $sql .= " WHERE TRUE" . "\n";
        $sql .= " AND m.company_id = " . $filter['company_id'] . "\n";
        $sql .= " AND m.company_branch_id = '" . $filter['company_branch_id'] . "'" . "\n";
        $sql .= " AND m.fiscal_year_id = " . $filter['fiscal_year_id'] . "" . "\n";
        if(isset($filter['people_type_id'])) {
            $sql .= " AND 2 = " . $filter['people_type_id'] . "\n";
        }
        if(isset($filter['people_id'])) {
            $sql .= " AND m.customer_id = '" . $filter['people_id'] . "'" . "\n";
        }
        if(isset($filter['date_to'])) {
            $sql .= " AND m.invoice_date <= '" . $filter['date_to'] . "'" . "\n";
        }
        $sql .= " UNION ALL" . "\n";
        $sql .= " SELECT m.people_type_id, m.people_id" . "\n";
        $sql .= " , m.document_type_id, m.bank_receipt_id AS document_id, m.voucher_no AS document_identity, m.voucher_date AS document_date" . "\n";
        $sql .= " , m.remarks, '' as manual_ref_no" . "\n";
        $sql .= " , '' AS product_id, 0 AS qty, 0 AS rate" . "\n";
        $sql .= " , 0 AS debit, (d.amount * m.conversion_rate) AS credit" . "\n";
        $sql .= " , m.created_at" . "\n";
        $sql .= " FROM `bank_receipt` m" . "\n";
        $sql .= " INNER JOIN `bank_receipt_detail` d ON d.bank_receipt_id = m.bank_receipt_id" . "\n";
        $sql .= " WHERE TRUE" . "\n";
        $sql .= " AND m.company_id = " . $filter['company_id'] . "\n";
        $sql .= " AND m.company_branch_id = '" . $filter['company_branch_id'] . "'" . "\n";
        $sql .= " AND m.fiscal_year_id = " . $filter['fiscal_year_id'] . "" . "\n";
        if(isset($filter['people_type_id'])) {
            $sql .= " AND m.people_type_id = " . $filter['people_type_id'] . "" . "\n";
        }
        if(isset($filter['people_id'])) {
            $sql .= " AND m.people_id = '" . $filter['people_id'] . "'" . "\n";
        }
        if(isset($filter['date_to'])) {
            $sql .= " AND m.voucher_date <= '" . $filter['date_to'] . "'" . "\n";
        }
        $sql .= " UNION ALL" . "\n";
        $sql .= " SELECT m.people_type_id, m.people_id" . "\n";
        $sql .= " , m.document_type_id, m.cash_receipt_id AS document_id, m.voucher_no AS document_identity, m.voucher_date AS document_date" . "\n";
        $sql .= " , m.remarks, '' as manual_ref_no" . "\n";
        $sql .= " , '' AS product_id, 0 AS qty, 0 AS rate" . "\n";
        $sql .= " , 0 AS debit, (d.amount * m.conversion_rate) AS credit" . "\n";
        $sql .= " , m.created_at" . "\n";
        $sql .= " FROM `cash_receipt` m" . "\n";
        $sql .= " INNER JOIN `cash_receipt_detail` d ON d.cash_receipt_id = m.cash_receipt_id" . "\n";
        $sql .= " WHERE TRUE" . "\n";
        $sql .= " AND m.company_id = " . $filter['company_id'] . "\n";
        $sql .= " AND m.company_branch_id = '" . $filter['company_branch_id'] . "'" . "\n";
        $sql .= " AND m.fiscal_year_id = " . $filter['fiscal_year_id'] . "" . "\n";
        if(isset($filter['people_type_id'])) {
            $sql .= " AND m.people_type_id = " . $filter['people_type_id'] . "" . "\n";
        }
        if(isset($filter['people_id'])) {
            $sql .= " AND m.people_id = '" . $filter['people_id'] . "'" . "\n";
        }
        if(isset($filter['date_to'])) {
            $sql .= " AND m.voucher_date <= '" . $filter['date_to'] . "'" . "\n";
        }
        $sql .= " UNION ALL" . "\n";
        $sql .= " SELECT d.people_type_id, d.people_id" . "\n";
        $sql .= " , m.document_type_id, m.journal_voucher_id AS document_id, m.voucher_no AS document_identity, m.voucher_date AS document_date" . "\n";
        $sql .= " , m.remarks, '' AS manual_ref_no" . "\n";
        $sql .= " , '' AS product_id, 0 AS qty, 0 AS rate" . "\n";
        $sql .= " , (d.debit * m.conversion_rate) AS debit, (d.credit * m.conversion_rate) AS credit" . "\n";
        $sql .= " , m.created_at" . "\n";
        $sql .= " FROM `journal_voucher` m" . "\n";
        $sql .= " INNER JOIN `journal_voucher_detail` d ON d.journal_voucher_id = m.journal_voucher_id" . "\n";
        $sql .= " WHERE TRUE" . "\n";
        $sql .= " AND m.company_id = " . $filter['company_id'] . "\n";
        $sql .= " AND m.company_branch_id = '" . $filter['company_branch_id'] . "'" . "\n";
        $sql .= " AND m.fiscal_year_id = " . $filter['fiscal_year_id'] . "" . "\n";
        if(isset($filter['people_type_id'])) {
            $sql .= " AND d.people_type_id = " . $filter['people_type_id'] . "" . "\n";
        }
        if(isset($filter['people_id'])) {
            $sql .= " AND d.people_id = '" . $filter['people_id'] . "'" . "\n";
        }
        if(isset($filter['date_to'])) {
            $sql .= " AND m.voucher_date <= '" . $filter['date_to'] . "'" . "\n";
        }
        $sql .= " UNION ALL" . "\n";
        $sql .= " SELECT m.people_type_id, m.people_id" . "\n";
        $sql .= " , m.document_type_id, m.advance_payment_id AS document_id, m.voucher_no AS document_identity, m.voucher_date AS document_date" . "\n";
        $sql .= " , m.remarks, '' AS manual_ref_no" . "\n";
        $sql .= " , '' AS product_id, 0 AS qty, 0 AS rate" . "\n";
        $sql .= " , (m.amount * m.conversion_rate) AS debit, 0 AS credit" . "\n";
        $sql .= " , m.created_at" . "\n";
        $sql .= " FROM `advance_payment` m" . "\n";
        $sql .= " WHERE TRUE" . "\n";
        $sql .= " AND m.company_id = " . $filter['company_id'] . "\n";
        $sql .= " AND m.company_branch_id = '" . $filter['company_branch_id'] . "'" . "\n";
        $sql .= " AND m.fiscal_year_id = " . $filter['fiscal_year_id'] . "" . "\n";
        if(isset($filter['people_type_id'])) {
            $sql .= " AND m.people_type_id = " . $filter['people_type_id'] . "" . "\n";
        }
        if(isset($filter['people_id'])) {
            $sql .= " AND m.people_id = '" . $filter['people_id'] . "'" . "\n";
        }
        if(isset($filter['date_to'])) {
            $sql .= " AND m.voucher_date <= '" . $filter['date_to'] . "'" . "\n";
        }
        //d($sql, true);
        $this->conn->query($sql);
    }

    public function getLedger($filter=array(), $sort_order=array()) {
        $this->fillPartyLedger($filter);

        $sql = "";
        $sql .= " SELECT l.*, p.`name` AS product_name, pl.people_name" . "\n";
        $sql .= " FROM (" . "\n";
        $sql .= " SELECT 0 AS sort_order, people_type_id, people_id, '' AS document_type_id, '' AS document_id, '' AS document_identity, '".$filter['date_from']."' AS document_date" . "\n";
        $sql .= " , 'OPENING BALANCE' AS remarks, '' AS manual_ref_no" . "\n";
        $sql .= " , '' AS product_id, 0 AS qty, 0 AS rate" . "\n";
        $sql .= " , CASE WHEN SUM(debit-credit) > 0 THEN SUM(debit-credit) ELSE 0 END AS debit" . "\n";
        $sql .= " , CASE WHEN SUM(credit-debit) > 0 THEN SUM(credit-debit) ELSE 0 END AS credit" . "\n";
        $sql .= " , '".$filter['date_from']."' AS created_at" . "\n";
        $sql .= " FROM `temp_party_ledger`" . "\n";
        $sql .= " WHERE document_date < '" . $filter['date_from'] . "'" . "\n";
        $sql .= " GROUP BY people_type_id, people_id" . "\n";
        $sql .= " UNION ALL" . "\n";
        $sql .= " SELECT 1 AS sort_order, people_type_id, people_id, document_type_id, document_id, document_identity, document_date" . "\n";
        $sql .= " , remarks, manual_ref_no" . "\n";
        $sql .= " , product_id, qty, rate" . "\n";
        $sql .= " , debit" . "\n";
        $sql .= " , credit" . "\n";
        $sql .= " , created_at" . "\n";
        $sql .= " FROM `temp_party_ledger`" . "\n";
        $sql .= " WHERE document_date >= '" . $filter['date_from'] . "' AND document_date <= '" . $filter['date_to'] . "'" . "\n";
        $sql .= " ) l" . "\n";
        $sql .= " LEFT JOIN `product` p ON p.product_id = l.product_id" . "\n";
        $sql .= " INNER JOIN `vw_people` pl ON pl.people_type_id = l.people_type_id AND pl.people_id = l.people_id" . "\n";
        $sql .= " ORDER BY pl.`people_type_id`, pl.`people_name`, l.document_date, l.created_at" . "\n";

        //d($sql, true);
        $query = $this->conn->query($sql);
        return $query->rows;
    }

    public function getSummary($filter=array(), $sort_order=array()) {
        $this->fillPartyLedger($filter);

        $sql = "";
        $sql .= " SELECT l.people_type_id, pl.people_type, l.people_id, pl.people_name, SUM(debit-credit) AS outstanding";
        $sql .= " FROM `temp_party_ledger` l";
        $sql .= " INNER JOIN `vw_people` pl ON pl.people_type_id = l.people_type_id AND pl.people_id = l.people_id" . "\n";
        $sql .= " GROUP BY pl.people_type_id, pl.people_id";
        $sql .= " ORDER BY pl.`people_type_id`, pl.`people_name`" . "\n";

        //d($sql, true);
        $query = $this->conn->query($sql);
        return $query->rows;
    }

}

?>