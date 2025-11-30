<?php

class ControllerReportTaxReport extends HController {

    protected function getAlias() {
        return 'report/tax_report';
    }

    protected function getDefaultOrder() {
        return 'sale_tax_invoice_id';
    }

    protected function getDefaultSort() {
        return 'DESC';
    }

    protected function getList() {
        parent::getList();

        $this->data['partner_types'] = $this->session->data['partner_types'];

        $this->model['customer'] = $this->load->model('setup/customer');

        $where = "company_id=" . $this->session->data['company_id'];
        $where .= " AND partner_category_id=" .'1';

        $this->data['partners'] = $this->model['customer']->getRows($where,array('name'));


        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
        $this->data['href_get_product_json'] = $this->url->link($this->getAlias() . '/getProductJson', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_get_detail_report'] = $this->url->link($this->getAlias() .'/getDetailReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_print_report'] = $this->url->link($this->getAlias() .'/printReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['date_from'] = stdDate($this->session->data['fiscal_date_from']);
        $this->data['date_to'] = stdDate(($this->session->data['fiscal_date_to'] > date('Y-m-d') ? '' : $this->session->data['fiscal_date_to']));

        $this->data['strValidation'] = "{
            'rules': {
                'date_from': {'required': true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},
                'date_to': {'required': true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},
            },
            ignore:[],
        }";

        $this->template = $this->getAlias() . '.tpl';
        $this->response->setOutput($this->render());
    }

    public function getProductJson() {
        $search = $this->request->post['q'];
        $page = $this->request->post['page'];

        $this->model['product'] = $this->load->model('inventory/product');
        $rows = $this->model['product']->getProductJson($search, $page);

        echo json_encode($rows);
    }


    public function printReport() {
        $this->init();
        ini_set('memory_limit','1024M');
        $post = $this->request->post;

        $filter = array();
        $filter[] = "`company_id` = '".$this->session->data['company_id']."'";
        $filter[] = "`company_branch_id` = '".$this->session->data['company_branch_id']."'";
        $filter[] = "`fiscal_year_id` = '".$this->session->data['fiscal_year_id']."'";
        $filter[] = "`partner_type_id` = '2'";

        if(isset($post['date_from']) && $post['date_from'] != '') {
            $filter[] = "`document_date` >= '".MySqlDate($post['date_from'])."'";
        }
        if(isset($post['date_to']) && $post['date_to'] != '') {
            $filter[] = "`document_date` <= '".MySqlDate($post['date_to'])."'";
        }
        if(isset($post['partner_id']) && $post['partner_id'] != '') {
            $filter[] = "`partner_id` = '".$post['partner_id']."'";
        }

        $where = implode(' AND ', $filter);
        //d($post, true);
        $this->model['sale_tax_invoice'] = $this->load->model('inventory/sale_tax_invoice');
        $rows = $this->model['sale_tax_invoice']->getRows($where, array('partner_name,document_identity'));
//        if($post['group_by']=='document') {
//            $this->pdfDocumentWise($rows);
//        }
        if($post['group_by']=='partner') {
            $this->pdfPartnerWise($rows);
        }
    }


    private function pdfPartnerWise($rows) {
        //d($rows, true);
        $invoices = array();
        foreach($rows as $row) {
            if(!isset($invoices[$row['partner_type'].'-'.$row['partner_name']])) {
                $invoices[$row['partner_type'].'-'.$row['partner_name']] = array(
                    'partner_type' => $row['partner_type'],
                    'partner_name' => $row['partner_name'],
                    'data' => array()
                );
            }
            $invoices[$row['partner_type'].'-'.$row['partner_name']]['data'][] = $row;
        }
//        d($invoices, true);
        $lang = $this->load->language($this->getAlias());
        $this->model['image'] = $this->load->model('tool/image');
        $this->model['setting'] = $this->load->model('common/setting');
        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'general',
            'field' => 'company_logo',
        ));

        $session = $this->session->data;
        $company_logo = $setting['value'];

        $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Sale Tax Report');
        $pdf->SetSubject('Sale Tax Report');

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => 'Sale Tax Report',
            'company_logo' => $company_logo
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10, 30, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // set font
        $pdf->SetFont('helvetica', '', 8);
        $grand_total = 0;
        foreach($invoices as $row) {
            $pdf->AddPage();
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0,9,'Customer : ' . html_entity_decode($row['partner_name']));

            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->ln(11);
            $pdf->Cell(10, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(30, 7, 'Doc. Date', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(30, 7, 'Doc. No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(30, 7, 'Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(30, 7, 'Tax Percent', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(30, 7, 'Tax Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(30, 7, 'Total Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');

            $sr =0;
            $amount = 0;
            $total_tax_amount = 0;
            $total_amount = 0;
            $pdf->SetFont('helvetica', '', 9);
            foreach($row['data'] as $detail) {

                $tax_percent = ($detail['item_tax'] * 100)/ $detail['item_amount'];

                $sr++;
                $pdf->Ln(7);
                $pdf->Cell(10, 7, $sr, 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(30, 7, stdDate($detail['document_date']), 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(30, 7, $detail['document_identity'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(30, 7, number_format($detail['item_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(30, 7, number_format($tax_percent,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(30, 7, number_format($detail['item_tax'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(30, 7, number_format($detail['net_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');

                $amount += $detail['item_amount'];
                $total_tax_amount += $detail['item_tax'];
                $total_amount += $detail['net_amount'];
            }
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Ln(7);
            $pdf->Cell(70, 7, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(30, 7, number_format($amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(30, 7, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(30, 7, number_format($total_tax_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(30, 7, number_format($total_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $grand_total += $total_amount;
        }

        $pdf->Ln(6);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(257, 6,'', 0, false, 'R', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(25, 6, number_format($grand_total,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');

        //Close and output PDF document
        $pdf->Output('Sale Tax Invoice Report:'.date('YmdHis').'.pdf', 'I');
    }

    private function pdfWarehouseWise($rows) {
        //d($rows, true);
        $invoices = array();
        foreach($rows as $row) {
            if(!isset($invoices[$row['warehouse']])) {
                $invoices[$row['warehouse']] = array(
                    'warehouse' => $row['warehouse'],
                    'data' => array()
                );
            }
            $invoices[$row['warehouse']]['data'][] = $row;
        }
        //d($invoices, true);
        $lang = $this->load->language($this->getAlias());
        $this->model['image'] = $this->load->model('tool/image');
        $this->model['setting'] = $this->load->model('common/setting');
        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'general',
            'field' => 'company_logo',
        ));

        $session = $this->session->data;
        $company_logo = $setting['value'];

        $pdf = new PDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Sale Tax Invoice Report');
        $pdf->SetSubject('Sale Tax Invoice Report');

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $company_logo
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10, 30, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // set font
        $pdf->SetFont('helvetica', '', 8);
        $grand_total = 0;
        foreach($invoices as $row) {
            $pdf->AddPage();
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0,9,'Warehouse : ' . $row['warehouse']);
            $pdf->SetFont('helvetica', '', 8);

            $pdf->ln(10);
            $pdf->Cell(7, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(15, 7, 'Doc. Date', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(25, 7, 'Doc. No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(85, 7, 'Product', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, 'Quantity', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(15, 7, 'Unit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, 'Rate', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(25, 7, 'Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, 'Dis Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(25, 7, 'Tax Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(25, 7, 'Total Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');

            $sr =0;
            $total_qty = 0;
            $amount = 0;
            $total_dis_amount = 0;
            $total_tax_amount = 0;
            $total_amount = 0;
            $pdf->Ln(1);
            foreach($row['data'] as $detail) {
                $sr++;
                $pdf->Ln(6);
                $pdf->Cell(7, 6, $sr, 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6, stdDate($detail['document_date']), 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, $detail['document_identity'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(85, 6, $detail['product_name'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, number_format($detail['qty'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6, $detail['unit'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, number_format($detail['rate'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, number_format($detail['amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, number_format($detail['discount_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, number_format($detail['tax_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, number_format($detail['total_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');

                $total_qty += $detail['qty'];
                $amount += $detail['amount'];
                $total_dis_amount += $detail['discount_amount'];
                $total_tax_amount += $detail['tax_amount'];
                $total_amount += $detail['total_amount'];

            }
            $pdf->Ln(6);
            $pdf->Cell(132, 6,'', 0, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 6, number_format($total_qty,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(35, 6, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 6, number_format($amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 6, number_format($total_dis_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 6, number_format($total_tax_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 6, number_format($total_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $grand_total += $total_amount;
        }

        $pdf->Ln(6);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(257, 6,'', 0, false, 'R', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(25, 6, number_format($grand_total,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');

        //Close and output PDF document
        $pdf->Output('Sale Tax Invoice Report:'.date('YmdHis').'.pdf', 'I');
    }

    private function pdfProductWise($rows) {
        //d($rows, true);
        $invoices = array();
        foreach($rows as $row) {
            if(!isset($invoices[$row['product_id']])) {
                $invoices[$row['product_id']] = array(
                    'product_code' => $row['product_code'],
                    'product_name' => $row['product_name'],
                    'cubic_meter' => $row['cubic_meter'],
                    'cubic_feet' => $row['cubic_feet'],
                    'data' => array()
                );
            }
            $invoices[$row['product_id']]['data'][] = $row;
        }

        $lang = $this->load->language($this->getAlias());
        $this->model['image'] = $this->load->model('tool/image');
        $this->model['setting'] = $this->load->model('common/setting');
        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'general',
            'field' => 'company_logo',
        ));

        $session = $this->session->data;
        $company_logo = $setting['value'];

        $pdf = new PDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Sale Tax Invoice Report');
        $pdf->SetSubject('Sale Tax Invoice Report');

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $company_logo
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10, 30, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // set font
        $pdf->SetFont('helvetica', '', 8);
        $grand_total = 0;
        foreach($invoices as $row) {
            $pdf->AddPage();
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0,9,'Product Name: ' . $row['product_name']);
            $pdf->ln(5);
            $pdf->Cell(50,9,'Product Code: ' . $row['product_code']);

            $pdf->SetFont('helvetica', '', 8);
            $pdf->ln(10);
            $pdf->Cell(7, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, 'Doc. Date', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(25, 7, 'Doc. No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(80, 7, 'Partner', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, 'Quantity', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(15, 7, 'Unit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, 'Rate', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(25, 7, 'Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, 'Dis Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(25, 7, 'Tax Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(25, 7, 'Total Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');

            $sr =0;
            $total_qty = 0;
            $amount = 0;
            $total_dis_amount = 0;
            $total_tax_amount = 0;
            $total_amount = 0;
            $pdf->Ln(1);
            foreach($row['data'] as $detail) {
                $sr++;
                $pdf->Ln(6);
                $pdf->Cell(7, 6, $sr, 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, stdDate($detail['document_date']), 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, $detail['document_identity'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(80, 6, html_entity_decode($detail['partner_name']), 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, number_format($detail['qty'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6, $detail['unit'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, number_format($detail['rate'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, number_format($detail['amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, number_format($detail['discount_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, number_format($detail['tax_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, number_format($detail['total_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');

                $total_qty += $detail['qty'];
                $amount += $detail['amount'];
                $total_dis_amount += $detail['discount_amount'];
                $total_tax_amount += $detail['tax_amount'];
                $total_amount += $detail['total_amount'];
            }
            $pdf->Ln(6);
            $pdf->Cell(132, 6,'', 0, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 6, number_format($total_qty,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(35, 6, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 6, number_format($amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 6, number_format($total_dis_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 6, number_format($total_tax_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 6, number_format($total_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $grand_total += $total_amount;
        }

        $pdf->Ln(6);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(257, 6,'', 0, false, 'R', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(25, 6, number_format($grand_total,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');

        //Close and output PDF document
        $pdf->Output('Sale Tax Invoice Report:'.date('YmdHis').'.pdf', 'I');
    }

    private function pdfContainerWise($rows) {
        //d($rows, true);
        $invoices = array();
        foreach($rows as $row) {
            if(!isset($invoices[$row['container_no']])) {
                $invoices[$row['container_no']] = array(
                    'container_no' => $row['container_no'],
                    'data' => array()
                );
            }
            $invoices[$row['container_no']]['data'][] = $row;
        }
        //d($invoices, true);
        $lang = $this->load->language($this->getAlias());
        $this->model['image'] = $this->load->model('tool/image');
        $this->model['setting'] = $this->load->model('common/setting');
        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'general',
            'field' => 'company_logo',
        ));

        $session = $this->session->data;
        $company_logo = $setting['value'];

        $pdf = new PDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Sale Tax Invoice Report');
        $pdf->SetSubject('Sale Tax Invoice Report');

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $company_logo
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(15, 35, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // set font
        $pdf->SetFont('helvetica', '', 8);
        foreach($invoices as $row) {
            $pdf->AddPage();
            $pdf->Cell(0,10,'Container No: ' . $row['container_no']);

            $pdf->ln(10);
            $pdf->Cell(7, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(15, 7, 'Doc. Date', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(25, 7, 'Doc. No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(15, 7, 'Warehouse', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(120, 7, 'Product', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(15, 7, 'Batch', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(15, 7, 'Quantity', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(14, 7, 'Meter', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(14, 7, 'Feet', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(10, 7, 'Cost', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, 'Total Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');

            $sr =0;
            $total_qty = 0;
            $total_cubic_meter = 0;
            $total_cubic_feet = 0;
            $total_amount = 0;
            $pdf->Ln(1);
            foreach($row['data'] as $detail) {
                $sr++;
                $pdf->Ln(6);
                $pdf->Cell(7, 6, $sr, 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6, $detail['document_date'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, $detail['document_identity'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6, $detail['warehouse'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(120, 6, $detail['product_name'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6, $detail['batch_no'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6, number_format($detail['qty'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(14, 6, number_format($detail['qty'] * $detail['cubic_meter'],4), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(14, 6, number_format($detail['qty'] * $detail['cubic_feet'],4), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(10, 6, number_format($detail['rate'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, number_format($detail['amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $total_qty += $detail['qty'];
                $total_cubic_meter += $detail['total_cubic_meter'];
                $total_cubic_feet += $detail['total_cubic_feet'];
                $total_amount += $detail['amount'];
            }
            $pdf->Ln(6);
            $pdf->Cell(197, 6,'', 0, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(15, 6, number_format($total_qty,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(14, 6, number_format($total_cubic_meter,4), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(14, 6, number_format($total_cubic_feet,4), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(10, 6, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 6, number_format($total_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
        }

        //Close and output PDF document
        $pdf->Output('Sale Tax Invoice Report:'.date('YmdHis').'.pdf', 'I');
    }

}

class PDF extends TCPDF {
    public $data = array();

    //Page header
    public function Header() {
        // Logo
        if($this->data['company_logo'] != '') {
            $image_file = DIR_IMAGE.$this->data['company_logo'];
            //$this->Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);
            $this->Image($image_file, 10, 10, 30, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        // Set font
        $this->SetFont('helvetica', 'B', 20);
        $this->Ln(2);
        // Title
        $this->Cell(0, 10, $this->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);
        $this->Cell(0, 10, $this->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}
?>