<?php

class ControllerReportLedgerReport extends HController {

    protected function getAlias() {
        return 'report/ledger_report';
    }
    
    protected function getList() {
        parent::getList();

        $this->model['coa_level2'] = $this->load->model('gl/coa_level2');
        $this->data['coa_levels2'] = $this->model['coa_level2']->getRows(array('company_id' => $this->session->data['company_id']), array('name'));

        $this->model['coa_level3'] = $this->load->model('gl/coa_level3');
        $this->data['coas'] = $this->model['coa_level3']->getRows(array('company_id' => $this->session->data['company_id']), array('name'));

        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
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
       if($post['coa_level2_id'] != '') {
           $filter[] = "`coa_level2_id` = '".$post['coa_level2_id']."'";
       }
        if($post['coa_level3_id'] != '') {
            $filter[] = "`coa_id` = '".$post['coa_level3_id']."'";
        }
        $where = implode(' AND ', $filter);

        $this->model['ledger'] = $this->load->model('gl/ledger');
        $rows = $this->model['ledger']->getRows($where);
        $html = '';
        foreach($rows as $row) {
            if($row['debit'] == 0 && $row['credit'] == 0)
            {

            }
            else
            {
                $html .= '<tr>';
                $html .= '  <td data-sort="'.$row['document_date'].'">'.stdDate($row['document_date']).'</td>';
                $html .= '  <td>'.$row['document_identity'].'</td>';
                $html .= '  <td>'.$row['partner_type'].'</td>';
                $html .= '  <td>'.$row['partner_name'].'</td>';
                $html .= '  <td>'.$row['account'].'</td>';
                $html .= '  <td>'.$row['ref_document_identity'].'</td>';
                $html .= '  <td>'.$row['remarks'].'</td>';
                $html .= '  <td>'.$row['debit'].'</td>';
                $html .= '  <td>'.$row['credit'].'</td>';
                $html .= '  <td data-sort="'.$row['created_at'].'" >'.stdDateTime($row['created_at']).'</td>';
                $html .= '</tr>';    
            }
            
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

        $this->model['ledger'] = $this->load->model('gl/ledger');

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

        $rows = $this->model['ledger']->getLedgerReport($filter);

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
            ->setCreator("Farrukh Afaq")
            ->setLastModifiedBy("Hira")
            ->setTitle("Ledger Report");

        $objPHPExcel->data = array(
            'company_name' => $session['company_name'],
            'report_name' => 'Ledger Report'
        );

        $rowCount = 1;

        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":I".($rowCount));
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

        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":I".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Ledger Report');
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

        foreach ($records as $key => $value)
        {
            $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":I".($rowCount));
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $key);
            $rowCount ++;
            $balance = 0;
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(17);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(17);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(17);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(17);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(17);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(17);
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'Document Date')->getStyle('B'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Document No.')->getStyle('C'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'Remarks')->getStyle('D'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'Debit')->getStyle('E'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'Credit')->getStyle('F'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, 'Balance')->getStyle('G'.$rowCount)->getFont()->setBold( true );
            $rowCount ++;
            foreach ($value as $key_1 => $val_1)
            {
                $balance = $balance + ($val_1['debit'] - $val_1['credit']);
                //$objPHPExcel->getActiveSheet()->mergeCells(''.($rowCount).":G".($rowCount));
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $val_1['document_date']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $val_1['document_identity']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $val_1['remarks']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $val_1['debit']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $val_1['credit']);
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $balance);
                $rowCount ++;
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="ledger_report.xlsx"');
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

        $this->model['ledger'] = $this->load->model('gl/ledger');

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

        $rows = $this->model['ledger']->getLedgerReport($filter);
        // d($rows,true);
        foreach($rows as $row) {
            if($row['debit'] == 0 && $row['credit'] == 0)
            {

            }
            else
            {
                $records[$row['display_name']][] = array(
                    'document_date' => stdDate($row['document_date']),
                    'document_identity' => $row['document_identity'],
                    'remarks' => $row['remarks'],
                    'debit' => $row['debit'],
                    'credit' => $row['credit'],
                    'cheque_no' => $row['cheque_no'],
                    'cheque_date' => $row['cheque_date'],
                    //'isPost' => (empty($arrDocuments[$row['document_identity']])?'':($arrDocuments[$row['document_identity']]?'Post':'Unpost'))
                );    
            }
            
        }
        // d($records,true);
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
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Ledger Report');
        $pdf->SetSubject('Ledger Report');

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $company_logo,
            'from_date' => stdDate($post['date_from']),
            'to_date' => stdDate($post['date_to']),
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(3, 50, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // set font
        $pdf->AddPage();
        foreach($records as $coa => $row) {
            $pdf->ln(5);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0,10,'Account: ' . $coa);
            $sr =0;
            $total_debit = 0;
            $total_credit = 0;
//            $pdf->Ln(1);
            foreach($row as $detail) {
                $total_debit += $detail['debit'];
                $total_credit += $detail['credit'];
                $balance = $total_debit - $total_credit;
                $sr++;
                if(strlen($detail['remarks'])<=30) {
                $pdf->Ln(7);
                $pdf->SetFont('helvetica', '', 7);
                $pdf->Cell(7, 7, $sr, 1, false, 'R');
                $pdf->Cell(20, 7, $detail['document_date'], 1, false, 'L');
                $pdf->Cell(30, 7, $detail['document_identity'], 1, false, 'L');
                $pdf->Cell(40, 7, $detail['remarks'], 1, false, 'L');
                $pdf->Cell(22, 7, $detail['cheque_no'], 1, false, 'L');
                $pdf->Cell(20, 7, $detail['cheque_date'], 1, false, 'L');
                $pdf->Cell(20, 7, number_format($detail['debit'],2), 1, false, 'R');
                $pdf->Cell(20, 7, number_format($detail['credit'],2), 1, false, 'R');
                if($balance < 0)
                {
                    $balance = abs($balance);
                    $pdf->Cell(25, 7, 'Cr '.number_format($balance,2), 1, false, 'R');    
                }
                else
                {
                    $pdf->Cell(25, 7, 'Dr '.number_format($balance,2), 1, false, 'R');   
                }
            }
            else
            {
                $arrRemarks = str_split($detail['remarks'], 30);
                foreach($arrRemarks as $index => $remarks) {
                     if($index==0) {
                        $pdf->Ln(7);
                        $pdf->SetFont('helvetica', '', 7);
                        $pdf->Cell(7, 7, $sr, 'TLR', false, 'R');
                        $pdf->Cell(20, 7, $detail['document_date'], 'TLR', false, 'L');
                        $pdf->Cell(30, 7, $detail['document_identity'], 'TLR', false, 'L');
                        $pdf->Cell(40, 7, $remarks, 'TLR', false, 'L');
                        $pdf->Cell(22, 7, $detail['cheque_no'], 'TLR', false, 'L');
                        $pdf->Cell(20, 7, $detail['cheque_date'], 'TLR', false, 'L');
                        $pdf->Cell(20, 7, number_format($detail['debit'],2), 'TLR', false, 'R');
                        $pdf->Cell(20, 7, number_format($detail['credit'],2), 'TLR', false, 'R');
                        if($balance < 0)
                        {
                            $balance = abs($balance);
                            $pdf->Cell(25, 7, 'Cr '.number_format($balance,2), 'TLR', false, 'R');    
                        }
                        else
                        {
                            $pdf->Cell(25, 7, 'Dr '.number_format($balance,2), 'TLR', false, 'R');   
                        }
                     }
                     elseif($index==count($arrRemarks)-1) {
                        $pdf->Ln(7);
                        $pdf->SetFont('helvetica', '', 7);
                        $pdf->Cell(7, 7, '', 'BLR', false, 'R');
                        $pdf->Cell(20, 7, '', 'BLR', false, 'L');
                        $pdf->Cell(30, 7, '', 'BLR', false, 'L');
                        $pdf->Cell(40, 7, $remarks, 'BLR', false, 'L');
                        $pdf->Cell(22, 7, '', 'BLR', false, 'L');
                        $pdf->Cell(20, 7, '', 'BLR', false, 'L');
                        $pdf->Cell(20, 7, '', 'BLR', false, 'R');
                        $pdf->Cell(20, 7, '', 'BLR', false, 'R');
                        $pdf->Cell(25, 7, '', 'BLR', false, 'R');   
                     }
                     else {
                        $pdf->Ln(7);
                        $pdf->SetFont('helvetica', '', 8);
                        $pdf->Cell(7, 7, '', 'LR', false, 'R');
                        $pdf->Cell(20, 7, '', 'LR', false, 'L');
                        $pdf->Cell(30, 7, '', 'LR', false, 'L');
                        $pdf->Cell(40, 7, $remarks, 'LR', false, 'L');
                        $pdf->Cell(22, 7, '', 'LR', false, 'L');
                        $pdf->Cell(20, 7, '', 'LR', false, 'L');
                        $pdf->Cell(20, 7, '', 'LR', false, 'R');
                        $pdf->Cell(20, 7, '', 'LR', false, 'R');
                        $pdf->Cell(25, 7, '', 'LR', false, 'R');
                    }
                }
            }

            }
            $pdf->Ln(7);
            $pdf->Cell(139, 7,'', 0, false, 'R');
            $pdf->Cell(20, 7, number_format($total_debit,2), 1, false, 'R');
            $pdf->Cell(20, 7, number_format($total_credit,4), 1, false, 'R');
        }

        //Close and output PDF document
        $pdf->Output('Ledger Report:'.date('YmdHis').'.pdf', 'I');
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
         $this->SetFont('times', 'B', 12);
        $this->Ln(12);
        $this->Cell(0, 10, 'From : '. $this->data['from_date'].' | '.'To : '. $this->data['to_date'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->ln(12);
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(  7, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell( 20, 7, 'Doc. Date', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell( 30, 7, 'Doc. No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell( 40, 7, 'Remarks', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell( 22, 7, 'Cheque No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell( 20, 7, 'Cheque Date', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell( 20, 7, 'Debit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell( 20, 7, 'Credit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell( 25, 7, 'Balance', 1, false, 'C', 0, '', 0, false, 'M', 'M');

       
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
