<?php

class ControllerReportBankReconciliationReport extends HController {

    protected function getAlias() {
        return 'report/bank_reconciliation_report';
    }
    
    protected function getList() {
        parent::getList();

        $this->model['coa_level2'] = $this->load->model('gl/coa_level2');
        $this->data['coa_levels2'] = $this->model['coa_level2']->getRows(array('company_id' => $this->session->data['company_id']), array('name'));

        $this->model['coa_level3'] = $this->load->model('gl/coa_level3');
        $this->data['coas'] = $this->model['coa_level3']->getRows(array('company_id' => $this->session->data['company_id']), array('name'));

        $this->data['action_validate_date'] = $this->url->link('common/home/validateDate', 'token=' . $this->session->data['token']);
        $this->data['action_print'] = $this->url->link($this->getAlias() .'/printReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['date_from'] = stdDate($this->session->data['fiscal_date_from']);
        $this->data['date_to'] = stdDate(($this->session->data['fiscal_date_to'] > date('Y-m-d') ? '' : $this->session->data['fiscal_date_to']));
        $this->data['href_get_coa_level2'] = $this->url->link($this->getAlias() .'/getCOALevel2', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_get_coa_level3'] = $this->url->link($this->getAlias() .'/getCOALevel3', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_get_report'] = $this->url->link($this->getAlias() .'/getReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_print_report'] = $this->url->link($this->getAlias() .'/printReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_print_excel'] = $this->url->link($this->getAlias() .'/printReportExcel', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['strValidation'] = "{
            'rules': {
                'date_from': {'required': true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},
                'date_to': {'required': true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},
            },
        }";

        $this->template = $this->getAlias() . '.tpl';
        $this->response->setOutput($this->render());
    }

    public function getCOALevel2() {
        $coa_level1_id = $this->request->post['coa_level1_id'];
        $this->model['coa_level2'] = $this->load->model('gl/coa_level2');
        $rows = $this->model['coa_level2']->getRows(array('company_id' => $this->session->data['company_id'], 'coa_level1_id' => $coa_level1_id), array('name'));

        $html = "";
        $html .= '<option value="">&nbsp;</option>';
        foreach($rows as $row) {
            $html .= '<option value="'.$row['coa_level2_id'].'">'.$row['name'].'</option>';
        }

        $json = array('success' => true, 'html' => $html);
        echo json_encode($json);
    }

    public function getCOALevel3() {
        //$coa_level1_id = $this->request->post['coa_level1_id'];
        $coa_level2_id = $this->request->post['coa_level2_id'];
        $this->model['coa_level3'] = $this->load->model('gl/coa_level3');

        if($coa_level2_id != '')
        {
            $rows = $this->model['coa_level3']->getRows(array('company_id' => $this->session->data['company_id'],  'coa_level2_id' => $coa_level2_id), array('name'));

        }else {

            $rows = $this->model['coa_level3']->getRows(array('company_id' => $this->session->data['company_id']), array('name'));
        }


        $html = "";
        $html .= '<option value="">&nbsp;</option>';
        foreach($rows as $row) {
            $html .= '<option value="'.$row['coa_level3_id'].'">'.$row['name'].'</option>';
        }

        //d($html,true);
        $json = array('success' => true, 'html' => $html);
        echo json_encode($json);
    }


    public function getReport() {
        $post = $this->request->post;
        $filter = array();
        $filter[] = "`fiscal_year_id` = '".$this->session->data['fiscal_year_id']."'";
        if($post['date_from'] != '') {
            $filter[] = "`document_date` >= '".MySqlDate($post['date_from'])."'";
        }
        if($post['date_to'] != '') {
            $filter[] = "`document_date` <= '".MySqlDate($post['date_to'])."'";
        }
//        if($post['coa_level1_id'] != '') {
//            $filter[] = "`coa_level1_id` = '".$post['coa_level1_id']."'";
//        }
//        if($post['coa_level2_id'] != '') {
//            $filter[] = "`coa_level2_id` = '".$post['coa_level2_id']."'";
//        }
        if($post['coa_level3_id'] != '') {
            $filter[] = "`coa_id` = '".$post['coa_level3_id']."'";
        }
        $where = implode(' AND ', $filter);

        $this->model['ledger'] = $this->load->model('gl/ledger');
        $rows = $this->model['ledger']->getRows($where);
        $html = '';
        foreach($rows as $row) {
            $html .= '<tr>';
            $html .= '  <td>'.stdDate($row['document_date']).'</td>';
            $html .= '  <td>'.$row['document_identity'].'</td>';
            $html .= '  <td>'.$row['partner_type'].'</td>';
            $html .= '  <td>'.$row['partner_name'].'</td>';
            $html .= '  <td>'.$row['account'].'</td>';
            $html .= '  <td>'.$row['ref_document_identity'].'</td>';
            $html .= '  <td>'.$row['remarks'].'</td>';
            $html .= '  <td>'.$row['debit'].'</td>';
            $html .= '  <td>'.$row['credit'].'</td>';
            $html .= '  <td>'.stdDateTime($row['created_at']).'</td>';
            $html .= '</tr>';
        }

        $json = array(
            'success' => true,
            'post' => $post,
            'html' => $html,
        );
        echo json_encode($json);
    }

    public function printReportExcel()
    {
        ini_set('max_execution_time',400);
        ini_set('memory_limit','3072M');

        $lang=$this->load->language($this->getAlias());
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

        $arrLedger = array();

        $this->model['bank_reconciliation'] = $this->load->model('report/bank_reconciliation_report');

        $filter = array(
            'company_id' => $session['company_id'],
            'company_branch_id' => $session['company_branch_id'],
            'fiscal_year_id' => $session['fiscal_year_id'],
            'session_from' => $session['fiscal_date_from'],
            'date_from' => $post['date_from'],
            'date_to' => $post['date_to'],
            'coa_level1_id' => $post['coa_level1_id'],
            'coa_level2_id' => $post['coa_level2_id'],
            'coa_level3_id' => $post['coa_level3_id'],
        );

        $rows = $this->model['bank_reconciliation']->getUnclearedLedgerReport($filter);
        $rows_cleared = $this->model['bank_reconciliation']->getClearedLedgerReport($filter);
//        echo '<pre>';
//        print_r($rows);
//        print_r($rows_cleared);
//        exit;
//d($rows_cleared,true);
        foreach($rows as $row) {
            $records[$row['display_name']][] = array(
                'document_date' => stdDate($row['document_date']),
                'document_identity' => $row['document_identity'],
                'remarks' => $row['remarks'],
                'debit' => $row['debit'],
                'credit' => $row['credit'],
                //'isPost' => (empty($arrDocuments[$row['document_identity']])?'':($arrDocuments[$row['document_identity']]?'Post':'Unpost'))
            );
        }


        $this->model['company'] = $this->load->model('setup/company');
        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));

        $this->model['coa'] = $this->load->model('gl/coa');
        $coa = $this->model['coa']->getRow(array('company_id' => $this->session->data['company_id'], 'coa_level3_id' => $post['coa_id']));

        $display_filter = array(
            'date_from' => stdDate($post['date_from']),
            'date_to' => stdDate($post['date_to']),
            'coa' => $coa['display_name']
        );

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


        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);

        $objPHPExcel->getProperties()
            ->setCreator("Hira Anwer")
            ->setLastModifiedBy("Hira Anwer")
            ->setTitle("Bank Reconciliation Report");

        $objPHPExcel->data = array(
            'company_name' => $session['company_name'],
            'report_name' => 'Bank Reconciliation Report'
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
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Bank Reconciliation Report');
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

        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":G".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Cleared Entities');
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

        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Sr.');
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'Bank.');
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Doc. Date');
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'Doc. No.');
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'Debit');
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'Credit');
        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, 'Balance');

        $rowCount++;

        $s_no = 1;
        foreach ($rows_cleared as $key => $value)
        {
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $s_no);
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $value['display_name']);
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value['document_date']);
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value['document_identity']);
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value['debit']);
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $value['credit']);
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, ($value['debit'] - $value['credit']));
            $rowCount ++;
            $s_no++;
        }

        $rowCount++;

        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":G".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Un-Cleared Entities');
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

        $rowCount++;

        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Sr.');
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'Bank.');
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Doc. Date');
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'Doc. No.');
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'Debit');
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'Credit');
        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, 'Balance');

        $rowCount++;

        $s_no = 1;
        foreach ($rows as $key => $value)
        {
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $s_no);
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $value['display_name']);
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value['document_date']);
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value['document_identity']);
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value['debit']);
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $value['credit']);
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, ($value['debit'] - $value['credit']));
            $rowCount ++;
            $s_no++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="bank_reconciliation_report.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save('php://output');
        exit;

    }

    public function printReport() {
        ini_set('max_execution_time',400);
        ini_set('memory_limit','3072M');

        $lang=$this->load->language($this->getAlias());
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

        $arrLedger = array();

        $this->model['bank_reconciliation'] = $this->load->model('report/bank_reconciliation_report');

        $filter = array(
            'company_id' => $session['company_id'],
            'company_branch_id' => $session['company_branch_id'],
            'fiscal_year_id' => $session['fiscal_year_id'],
            'session_from' => $session['fiscal_date_from'],
            'date_from' => $post['date_from'],
            'date_to' => $post['date_to'],
            'coa_level1_id' => $post['coa_level1_id'],
            'coa_level2_id' => $post['coa_level2_id'],
            'coa_level3_id' => $post['coa_level3_id'],
        );

        $rows = $this->model['bank_reconciliation']->getUnclearedLedgerReport($filter);
        $rows_cleared = $this->model['bank_reconciliation']->getClearedLedgerReport($filter);
//d($rows_cleared,true);
        foreach($rows as $row) {
            $records[$row['display_name']][] = array(
                'document_date' => stdDate($row['document_date']),
                'document_identity' => $row['document_identity'],
                'remarks' => $row['remarks'],
                'debit' => $row['debit'],
                'credit' => $row['credit'],
                //'isPost' => (empty($arrDocuments[$row['document_identity']])?'':($arrDocuments[$row['document_identity']]?'Post':'Unpost'))
            );
        }

//        foreach($rows_cleared as $row) {
//            $records_cleared[$row['display_name']][] = array(
//                'document_date' => stdDate($row['document_date']),
//                'document_identity' => $row['document_identity'],
//                'remarks' => $row['remarks'],
//                'debit' => $row['debit'],
//                'credit' => $row['credit'],
//                //'isPost' => (empty($arrDocuments[$row['document_identity']])?'':($arrDocuments[$row['document_identity']]?'Post':'Unpost'))
//            );
//        }

        $this->model['company'] = $this->load->model('setup/company');
        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));

        $this->model['coa'] = $this->load->model('gl/coa');
        $coa = $this->model['coa']->getRow(array('company_id' => $this->session->data['company_id'], 'coa_level3_id' => $post['coa_id']));

        $display_filter = array(
            'date_from' => stdDate($post['date_from']),
            'date_to' => stdDate($post['date_to']),
            'coa' => $coa['display_name']
        );

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
        $pdf->SetAuthor('Farrukh Afaq');
        $pdf->SetTitle('Bank Reconciliation Report');
        $pdf->SetSubject('Bank Reconciliation Report');

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $company_logo
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10, 35, 10);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Cell(0, 10, 'Cleared Entries', 'B', false, 'C', 0, '', 0, false, 'M', 'M');

        $pdf->ln(20);
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('freesans', 'B', 8);
        $pdf->Cell(  7, 7, 'Sr.', 1, false, 'C', 1, '', 0, false, 'M', 'M');
        $pdf->Cell( 45, 7, 'Bank', 1, false, 'C', 1, '', 0, false, 'M', 'M');
        $pdf->Cell( 20, 7, 'Clearing Date', 1, false, 'C', 1, '', 0, false, 'M', 'M');
        $pdf->Cell( 20, 7, 'Doc. Date', 1, false, 'C', 1, '', 0, false, 'M', 'M');
        $pdf->Cell( 30, 7, 'Doc. No.', 1, false, 'C', 1, '', 0, false, 'M', 'M');
        //$pdf->Cell( 60, 7, 'Remarks', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell( 22, 7, 'Debit', 1, false, 'C', 1, '', 0, false, 'M', 'M');
        $pdf->Cell( 22, 7, 'Credit', 1, false, 'C', 1, '', 0, false, 'M', 'M');
        $pdf->Cell( 25, 7, 'Balance', 1, false, 'C', 1, '', 0, false, 'M', 'M');

        $sr =0;
        $total_debit = 0;
        $total_credit = 0;
//            $pdf->Ln(1);
        foreach($rows_cleared as $detail) {
            $total_debit += $detail['debit'];
            $total_credit += $detail['credit'];
            $balance = $total_debit - $total_credit;
            $sr++;
            $pdf->Ln(7);
            $pdf->SetFont('freesans', '', 8);
            $pdf->Cell(7, 7, $sr, 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(45, 7, $detail['display_name'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 7, stdDate($detail['clearing_date']), 1, false, 'L', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 7, stdDate($detail['document_date']), 1, false, 'L', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(30, 7, $detail['document_identity'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
            //$pdf->Cell(60, 7, $detail['remarks'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(22, 7, number_format($detail['debit'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(22, 7, number_format($detail['credit'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 7, number_format($balance,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
        }
        $pdf->Ln(7);
        $pdf->Cell(122, 7,'', 0, false, 'R', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(22, 7, number_format($total_debit,2), 1, false, 'R', 1, '', 1, false, 'M', 'M');
        $pdf->Cell(22, 7, number_format($total_credit,4), 1, false, 'R', 1, '', 1, false, 'M', 'M');

        $pdf->AddPage();
        // set font
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Cell(0, 10, 'Uncleared Entries', 'B', false, 'C', 0, '', 0, false, 'M', 'M');
        foreach($records as $coa => $row) {
            $pdf->ln(20);
            $pdf->SetFont('freesans', 'B', 10);
            $pdf->Cell(0,10,'Account: ' . $coa);

            $pdf->ln(12);
            $pdf->SetFont('freesans', 'B', 8);
            $pdf->Cell(  7, 7, 'Sr.', 1, false, 'C', 1, '', 0, false, 'M', 'M');
            $pdf->Cell( 20, 7, 'Doc. Date', 1, false, 'C', 1, '', 0, false, 'M', 'M');
            $pdf->Cell( 30, 7, 'Doc. No.', 1, false, 'C', 1, '', 0, false, 'M', 'M');
            $pdf->Cell( 60, 7, 'Remarks', 1, false, 'C', 1, '', 0, false, 'M', 'M');
            $pdf->Cell( 22, 7, 'Debit', 1, false, 'C', 1, '', 0, false, 'M', 'M');
            $pdf->Cell( 22, 7, 'Credit', 1, false, 'C', 1, '', 0, false, 'M', 'M');
            $pdf->Cell( 25, 7, 'Balance', 1, false, 'C', 1, '', 0, false, 'M', 'M');

            $sr =0;
            $total_debit = 0;
            $total_credit = 0;
//            $pdf->Ln(1);
            foreach($row as $detail) {
                $total_debit += $detail['debit'];
                $total_credit += $detail['credit'];
                $balance = $total_debit - $total_credit;
                $sr++;
                $pdf->Ln(7);
                $pdf->SetFont('freesans', '', 8);
                $pdf->Cell(7, 7, $sr, 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 7, $detail['document_date'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(30, 7, $detail['document_identity'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(60, 7, $detail['remarks'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(22, 7, number_format($detail['debit'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(22, 7, number_format($detail['credit'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 7, number_format($balance,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            }
            $pdf->Ln(7);
            $pdf->Cell(117, 7,'', 0, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(22, 7, number_format($total_debit,2), 1, false, 'R', 1, '', 1, false, 'M', 'M');
            $pdf->Cell(22, 7, number_format($total_credit,4), 1, false, 'R', 1, '', 1, false, 'M', 'M');

        }

        //}

        //Close and output PDF document
        $pdf->Output('Bank Reconciliation Report:'.date('YmdHis').'.pdf', 'I');
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