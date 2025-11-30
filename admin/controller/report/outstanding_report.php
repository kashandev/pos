<?php

class ControllerReportOutstandingReport extends HController {

    protected function getAlias() {
        return 'report/outstanding_report';
    }

    protected function init() {
        $this->model[$this->getAlias()] = $this->load->model('gl/ledger');
        $this->data['lang'] = $this->load->language($this->getAlias());
        $this->document->setTitle($this->data['lang']['heading_title']);
        $this->data['token'] = $this->session->data['token'];
    }

    protected function getList() {
        parent::getList();


        // $this->model['partner_category'] = $this->load->model('setup/partner_category');
        // $this->data['partner_categorys'] = $this->model['partner_category']->getRows();
       $this->data['partner_types'] = $this->session->data['partner_types'];
       // d($this->data['partner_types'],true);
        $this->model['customer_unit'] = $this->load->model('inventory/customer_unit');
        $this->data['customer_units'] = $this->model['customer_unit']->getRows(array('company_id' => $this->session->data['company_id']),array('customer_unit'));

        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
        $this->data['href_get_detail_report'] = $this->url->link($this->getAlias() .'/getDetailReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_get_summary_report'] = $this->url->link($this->getAlias() .'/getSummaryReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_print_detail_report'] = $this->url->link($this->getAlias() .'/printDetailReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_print_summary_report'] = $this->url->link($this->getAlias() .'/printSummaryReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_get_partner'] = $this->url->link($this->getAlias() .'/GetPartners', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['date_from'] = stdDate($this->session->data['fiscal_date_from']);
        $this->data['date_to'] = stdDate(($this->session->data['fiscal_date_to'] > date('Y-m-d') ? '' : $this->session->data['fiscal_date_to']));
        $this->data['href_print_excel'] = $this->url->link($this->getAlias() .'/printReportExcel', 'token=' . $this->session->data['token'], 'SSL');


        $this->data['strValidation'] = "{
            'rules': {
                'date_from': {'required': true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},
                'date_to': {'required': true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},
                'people_type_id' : {'required' : true}
            },
            ignore:[],
        }";

        $this->template = $this->getAlias() . '.tpl';
        $this->response->setOutput($this->render());
    }


    public function GetPartners()
    {

     $post = $this->request->post;

        $this->model['partner'] = $this->load->model('common/partner');
        $customers = $this->model['partner']->getRows(array('partner_type_id' => $post['partner_type_id']),array('name'));

        $html='';

        $html = '<option value="">&nbsp;</option>';
        foreach($customers as $customer)
        {
            $html .=  '<option value="'.$customer['partner_id'].'">'.$customer['name'].'</option>';
        }

        $json = array(
            'success' =>true,
            'html' => $html,

        );

        $this->response->setOutput(json_encode($json));

    }
    public function getDetailReport() {
        //$lang = $this->load->language('report/party_ledger');
        $post = $this->request->post;
        $session = $this->session->data;

        $this->model['party_ledger'] = $this->load->model('report/party_ledger');
        $where = "l.company_id = '".$this->session->data['company_id']."'";
        $where .= " AND l.company_branch_id = '".$this->session->data['company_branch_id']."'";
        $where .= " AND l.fiscal_year_id = '".$this->session->data['fiscal_year_id']."'";
        if($post['from_date'] != '') {
            $where .= " AND l.document_date >= '".$post['from_date']."'";
        }
        if($post['to_date'] != '') {
            $where .= " AND l.document_date <= '".$post['to_date']."'";
        }
        if($post['partner_type_id'] != '') {
            $where .= " AND l.partner_type_id = '".$post['partner_type_id']."'";
        }
        if($post['partner_id'] != '') {
            $where .= " AND l.partner_id = '".$post['partner_id']."'";
        }

        $rows = $this->model['party_ledger']->getPartyLedger($where);
// d($rows,true);
        $html = '';
        foreach($rows as $row) {
            $html .='<tr>';
            $html .='<td>'.$row['partner_type'].'</td>';
            $html .='<td>'.$row['partner_name'].'</td>';
            $html .='<td>'.$row['document_date'].'</td>';
            $html .='<td>'.$row['document_identity'].'</td>';
            $html .='<td>'.$row['ref_document_identity'].'</td>';
            $html .='<td>'.$row['remarks'].'</td>';
            $html .='<td>'.$row['account'].'</td>';
            $html .='<td>'.$row['debit'].'</td>';
            $html .='<td>'.$row['credit'].'</td>';
            $html .='</tr>';
        }

        $json = array(
            'post' => $post,
            'rows' => $rows,
            'html' => $html,
            'success' => true,
        );

        echo json_encode($json);
        exit;
    }

    public function printDetailReport() {
//
//        ini_set('max_execution_time',400);
//        ini_set('memory_limit','3072M');
//
//        $lang = $this->load->language($this->getAlias());
//        //d($lang,true);
//        $post = $this->request->post;
//        $session = $this->session->data;
//
//        if($post['date_from'] == "") {
//            $post['date_from'] = $session['date_from'];
//        } else {
//            $post['date_from'] = MySqlDate($post['date_from']);
//        }
//        if($post['date_to'] == "") {
//            $post['date_to'] = $session['date_to'];
//        } else {
//            $post['date_to'] = MySqlDate($post['date_to']);
//        }
//
//        if($post['partner_type_id'] != "") {
//            $this->model['partner_type'] = $this->load->model('common/partner_type');
//            $partner_type = $this->model['partner_type']->getRow(array('partner_type_id' => $post['partner_type_id']));
//            $post['partner_type'] = $partner_type['name'];
//        } else {
//            $post['partner_type'] = '';
//        }
//
//        if($post['partner_id'] != "") {
//            $this->model['partner'] = $this->load->model('common/partner');
//            $partner = $this->model['partner']->getRow(array('partner_id' => $post['partner_id']));
//            $post['partner_name'] = $partner['name'];
//        } else {
//            $post['partner_name'] = '';
//        }
//
//        $arrLedger = array();
//        $this->model['party_ledger'] = $this->load->model('report/party_ledger');
//
//        $where = "l.company_id = '".$this->session->data['company_id']."'";
//        $where .= " AND l.company_branch_id = '".$this->session->data['company_branch_id']."'";
//        $where .= " AND l.fiscal_year_id = '".$this->session->data['fiscal_year_id']."'";
//        if($post['date_from'] != '') {
//            $where .= " AND l.document_date < '".$post['date_from']."'";
//        }
//        if($post['partner_type_id'] != '') {
//            $where .= " AND l.partner_type_id = '".$post['partner_type_id']."'";
//        }
//        if($post['partner_id'] != '') {
//            $where .= " AND l.partner_id = '".$post['partner_id']."'";
//        }
//        $rows = $this->model['party_ledger']->getPartyOpening($where, array('partner_type', 'partner_name'));
//        foreach($rows as $row) {
//            $arrLedger[$row['partner_type'] . ': ' . $row['partner_name']][] = $row;
//        }
//
//        $where = "l.company_id = '".$this->session->data['company_id']."'";
//        $where .= " AND l.company_branch_id = '".$this->session->data['company_branch_id']."'";
//        $where .= " AND l.fiscal_year_id = '".$this->session->data['fiscal_year_id']."'";
//        if($post['date_from'] != '') {
//            $where .= " AND l.document_date >= '".$post['date_from']."'";
//        }
//        if($post['date_to'] != '') {
//            $where .= " AND l.document_date <= '".$post['date_to']."'";
//        }
//        if($post['partner_type_id'] != '') {
//            $where .= " AND l.partner_type_id = '".$post['partner_type_id']."'";
//        }
//        if($post['partner_id'] != '') {
//            $where .= " AND l.partner_id = '".$post['partner_id']."'";
//        }
//        $rows = $this->model['party_ledger']->getPartyLedger($where, array('partner_type', 'partner_name','document_date', 'document_identity', 'ref_document_identity'));
//        foreach($rows as $row) {
//            $arrLedger[$row['partner_type'] . ': ' . $row['partner_name']][] = $row;
//        }
//
//        $this->model['company'] = $this->load->model('setup/company');
//        $company = $this->model['company']->getRows(array('company_id' => $this->session->data['company_id']));
//
//        $data = array(
//            'lang' => $lang,
//            'company' => $company,
//            'filter' => $post,
//            'rows' => $arrLedger
//        );
//        //d($data, true);
//        try
//        {
//            $pdf=new mPDF();
//
//            $pdf->SetDisplayMode('fullpage');
//            //d($data,true);
//            $pdf->mPDF('utf-8','A4','','','15','15','35','18');
//            $pdf->setHTMLHeader($this->getPDFHeader($data));
//            $pdf->setHTMLFooter($this->getPDFFooter($data));
//            $pdf->WriteHTML($this->getPDFStyle($data));
//            $pdf->WriteHTML($this->getPDFBodyDetail($data));
//
//            $pdf->Output();
//        }
//        catch(Exception $e) {
//            echo $e;
//            exit;
//        }
//        exit;
    }

    public function printSummaryReport() {

        ini_set('max_execution_time',400);
        ini_set('memory_limit','3072M');

        $lang = $this->load->language($this->getAlias());
        //d($lang,true);
        $post = $this->request->post;
        // d($post,true);
        $session = $this->session->data;

        if($post['date_from'] == "") {
            $post['date_from'] = $session['date_from'];
        } else {
            $post['date_from'] = MySqlDate($post['date_from']);
        }
        if($post['date_to'] == "") {
            $post['date_to'] = $session['date_to'];
        } else {
            $post['date_to'] = MySqlDate($post['date_to']);
        }

        if($post['partner_id'] != "") {
            $this->model['partner'] = $this->load->model('common/partner');
            $partner = $this->model['partner']->getRow(array('partner_id' => $post['partner_id']));
            $post['partner_name'] = $partner['name'];
        } else {
            $post['partner_name'] = '';
        }

        $arrLedger = array();
//        $this->model['document'] = $this->load->model('report/document');
        $this->model['document'] = $this->load->model('common/document');

        $where = "company_id = '".$this->session->data['company_id']."'";
        $where .= " AND company_branch_id = '".$this->session->data['company_branch_id']."'";
        $where .= " AND fiscal_year_id = '".$this->session->data['fiscal_year_id']."'";
        if($post['date_to'] != '') {
            $where .= " AND document_date <= '".$post['date_to']."'";
        }
        if($post['date_from'] != '') {
            $where .= " AND document_date >= '".$post['date_from']."'";
        }
        if($post['partner_category_id'] != '') {
            $where .= " AND partner_category_id = '".$post['partner_category_id']."'";
        }
        if($post['customer_unit_id'] != '') {
            $where .= " AND customer_unit_id = '".$post['customer_unit_id']."'";
        }

        if($post['partner_id'] != '') {
            $where .= " AND partner_type_id = '".$post['partner_type_id']."'";
            $where .= " AND partner_id = '".$post['partner_id']."'";
         //   $where .= " AND `outstanding_amount` != 0";
        }

        $where .= " AND `outstanding_amount` != 0";

        $rows = $this->model['document']->getOutstandingDocuments($where, array('name','document_date','ref_document_identity'));

//        d($rows,true);


        $this->model['customer_unit'] = $this->load->model('inventory/customer_unit');

        foreach($rows as $row) {

            $customer_units = $this->model['customer_unit']->getRow(array('company_id' => $this->session->data['company_id'],'customer_unit_id' => $row['customer_unit_id']));
            $row['customer_unit'] = $customer_units['customer_unit'];

            $arrLedger[$row['name']][] = $row;
        }
//d(array($rows,$arrLedger),true);
        $this->model['company'] = $this->load->model('setup/company');
        $company = $this->model['company']->getRows(array('company_id' => $this->session->data['company_id']));

        $data = array(
            'lang' => $lang,
            'company' => $company,
            'filter' => $post,
            'rows' => $arrLedger
        );

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
        $pdf->SetTitle('Outstanding Report');
        $pdf->SetSubject('Outstanding Report');

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $company_logo,
            'to_date' => $post['date_to'],
            'from_date' => $post['date_from'],
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(8, 55, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // set font
            $pdf->AddPage();

            $pdf->SetFont('helvetica', 'B', 10);

            $sr =0;
            //            $pdf->Ln(1);
            foreach($arrLedger as $partner_name => $details) {

                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->Cell( 0, 7, html_entity_decode($partner_name), 0, false, 'L', 0, '', 0, false, 'M', 'M');
                $pdf->Ln(2);

                $total_balance = 0;
                foreach($details as $detail )
                {
                    $total_balance  += $detail['outstanding_amount'];

                    $sr++;
                    $pdf->Ln(7);
                    $pdf->SetFont('helvetica', '', 7);
                    $pdf->Cell(7, 7, $sr, 1, false, 'R', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(15, 7, stdDate($detail['document_date']), 1, false, 'L', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(23, 7, html_entity_decode($detail['ref_document_identity']), 1, false, 'L', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(75, 7, $detail['dc_no'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(30, 7, $detail['po_no'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(25, 7, $detail['customer_unit'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(25, 7, number_format($detail['outstanding_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');

                }
                $pdf->Ln(7);
                $pdf->Cell(175, 7,'', 0, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 7, number_format($total_balance,4), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Ln(3);

                }

        //Close and output PDF document
        $pdf->Output('Outstanding Report:'.date('YmdHis').'.pdf', 'I');

    }

    public function printReportExcel()
    {
        ini_set('max_execution_time',400);
        ini_set('memory_limit','3072M');

        $lang = $this->load->language($this->getAlias());
        //d($lang,true);
        $post = $this->request->post;
        $session = $this->session->data;

        if($post['date_from'] == "") {
            $post['date_from'] = $session['date_from'];
        } else {
            $post['date_from'] = MySqlDate($post['date_from']);
        }
        if($post['date_to'] == "") {
            $post['date_to'] = $session['date_to'];
        } else {
            $post['date_to'] = MySqlDate($post['date_to']);
        }

        if($post['partner_id'] != "") {
            $this->model['partner'] = $this->load->model('common/partner');
            $partner = $this->model['partner']->getRow(array('partner_id' => $post['partner_id']));
            $post['partner_name'] = $partner['name'];
        } else {
            $post['partner_name'] = '';
        }

        $arrLedger = array();
//        $this->model['document'] = $this->load->model('report/document');
        $this->model['document'] = $this->load->model('common/document');

        $where = "company_id = '".$this->session->data['company_id']."'";
        $where .= " AND company_branch_id = '".$this->session->data['company_branch_id']."'";
        $where .= " AND fiscal_year_id = '".$this->session->data['fiscal_year_id']."'";
        if($post['date_to'] != '') {
            $where .= " AND document_date <= '".$post['date_to']."'";
        }
        if($post['partner_category_id'] != '') {
            $where .= " AND partner_category_id = '".$post['partner_category_id']."'";
        }
        if($post['customer_unit_id'] != '') {
            $where .= " AND customer_unit_id = '".$post['customer_unit_id']."'";
        }

        if($post['partner_id'] != '') {
            $where .= " AND partner_type_id = '".$post['partner_type_id']."'";
            $where .= " AND partner_id = '".$post['partner_id']."'";
            //   $where .= " AND `outstanding_amount` != 0";
        }

        $where .= " AND `outstanding_amount` != 0";

        $rows = $this->model['document']->getOutstandingDocuments($where, array('name','document_date','ref_document_identity'));

        $this->model['customer_unit'] = $this->load->model('inventory/customer_unit');

        foreach($rows as $row) {

            $customer_units = $this->model['customer_unit']->getRow(array('company_id' => $this->session->data['company_id'],'customer_unit_id' => $row['customer_unit_id']));
            $row['customer_unit'] = $customer_units['customer_unit'];

            $arrLedger[$row['name']][] = $row;
        }

        $this->model['company'] = $this->load->model('setup/company');
        $company = $this->model['company']->getRows(array('company_id' => $this->session->data['company_id']));

        $data = array(
            'lang' => $lang,
            'company' => $company,
            'filter' => $post,
            'rows' => $arrLedger
        );

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

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);

        $objPHPExcel->getProperties()
            ->setCreator("Farrukh Afaq")
            ->setLastModifiedBy("Hira")
            ->setTitle("Outstanding Report");

        $objPHPExcel->data = array(
            'company_name' => $session['company_name'],
            'report_name' => 'Outstanding Report',
            'date_to' => $post['date_to']
        );

        $rowCount = 1;

        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":G".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $session['company_name']);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'size' => 25
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'ebebeb')
                )
            )
        );
        $rowCount ++;

        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":G".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Outstanding Report');
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'size' => 20
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'ebebeb')
                )
            )
        );

        $rowCount ++;

        foreach($arrLedger as $key => $value) {
            $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":G".($rowCount));
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $key);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->getFill('ebebeb');
            $rowCount ++;

            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(7);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);

            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'S.no');
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'Date');
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Invoice No.');
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'Challan No');
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'Order No.');
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'Customer Unit');
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, 'Outstanding Amount');
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':G'.$rowCount)->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    )
                )
            );
            $rowCount++;
            $total_amount = 0;
            $s_no = 1;
            foreach ($value as $key_1 => $value_1)
            {
                $total_amount += $value_1['outstanding_amount'];
                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount,$s_no);
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, stdDate($value_1['document_date']));
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value_1['ref_document_identity']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value_1['dc_no']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value_1['po_no']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $value_1['customer_unit']);
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, number_format($value_1['outstanding_amount'],2));
                $s_no++;
                $rowCount++;
            }
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, number_format($total_amount,2))->getStyle('H'.$rowCount)->getFont()->setBold( true );
            $rowCount+=3;

        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="outstanding_report.xlsx"');
        header('Cache-Control: max-age=0');
        //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        //$objWriter->save('some_excel_file.xlsx');
        $objWriter->save('php://output');
        exit;
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
        $this->SetFont('times', 'B', 20);
        $this->Ln(2);
        // Title
        $this->Cell(0, 10, $this->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);
        $this->Cell(0, 10, $this->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(1);
        $this->SetFont('times', 'B', 8);
        $this->Cell(0, 10, ' To Date : ' .stdDate($this->data['to_date']), 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $this->Ln(6);
        $this->Cell(0, 10, ' From Date : ' .stdDate($this->data['from_date']), 0, false, 'R', 0, '', 0, false, 'M', 'M');

        $this->Ln(20);
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(  7, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell( 15, 7, 'Date', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell( 23, 7, 'Invoice No', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell( 75, 7, 'Challan No', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell( 30, 7, 'Order No', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell( 25, 7, 'Customer Unit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell( 25, 7, 'Outstanding', 1, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('times', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}
?>