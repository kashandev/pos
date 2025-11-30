<?php

class ControllerReportPartyLedger extends HController {

    protected function getAlias() {
        return 'report/party_ledger';
    }

    protected function init() {
        $this->model[$this->getAlias()] = $this->load->model('gl/ledger');
        $this->data['lang'] = $this->load->language($this->getAlias());
        $this->document->setTitle($this->data['lang']['heading_title']);
        $this->data['token'] = $this->session->data['token'];
    }

    protected function getList() {
        parent::getList();

        $this->data['partner_types'] = $this->session->data['partner_types'];

        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
        $this->data['href_get_detail_report'] = $this->url->link($this->getAlias() .'/getDetailReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_get_summary_report'] = $this->url->link($this->getAlias() .'/getSummaryReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_print_detail_report'] = $this->url->link($this->getAlias() .'/printDetailReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_print_summary_report'] = $this->url->link($this->getAlias() .'/printSummaryReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['date_from'] = stdDate($this->session->data['fiscal_date_from']);
        $this->data['date_to'] = stdDate(($this->session->data['fiscal_date_to'] > date('Y-m-d') ? '' : $this->session->data['fiscal_date_to']));
        $this->data['href_print_excel'] = $this->url->link($this->getAlias() .'/printReportExcel', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['href_print_detail_excel'] = $this->url->link($this->getAlias() .'/printDetailReportExcel', 'token=' . $this->session->data['token'], 'SSL');



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

    public function getDetailReport() {
        $lang = $this->load->language('report/party_ledger');
        $post = $this->request->post;
        $session = $this->session->data;

        $this->model['party_ledger'] = $this->load->model('report/party_ledger');
        $where = "l.company_id = '".$this->session->data['company_id']."'";
        $where .= " AND l.company_branch_id = '".$this->session->data['company_branch_id']."'";
        $where .= " AND l.fiscal_year_id = '".$this->session->data['fiscal_year_id']."'";
        if($post['date_from'] != '') {
            $where .= " AND l.document_date >= '".MySqlDate($post['date_from'])."'";
        }
        if($post['date_to'] != '') {
            $where .= " AND l.document_date <= '".MySqlDate($post['date_to'])."'";
        }
        if($post['partner_type_id'] != '') {
            $where .= " AND l.partner_type_id = '".$post['partner_type_id']."'";
        }
        if($post['partner_id'] != '') {
            $where .= " AND l.partner_id = '".$post['partner_id']."'";
        }

        $rows = $this->model['party_ledger']->getPartyLedger($where);
//d($rows,true);
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
//d($json,true);
        echo json_encode($json);
        exit;
    }

    public function printDetailReport() {

        ini_set('max_execution_time',400);
        ini_set('memory_limit','3072M');

        $lang = $this->load->language($this->getAlias());
        $post = $this->request->post;
        $session = $this->session->data;
        //d(array($lang, $post, $session), true);

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

        if($post['partner_type_id'] != "") {
            $this->model['partner_type'] = $this->load->model('common/partner_type');
            $partner_type = $this->model['partner_type']->getRow(array('partner_type_id' => $post['partner_type_id']));
            $post['partner_type'] = $partner_type['name'];
        } else {
            $post['partner_type'] = '';
        }

        if($post['partner_id'] != "") {
            $this->model['partner'] = $this->load->model('common/partner');
            $partner = $this->model['partner']->getRow(array('partner_id' => $post['partner_id']));
            $post['partner_name'] = $partner['name'];
        } else {
            $post['partner_name'] = '';
        }

        $arrLedger = array();
        $this->model['party_ledger'] = $this->load->model('report/party_ledger');

        $where = "l.company_id = '".$this->session->data['company_id']."'";
        $where .= " AND l.company_branch_id = '".$this->session->data['company_branch_id']."'";
        $where .= " AND l.fiscal_year_id = '".$this->session->data['fiscal_year_id']."'";
        if($post['date_from'] != '') {
            $where .= " AND l.document_date < '".$post['date_from']."'";
        }
        if($post['partner_type_id'] != '') {
            $where .= " AND l.partner_type_id = '".$post['partner_type_id']."'";
        }
        if($post['partner_id'] != '') {
            $where .= " AND l.partner_id = '".$post['partner_id']."'";
        }
        $rows = $this->model['party_ledger']->getPartyOpening($where, array('partner_type', 'partner_name'));
        foreach($rows as $row) {
            $arrLedger[$row['partner_type'] . ': ' . $row['partner_name']][$row['account']][] = $row;
        }
        //d($arrLedger, true);

        $where = "l.company_id = '".$this->session->data['company_id']."'";
        $where .= " AND l.company_branch_id = '".$this->session->data['company_branch_id']."'";
        $where .= " AND l.fiscal_year_id = '".$this->session->data['fiscal_year_id']."'";
        if($post['date_from'] != '') {
            $where .= " AND l.document_date >= '".$post['date_from']."'";
        }
        if($post['date_to'] != '') {
            $where .= " AND l.document_date <= '".$post['date_to']."'";
        }
        if($post['partner_type_id'] != '') {
            $where .= " AND l.partner_type_id = '".$post['partner_type_id']."'";
        }
        if($post['partner_id'] != '') {
            $where .= " AND l.partner_id = '".$post['partner_id']."'";
        }
        $rows = $this->model['party_ledger']->getPartyLedger($where, array('partner_type', 'partner_name','document_date', 'document_identity', 'sort_order'));
        foreach($rows as $row) {
            $arrLedger[$row['partner_type'] . ': ' . $row['partner_name']][$row['account']][] = $row;
        }

        $this->model['image'] = $this->load->model('tool/image');
        $this->model['setting'] = $this->load->model('common/setting');
        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'general',
            'field' => 'company_logo',
        ));
        $company_logo = $setting['value'];

        //d($arrLedger, true);
        $pdf = new PDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Party Ledger Report');
        $pdf->SetSubject('Party Ledger Report');

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_branch_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $company_logo,
            'date_from' => $post['date_from'],
            'date_to' => $post['date_to'],
            'type' => 'Detail',
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(15, 50, 10);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // set font
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->AddPage();
        foreach($arrLedger as $partner => $accounts) {
            $pdf->ln(5);
            $pdf->Cell(0,7,$partner);
            foreach($accounts as $account => $records) {
                $sr = 0;
                $total_debit = 0;
                $total_credit = 0;
                foreach($records as $detail) {
                    $total_debit += $detail['debit'];
                    $total_credit += $detail['credit'];
                    $balance = $total_debit - $total_credit;
                    $sr++;
                if(strlen($detail['remarks'])<=40) {
                    $pdf->Ln(7);
                    $pdf->SetFont('helvetica', '', 8);
                    $pdf->Cell(7, 7, $sr, 1, false, 'R');
                    $pdf->Cell(20, 7, stdDate($detail['document_date']), 1, false, 'L');
                    $pdf->Cell(30, 7, $detail['document_identity'], 1, false, 'L');
                    $pdf->Cell(60, 7, $detail['remarks'], 1, false, 'L');
                    $pdf->Cell(30, 7, $detail['ref_document_identity'], 1, false, 'L');
                    if($detail['po_no'] < 25)
                    {
                        $pdf->Cell(30, 7, $detail['po_no'], 1, false, 'R');    
                    }
                    else
                    {
                        $pdf->MultiCell(30, 7, $detail['po_no'], 1, 'L', 0, 0, '', '', true);
                        // $pdf->Cell(30, 7, $detail['po_no'], 1, false, 'R');
                    }
                    
                    $pdf->Cell(30, 7, number_format($detail['debit'],2), 1, false, 'R');
                    $pdf->Cell(30, 7, number_format($detail['credit'],2), 1, false, 'R');
                    if($balance < 0)
                        {
                            $balance = abs($balance);
                            $pdf->Cell(30, 7, 'Cr '.number_format($balance,2),1, false, 'R');    
                        }
                        else
                        {
                            $pdf->Cell(30, 7, 'Dr '.number_format($balance,2),1, false, 'R');   
                        }
                }
                else
                {
                    $arrRemarks = str_split($detail['remarks'], 40);
                    foreach($arrRemarks as $index => $remarks) {
                         if($index==0) {
                            $pdf->Ln(7);
                            $pdf->SetFont('helvetica', '', 8);
                            $pdf->Cell(7, 7, $sr, 'TLR', false, 'R');
                            $pdf->Cell(20, 7, stdDate($detail['document_date']), 'TLR', false, 'L');
                            $pdf->Cell(30, 7, $detail['document_identity'], 'TLR', false, 'L');
                            $pdf->Cell(60, 7, $remarks, 'TLR', false, 'L');
                            $pdf->Cell(30, 7, $detail['ref_document_identity'], 'TLR', false, 'L');
                            if($detail['po_no'] < 25)
                            {
                                $pdf->Cell(30, 7, $detail['po_no'], 'TLR', false, 'R');    
                            }
                            else
                            {
                                $pdf->MultiCell(30, 7, $detail['po_no'], 'TLR', 'L', 0, 0, '', '', true);
                                // $pdf->Cell(30, 7, $detail['po_no'], 1, false, 'R');
                            }
                            $pdf->Cell(30, 7, number_format($detail['debit'],2), 'TLR', false, 'R');
                            $pdf->Cell(30, 7, number_format($detail['credit'],2), 'TLR', false, 'R');
                            if($balance < 0)
                            {
                                $balance = abs($balance);
                                $pdf->Cell(30, 7, 'Cr '.number_format($balance,2), 'TLR', false, 'R');    
                            }
                            else
                            {
                                $pdf->Cell(30, 7, 'Dr '.number_format($balance,2), 'TLR', false, 'R');   
                            }
                         }
                         elseif($index==count($arrRemarks)-1) {
                            $pdf->Ln(7);
                            $pdf->SetFont('helvetica', '', 8);
                            $pdf->Cell(7, 7, '', 'BLR', false, 'R');
                            $pdf->Cell(20, 7, '', 'BLR', false, 'L');
                            $pdf->Cell(30, 7, '', 'BLR', false, 'L');
                            $pdf->Cell(60, 7, $remarks, 'BLR', false, 'L');
                            $pdf->Cell(30, 7, '', 'BLR', false, 'L');
                            $pdf->Cell(30, 7, '', 'BLR', false, 'L');
                            $pdf->Cell(30, 7, '', 'BLR', false, 'R');
                            $pdf->Cell(30, 7, '', 'BLR', false, 'R');
                            $pdf->Cell(30, 7, '', 'BLR', false, 'R');
                         }
                         else {
                            $pdf->Ln(7);
                            $pdf->SetFont('helvetica', '', 8);
                            $pdf->Cell(7, 7, '', 'LR', false, 'R');
                            $pdf->Cell(20, 7, '', 'LR', false, 'L');
                            $pdf->Cell(30, 7, '', 'LR', false, 'L');
                            $pdf->Cell(60, 7, $remarks, 'LR', false, 'L');
                            $pdf->Cell(30, 7, '', 'LR', false, 'L');
                            $pdf->Cell(30, 7, '', 'LR', false, 'L');
                            $pdf->Cell(30, 7, '', 'LR', false, 'R');
                            $pdf->Cell(30, 7, '', 'LR', false, 'R');
                            $pdf->Cell(30, 7, '', 'LR', false, 'R');
                         }
                     }
                }
                    
                }
                $pdf->Ln(7);
                $pdf->Cell(177, 7,'', 0, false, 'R');
                $pdf->Cell(30, 7, number_format($total_debit,2), 1, false, 'R');
                $pdf->Cell(30, 7, number_format($total_credit,4), 1, false, 'R');
            }
        }

        //Close and output PDF document
        $pdf->Output('Party Ledger Report:'.date('YmdHis').'.pdf', 'I');
    }

    public function printDetailReportExcel()
    {
        ini_set('max_execution_time',400);
        ini_set('memory_limit','3072M');

        $lang = $this->load->language($this->getAlias());
        $post = $this->request->post;
        $session = $this->session->data;
        //d(array($lang, $post, $session), true);

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

        if($post['partner_type_id'] != "") {
            $this->model['partner_type'] = $this->load->model('common/partner_type');
            $partner_type = $this->model['partner_type']->getRow(array('partner_type_id' => $post['partner_type_id']));
            $post['partner_type'] = $partner_type['name'];
        } else {
            $post['partner_type'] = '';
        }

        if($post['partner_id'] != "") {
            $this->model['partner'] = $this->load->model('common/partner');
            $partner = $this->model['partner']->getRow(array('partner_id' => $post['partner_id']));
            $post['partner_name'] = $partner['name'];
        } else {
            $post['partner_name'] = '';
        }

        $arrLedger = array();
        $this->model['party_ledger'] = $this->load->model('report/party_ledger');

        $where = "l.company_id = '".$this->session->data['company_id']."'";
        $where .= " AND l.company_branch_id = '".$this->session->data['company_branch_id']."'";
        $where .= " AND l.fiscal_year_id = '".$this->session->data['fiscal_year_id']."'";
        if($post['date_from'] != '') {
            $where .= " AND l.document_date < '".$post['date_from']."'";
        }
        if($post['partner_type_id'] != '') {
            $where .= " AND l.partner_type_id = '".$post['partner_type_id']."'";
        }
        if($post['partner_id'] != '') {
            $where .= " AND l.partner_id = '".$post['partner_id']."'";
        }
        $rows = $this->model['party_ledger']->getPartyOpening($where, array('partner_type', 'partner_name'));
        foreach($rows as $row) {
            $arrLedger[$row['partner_type'] . ': ' . $row['partner_name']][$row['account']][] = $row;
        }
        //d($arrLedger, true);

        $where = "l.company_id = '".$this->session->data['company_id']."'";
        $where .= " AND l.company_branch_id = '".$this->session->data['company_branch_id']."'";
        $where .= " AND l.fiscal_year_id = '".$this->session->data['fiscal_year_id']."'";
        if($post['date_from'] != '') {
            $where .= " AND l.document_date >= '".$post['date_from']."'";
        }
        if($post['date_to'] != '') {
            $where .= " AND l.document_date <= '".$post['date_to']."'";
        }
        if($post['partner_type_id'] != '') {
            $where .= " AND l.partner_type_id = '".$post['partner_type_id']."'";
        }
        if($post['partner_id'] != '') {
            $where .= " AND l.partner_id = '".$post['partner_id']."'";
        }
        $rows = $this->model['party_ledger']->getPartyLedger($where, array('partner_type', 'partner_name','document_date', 'document_identity', 'sort_order'));
        foreach($rows as $row) {
            $arrLedger[$row['partner_type'] . ': ' . $row['partner_name']][$row['account']][] = $row;
        }

        $this->model['image'] = $this->load->model('tool/image');
        $this->model['setting'] = $this->load->model('common/setting');
        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'general',
            'field' => 'company_logo',
        ));
        $company_logo = $setting['value'];


        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);

        $objPHPExcel->getProperties()
            ->setCreator("hira")
            ->setLastModifiedBy("Hira")
            ->setTitle("Party Ledger");

        $objPHPExcel->data = array(
            'company_name' => $session['company_name'],
            'report_name' => 'Party Ledger Detail Report',
            'date_to' => $post['date_to'],
            'date_from' => $post['date_from']
        );

        $rowCount = 1;

        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":H".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $session['company_branch_name']);
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

        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":H".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Party Ledger');
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

        // d($arrLedger,true);
        foreach($arrLedger as $key => $value) {
            $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":H".($rowCount));
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $key);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->getFill('ebebeb');
            $rowCount ++;

            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);

            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Document Date');
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'Document No.');
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Remarks');
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'Ref. Doc.');
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'PO No.');
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'Debit');
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, 'Credit');
            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, 'Balance');
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':H'.$rowCount)->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    )
                )
            );
            $rowCount++;
            $total_amount_credit = 0;
            $total_amount_debit = 0;
            foreach ($value as $temp => $details)
            {
                foreach ($details as $key_1 => $value_1) {
                    $total_amount_credit += $value_1['credit'];
                    $total_amount_debit += $value_1['debit'];

                    $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $value_1['document_date']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $value_1['document_identity']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value_1['remarks']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value_1['ref_document_identity']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value_1['po_no']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $value_1['debit']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $value_1['credit']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $value_1['balance']);

                    $rowCount++;
                }
                
            }
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $total_amount_credit)->getStyle('G'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $total_amount_debit)->getStyle('F'.$rowCount)->getFont()->setBold( true );
            $rowCount+=3;

        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Party Ledger Detail.xlsx"');
        header('Cache-Control: max-age=0');
        //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        //$objWriter->save('some_excel_file.xlsx');
        $objWriter->save('php://output');
        exit;


    }

    public function printSummaryReport() {
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

        if($post['partner_type_id'] != "") {
            $this->model['partner_type'] = $this->load->model('common/partner_type');
            $partner_type = $this->model['partner_type']->getRow(array('partner_type_id' => $post['partner_type_id']));
            $post['partner_type'] = $partner_type['name'];
        } else {
            $post['partner_type'] = '';
        }

        if($post['partner_id'] != "") {
            $this->model['partner'] = $this->load->model('common/partner');
            $partner = $this->model['partner']->getRow(array('partner_id' => $post['partner_id']));
            $post['partner_name'] = $partner['name'];
        } else {
            $post['partner_name'] = '';
        }

        $arrLedger = array();
        $this->model['party_ledger'] = $this->load->model('report/party_ledger');

        $filter['company_id'] = $this->session->data['company_id'];
        $filter['company_branch_id'] = $this->session->data['company_branch_id'];
        $filter['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
        $filter['from_date'] = $post['date_from'];
        $filter['to_date'] = $post['date_to'];
        $filter['partner_type_id'] = $post['partner_type_id'];
        $filter['partner_id'] = $post['partner_id'];

        $rows = $this->model['party_ledger']->getPartySummary($filter);
        //d($rows,true);


        foreach($rows as $row) {
            $arrLedger[$row['partner_type']][] = array(
                'partner_type' => $row['partner_type'],
                'partner_name' => $row['partner_name'],
                'previous' => $row['previous'],
                'debit' => $row['debit'],
                'credit' => $row['credit'],
                'balance' => $row['previous'] + $row['debit'] - $row['credit'],
            );
        }

        $this->model['image'] = $this->load->model('tool/image');
        $this->model['setting'] = $this->load->model('common/setting');
        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'general',
            'field' => 'company_logo',
        ));
        $company_logo = $setting['value'];

        // d($company_logo, true);
        $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Party Summary Report');
        $pdf->SetSubject('Party Summary Report');

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_branch_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $company_logo,
            'date_from' => $post['date_from'],
            'date_to' => $post['date_to'],
            'type' => 'summary',
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10, 50, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // set font
        $pdf->AddPage();
        foreach($arrLedger as $partner_type => $records) {

            $pdf->ln(7);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(50,7,$partner_type);
            $sr =0;
            $total_debit = 0;
            $total_credit = 0;
           $pdf->Ln(3);
            foreach($records as $detail) {
                $total_debit += $detail['debit'];
                $total_credit += $detail['credit'];
                $balance = $total_debit - $total_credit;
                $sr++;
                $pdf->Ln(7);
                $pdf->SetFont('helvetica', '', 8);
                $pdf->Cell(7, 7, $sr, 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(75, 7, $detail['partner_name'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(30, 7, ($detail['previous'] < 0?'CR ' . number_format(-1 * $detail['previous'],2):'DR ' . number_format($detail['previous'],2)), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 7, number_format($detail['debit'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 7, number_format($detail['credit'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(30, 7, ($detail['balance'] < 0?'CR ' . number_format(-1 * $detail['balance'],2):'DR ' . number_format($detail['balance'],2)), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            }
            $pdf->Ln(7);
            $pdf->Cell(112, 7,'', 0, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 7, number_format($total_debit,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 7, number_format($total_credit,4), 1, false, 'R', 0, '', 1, false, 'M', 'M');
        }

        //Close and output PDF document
        $pdf->Output('Party Summary Report:'.date('YmdHis').'.pdf', 'I');
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

        if($post['partner_type_id'] != "") {
            $this->model['partner_type'] = $this->load->model('common/partner_type');
            $partner_type = $this->model['partner_type']->getRow(array('partner_type_id' => $post['partner_type_id']));
            $post['partner_type'] = $partner_type['name'];
        } else {
            $post['partner_type'] = '';
        }

        if($post['partner_id'] != "") {
            $this->model['partner'] = $this->load->model('common/partner');
            $partner = $this->model['partner']->getRow(array('partner_id' => $post['partner_id']));
            $post['partner_name'] = $partner['name'];
        } else {
            $post['partner_name'] = '';
        }

        $arrLedger = array();
        $this->model['party_ledger'] = $this->load->model('report/party_ledger');

        $filter['company_id'] = $this->session->data['company_id'];
        $filter['company_branch_id'] = $this->session->data['company_branch_id'];
        $filter['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
        $filter['from_date'] = $post['date_from'];
        $filter['to_date'] = $post['date_to'];
        $filter['partner_type_id'] = $post['partner_type_id'];
        $filter['partner_id'] = $post['partner_id'];

        $rows = $this->model['party_ledger']->getPartySummary($filter);
        //d($rows,true);


        foreach($rows as $row) {
            $arrLedger[$row['partner_type']][] = array(
                'partner_type' => $row['partner_type'],
                'partner_name' => $row['partner_name'],
                'previous' => $row['previous'],
                'debit' => $row['debit'],
                'credit' => $row['credit'],
                'balance' => $row['previous'] + $row['debit'] - $row['credit'],
            );
        }

        $this->model['image'] = $this->load->model('tool/image');
        $this->model['setting'] = $this->load->model('common/setting');
        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'general',
            'field' => 'company_logo',
        ));
        $company_logo = $setting['value'];
//        echo '<pre>';
//        print_r($arrLedger);
//        exit;

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
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $session['company_branch_name']);
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

            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'Partner Name');
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Previous');
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'Debit');
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'Credit');
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'Balance');
            $objPHPExcel->getActiveSheet()->getStyle('B'.$rowCount.':F'.$rowCount)->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    )
                )
            );
            $rowCount++;
            $total_amount_credit = 0;
            $total_amount_debit = 0;
            foreach ($value as $key_1 => $value_1)
            {
                $total_amount_credit += $value_1['credit'];
                $total_amount_debit += $value_1['debit'];

                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $value_1['partner_name']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value_1['previous']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value_1['debit']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value_1['credit']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $value_1['balance']);

                $rowCount++;
            }
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $total_amount_credit)->getStyle('D'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $total_amount_debit)->getStyle('E'.$rowCount)->getFont()->setBold( true );
            $rowCount+=3;

        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Party Ledger Summary.xlsx"');
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
        $this->SetFont('times', 'B', 10);

        if($this->data['type'] == 'Detail')
        {
            $this->Ln(10);
            $this->Cell(70, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->SetFont('helvetica', 'B', 14);
            $this->Cell(60, 7, 'Detail', 0, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->SetFont('helvetica', 'B', 10);
            $this->Cell(60, 7, 'From Date  = '.stdDate($this->data['date_from']), 0, false, 'R', 0, '', 0, false, 'M', 'M');
            $this->Ln(7);
            $this->Cell(130, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
            $this->Cell(60, 7, 'To Date  = '.stdDate($this->data['date_to']), 0, false, 'R', 0, '', 0, false, 'M', 'M');
            $this->ln(7);
            $this->SetFont('helvetica', 'B', 8);
            $this->Cell( 7, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(20, 7, 'Doc. Date', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(30, 7, 'Doc. No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(60, 7, 'Remarks', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(30, 7, 'Ref Doc', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(30, 7, 'Po No', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(30, 7, 'Debit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(30, 7, 'Credit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(30, 7, 'Balance', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        }
        else{
            $this->Ln(10);
            $this->SetFont('helvetica', 'B', 14);
            $this->Cell(70, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(60, 7, 'Summary', 0, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->SetFont('helvetica', 'B', 10);
            $this->Cell(60, 7, 'From Date  = '.stdDate($this->data['date_from']), 0, false, 'R', 0, '', 0, false, 'M', 'M');
            $this->ln(7);
            $this->Cell(130, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
            $this->Cell(60, 7, 'To Date  = '.stdDate($this->data['date_to']), 0, false, 'R', 0, '', 0, false, 'M', 'M');
            $this->ln(10);
            $this->SetFont('helvetica', 'B', 8);
            $this->Cell( 7, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(75, 7, 'Partner Name', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(30, 7, 'Previous', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, 'Debit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, 'Credit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(30, 7, 'Balance', 1, false, 'C', 0, '', 0, false, 'M', 'M');

        }
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