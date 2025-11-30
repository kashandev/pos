<?php

final class Company {
    private $company_id;
    private $name;
    private $address;
    private $phone_no;
    private $fax_no;
    private $email;
    private $gst_no;
    private $base_currency_id;
    private $round_decimal_places;
    private $sale_tax_account_id;
    private $purchase_discount_account_id;
    private $sale_discount_account_id;
    private $other_charges_account_id;
    private $branches = array();

    public function __construct($registry) {
        $this->config = $registry->get('config');
        $this->db = $registry->get('db');
        $this->request = $registry->get('request');
        $this->session = $registry->get('session');

        if (isset($this->session->data['company_id'])) {
            $sql = "SELECT * FROM " . DB_PREFIX . "company WHERE company_id = '" . $this->session->data['company_id'] . "' AND status = '1'";
            $query = $this->db->query($sql);
            if ($query->num_rows) {
                $this->company_id = $query->row['company_id'];
                $this->name = $query->row['name'];
                $this->address = $query->row['address'];
                $this->phone_no = $query->row['phone_no'];
                $this->fax_no = $query->row['fax_no'];
                $this->email = $query->row['email'];
                $this->gst_no = $query->row['gst_no'];
                $this->base_currency_id = $query->row['base_currency_id'];
                $this->round_decimal_places = $query->row['round_decimal_places'];
                $this->sale_tax_account_id = $query->row['sale_tax_account_id'];
                $this->purchase_discount_account_id = $query->row['purchase_discount_account_id'];
                $this->sale_discount_account_id = $query->row['sale_discount_account_id'];
                $this->other_charges_account_id = $query->row['other_charges_account_id'];
                
                $sql = "SELECT *";
                $sql .= " FROM " . DB_PREFIX . "company_branch";
                $sql .= " WHERE company_id = '" . $this->session->data['company_id'] . "' AND status = '1'";
                $query = $this->db->query($sql);
                $branches = $query->rows;
                foreach($branches as $branch) {
                    $sql = "SELECT *";
                    $sql .= " FROM " . DB_PREFIX . "company_branch_document_prefix";
                    $sql .= " WHERE company_branch_id = '" . $branch['company_branch_id'] . "'";
                    $query = $this->db->query($sql);
                    $documents = $query->rows;
                    $arrDocuments = array();
                    foreach($documents as $document) {
                        $arrDocuments[$document['document_type_code']] = $document;
                    }
                    
                    $this->branches[$branch['company_branch_id']] = array(
                        'name' => $branch['name'],
                        'address' => $branch['address'],
                        'phone_no' => $branch['phone_no'],
                        'documents' => $arrDocuments
                    );
                }
            }
        }
    }

    public function getId() {
        return $this->company_id;
    }

    public function getName() {
        return $this->name;
    }

    public function getAddress() {
        return $this->address;
    }

    public function getPhoneNo() {
        return $this->phone_no;
    }

    public function getFaxNo() {
        return $this->fax_no;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getGSTNo() {
        return $this->gst_no;
    }

    public function getCurrency() {
        return $this->base_currency_id;
    }

    public function getDecimalPlaces() {
        return $this->round_decimal_places;
    }

    public function getBranches() {
        return $this->branches;
    }

    public function getBranchName($branch_id) {
        return $this->branches[$branch_id]['name'];
    }

    public function getBranchAddress($branch_id) {
        return $this->branches[$branch_id]['address'];
    }

    public function getBranchPhoneNo($branch_id) {
        return $this->branches[$branch_id]['phone_no'];
    }

    public function getBranchDocuments($branch_id) {
        return $this->branches[$branch_id]['documents'];
    }

    public function getBranchDocument($branch_id, $document_type_code) {
        return $this->branches[$branch_id]['documents'][$document_type_code];
    }

    public function getSaleTaxAccount() {
        return $this->sale_tax_account_id;
    }

    public function getPurchaseDiscountAccount() {
        return $this->purchase_discount_account_id;
    }

    public function getSaleDiscountAccount() {
        return $this->sale_discount_account_id;
    }

    public function getOtherChargesAccount() {
        return $this->other_charges_account_id;
    }

    
}

?>