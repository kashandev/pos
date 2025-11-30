<?php

class ControllerReportTrialBalance extends HController {

    protected function getAlias() {
        return 'report/trial_balance';
    }

    protected function getList() {
        parent::getList();

        // $this->data['date_to'] = stdDate();
        // $this->data['date_from'] = stdDate($this->session->data['fiscal_date_from']);
        $this->data['action_print'] = $this->url->link($this->getAlias() .'/printReport', 'token=' . $this->session->data['token'], 'SSL');

        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $this->data['branchs'] = $this->model['company_branch']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->data['href_print_excel'] = $this->url->link($this->getAlias() .'/printReportExcel', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['date_from'] = stdDate($this->session->data['fiscal_date_from']);
        $this->data['date_to'] = stdDate(($this->session->data['fiscal_date_to'] > date('Y-m-d') ? '' : $this->session->data['fiscal_date_to']));

        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);

        $this->data['strValidation'] = "{
            'rules': {
                'date_from': {'required': true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},
                'date_to': {'required': true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},
            },
        }";

        $this->template = $this->getAlias() . '.tpl';
        $this->response->setOutput($this->render());
    }

    public function printReportExcel()
    {
        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        $this->model['trial_balance'] = $this->load->model('report/trial_balance');
        $lang = $this->load->language($this->getAlias());
        $post = $this->request->post;
        $session = $this->session->data;
        $arrFilter['to_date'] = MySqlDate($post['date_to']);
        $arrFilter['from_date'] = MySqlDate($post['date_from']);
        $arrFilter['company_id'] = $session['company_id'];
        // $arrFilter['branch_id'] = $post['branch_id'];
        $arrFilter['level'] = $post['level'];
        $number_cols = $post['col'];
        $this->model['company'] = $this->load->model('setup/company');
        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $this->model['bank_receipt'] = $this->load->model('gl/bank_receipt');
        $this->model['bank_receipt_detail'] = $this->load->model('gl/bank_receipt_detail');
        $this->model['coa'] = $this->load->model('gl/coa');
        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        $branch = $this->model['company_branch']->getRow(array('company_branch_id' => $session['company_branch_id']));
        $rows = $this->model['trial_balance']->getTrailBalanceConsolidate($arrFilter);
        // d($rows,true);
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getProperties()
            ->setCreator("Farrukh Afaq")
            ->setLastModifiedBy("Hira")
            ->setTitle("Trial Balance");

        $objPHPExcel->data = array(
            'company_name' => $session['company_name'],
            'report_name' => 'Trial Balance'
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
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Trial Balance');
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(17);


        if($number_cols == 2)
        {
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Level1');
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'Level2');
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Description');
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'Closing Debit');
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'Closing Credit');
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':E'.$rowCount)->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    )
                )
            );
            $rowCount++;
            $total_tot_debit = 0;
            $total_tot_credit = 0;
            foreach ($rows as $key => $value)
            {
                $total_tot_debit += $value['tot_debit'];
                $total_tot_credit += $value['tot_credit'];
                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $value['level1_display_name']);
                $rowCount++;
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $value['level2_display_name']);
                $rowCount++;
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value['account']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value['tot_debit']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value['tot_credit']);
                $rowCount++;
            }
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $total_tot_debit);
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $total_tot_credit);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':E'.$rowCount)->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    )
                )
            );
        }
        else
        {
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Level1');
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'Level2');
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Description');
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'Opening Debit');
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'Opening Credit');
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'Current Debit');
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, 'Current Credit');
            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, 'Closing Debit');
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, 'Closing Credit');
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':I'.$rowCount)->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    )
                )
            );
            $rowCount++;

            $total_op_debit = 0;
            $total_op_credit = 0;
            $total_cur_debit = 0;
            $total_cur_credit = 0;
            $total_tot_debit = 0;
            $total_tot_credit = 0;
            foreach ($rows as $key => $value)
            {
                $total_op_debit += $value['op_debit'];
                $total_op_credit += $value['op_credit'];
                $total_cur_debit += $value['cur_debit'];
                $total_cur_credit += $value['cur_credit'];
                $total_tot_debit += $value['tot_debit'];
                $total_tot_credit += $value['tot_credit'];

                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $value['level1_display_name']);
                $rowCount++;
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $value['level2_display_name']);
                $rowCount++;
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value['account']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value['op_debit']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value['op_credit']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $value['cur_debit']);
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $value['cur_credit']);
                $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $value['tot_debit']);
                $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $value['tot_credit']);
                $rowCount++;
            }
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $total_op_debit);
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $total_op_credit);
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $total_cur_debit);
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $total_cur_credit);
            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $total_tot_debit);
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $total_tot_credit);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':I'.$rowCount)->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    )
                )
            );
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="trial_balance.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save('php://output');
        exit;
    }

    public function printReport() {
        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        $this->model['trial_balance'] = $this->load->model('report/trial_balance');
        $lang = $this->load->language($this->getAlias());
        $post = $this->request->post;
        // d($post);
        $session = $this->session->data;
        $arrFilter['to_date'] = MySqlDate($post['date_to']);
        $arrFilter['from_date'] = MySqlDate($post['date_from']);
        $arrFilter['company_id'] = $session['company_id'];
        if($this->request->post['a_company_branch_id']){
            $arrFilter['branch_id'] = $post['a_company_branch_id'];
        }
        // d($arrFilter);
        $this->model['company'] = $this->load->model('setup/company');
        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $this->model['bank_receipt'] = $this->load->model('gl/bank_receipt');
        $this->model['bank_receipt_detail'] = $this->load->model('gl/bank_receipt_detail');
        $this->model['coa'] = $this->load->model('gl/coa');

        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        $branch = $this->model['company_branch']->getRow(array('company_branch_id' => $session['company_branch_id']));
        $department = $this->model['company_branch']->getRow(array('company_branch_id' => $post['a_company_branch_id']));


        $rows = $this->model['trial_balance']->getTrailBalanceConsolidate($arrFilter);
        // d($rows);
        $pdf = new PDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Fahad Siddiqui');
        $pdf->SetTitle('Trail Balance Report');
        $pdf->SetSubject('Trail Balance Report');

        // d($post['date_to'],true);
        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'company_address' => $branch['address'],
            'company_phone' => $branch['phone_no'],
            'report_name' => 'Trail Balance Report',
            'from_date' => $post['date_from'],
            'to_date' => $post['date_to'],
            'department'=>$department['name'],
            'company_logo' => $session['company_image'],
            'cols' => $post['col']
        );
        // d($pdf->data,true);

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(15, 55, 5);
        $pdf->SetHeaderMargin(2);
        $pdf->SetFooterMargin(2);
        // set font
        $pdf->SetFont('helvetica', 'B', 10);

        // add a page
        $pdf->AddPage();
        $sr = 0;
        $pdf->Ln(0);
        $Amount = 0;
        $WhAmount = 0;
        $OtAmount = 0;
        $NetAmount = 0;
        $pdf->SetFont('helvetica', '', 9);
        $total_op_debit = 0;
        $total_op_credit = 0;
        $total_cur_debit = 0;
        $total_cur_credit = 0;
        $total_tot_debit = 0;
        $total_tot_credit = 0;

        foreach($rows as $row){
            $arrRows[$row['gl_type']][$row['level1_display_name']][$row['level2_display_name']][] = array(
                'account' => $row['account'],
                'op_debit' => $row['op_debit'],
                'op_credit' => $row['op_credit'],
                'cur_debit' => $row['cur_debit'],
                'cur_credit' => $row['cur_credit'],
                'tot_debit' => $row['tot_debit'],
                'tot_credit' => $row['tot_credit']
            );
        }
        // d($arrRows,true);

        // for 2 cols
        if($post['col'] == 2)
        {

            foreach ($arrRows as $key => $value) {
                $pdf->ln(8);
                $pdf->SetFillColor(255,255,255);
                 $pdf->SetFont('helvetica', 'B,I', 10);
                $pdf->Cell(0,8,$key,0,0,'',1);
                foreach($value as $level1_display_name => $level2) {
                     $pdf->SetFont('helvetica', '', 8);
                    if($post['display_level'] > 1) {
                        $pdf->ln(8);
                        $pdf->SetFillColor(255,255,255);
                        $pdf->Cell(0,8,$level1_display_name,0,0,'',1);
                    }
                    $level1_op_debit = 0;
                    $level1_op_credit = 0;
                    $level1_cur_debit = 0;
                    $level1_cur_credit = 0;
                    $level1_tot_debit = 0;
                    $level1_tot_credit = 0;
                    $level1_balance = 0;


                    foreach($level2 as $level2_display_name => $level3) {
                        if($post['display_level']==3) {

                            $pdf->ln(8);
                            $pdf->SetFillColor(255,255,255);

                            $pdf->Cell(10,8,'',0,0,'',1);
                            $pdf->Cell(175,8,$level2_display_name,0,0,'L',1);
                        }
                        $level2_op_debit = 0;
                        $level2_op_credit = 0;
                        $level2_cur_debit = 0;
                        $level2_cur_credit = 0;
                        $level2_tot_debit = 0;
                        $level2_tot_credit = 0;
                        $level2_balance = 0;

                        foreach($level3 as $record) {

                            if($post['display_level']==3) {
                                $pdf->ln(9);
                                $pdf->Cell(15,8,'','B',0,'',1);

                               $pdf->SetFont('helvetica', '', 8);
                                $pdf->Cell(200, 8, html_entity_decode($record['account']), 'B', false, 'L', 0, '', 1);
                                // $pdf->Cell(30, 8, number_format($record['op_debit'],2), 'B', false, 'C', 0, '', 1);
                                // $pdf->Cell(30, 8, number_format($record['op_credit'],2), 'B', false, 'C', 0, '', 1);
                                // $pdf->Cell(30, 8, number_format($record['cur_debit'],2), 'B', false, 'C', 0, '', 1);
                                // $pdf->Cell(30, 8, number_format($record['cur_credit'],2), 'B', false, 'C', 0, '', 1);
                                $pdf->Cell(30, 8, number_format($record['tot_debit'],2), 'B', false, 'C', 0, '', 1);
                                $pdf->Cell(30, 8, number_format($record['tot_credit'],2), 'B', false, 'C', 0, '', 1);

                            }

                            $level2_op_debit += $record['op_debit'];
                            $level2_op_credit += $record['op_credit'];
                            $level2_cur_debit += $record['cur_debit'];
                            $level2_cur_credit += $record['cur_credit'];
                            $level2_tot_debit += $record['tot_debit'];
                            $level2_tot_credit += $record['tot_credit'];


                            $level2_balance = $level2_tot_debit - $level2_tot_credit;
                            $level2_tot_debit = round(( $level2_balance > 0 ? $level2_balance : 0),2);
                            $level2_tot_credit = round(($level2_balance < 0 ? (-1 * $level2_balance) : 0),2);

                            $total_op_debit += $record['op_debit'];
                            $total_op_credit += $record['op_credit'];
                            $total_cur_debit += $record['cur_debit'];
                            $total_cur_credit += $record['cur_credit'];
                            $total_tot_debit += $record['tot_debit'];
                            $total_tot_credit += $record['tot_credit'];
                        }
                        // level 2

                        if($post['display_level'] > 1) {
                            $pdf->ln(9);
                            $pdf->Cell(10,8,'','B',0,'',1);
                            $pdf->Cell(205,8,$level2_display_name,'B',0,'L',1);
                            // $pdf->Cell(30,8,number_format($level2_op_debit,2),'B',0,'C',1);
                            // $pdf->Cell(30,8,number_format($level2_op_credit,2),'B',0,'C',1);
                            // $pdf->Cell(30,8,number_format($level2_cur_debit,2),'B',0,'C',1);
                            // $pdf->Cell(30,8,number_format($level2_cur_credit,2),'B',0,'C',1);
                            $pdf->Cell(30,8,number_format($level2_tot_debit,2),'B',0,'C',1);
                            $pdf->Cell(30,8,number_format($level2_tot_credit,2),'B',0,'C',1);
                            $pdf->ln(5);
                        }

                        $level1_op_credit += $level2_op_credit;
                        $level1_op_debit += $level2_op_debit;
                        $level1_cur_credit += $level2_cur_credit;
                        $level1_cur_debit += $level2_cur_debit;
                        $level1_tot_credit += $level2_tot_credit;
                        $level1_tot_debit += $level2_tot_debit;

                        $level1_balance = $level1_tot_debit - $level1_tot_credit;
                        $level1_tot_debit = round(( $level1_balance > 0 ? $level1_balance : 0),2);
                        $level1_tot_credit = round(($level1_balance < 0 ? (-1 * $level1_balance) : 0),2);
                    }
                    // level1
                    $pdf->SetFillColor(255,255,255);
                    $pdf->ln(9);
                    $pdf->SetFont('helvetica', 'B', 9);
                    $pdf->Cell(215,8,$level1_display_name,'B',0,'L',1);
                    // $pdf->Cell(30,8,number_format($level1_op_debit,2),'B',0,'C',1);
                    // $pdf->Cell(30,8,number_format($level1_op_credit,2),'B',0,'C',1);
                    // $pdf->Cell(30,8,number_format($level1_cur_debit,2),'B',0,'C',1);
                    // $pdf->Cell(30,8,number_format($level1_cur_credit,2),'B',0,'C',1);
                    $pdf->Cell(30,8,number_format($level1_tot_debit,2),'B',0,'C',1);
                    $pdf->Cell(30,8,number_format($level1_tot_credit,2),'B',0,'C',1);
                    $pdf->ln(10);

                }

            }
            

            $pdf->Ln(10);


            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->setXY($x,$y);
            $pdf->SetFont('helvetica', 'B', 8);

            $pdf->Ln(7);
            $pdf->Cell(218, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
            // $pdf->Cell(23, 7, number_format($total_op_debit,2), 'T,B,B', false, 'C', 0, '', 0, false, 'M', 'M');
            // $pdf->Cell(5, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
            // $pdf->Cell(23, 7, number_format($total_op_credit,2), 'T,B,B', false, 'C', 0, '', 0, false, 'M', 'M');
            // $pdf->Cell(5, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
            // $pdf->Cell(23, 7, number_format($total_cur_debit,2), 'T,B,B', false, 'C', 0, '', 0, false, 'M', 'M');
            // $pdf->Cell(5, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
            // $pdf->Cell(23, 7, number_format($total_cur_credit,2), 'T,B,B', false, 'C', 0, '', 0, false, 'M', 'M');
            // $pdf->Cell(5, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(23, 7, number_format($total_tot_debit,2), 'T,B,B', false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(5, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(23, 7, number_format($total_tot_credit,2), 'T,B,B', false, 'C', 0, '', 0, false, 'M', 'M');
        }

        // for 6 cols
        else
        {
            foreach ($arrRows as $key => $value) {
                $pdf->ln(8);
                $pdf->SetFillColor(255,255,255);
                 $pdf->SetFont('helvetica', 'B,I', 10);
                $pdf->Cell(0,8,$key,0,0,'',1);
                foreach($value as $level1_display_name => $level2) {
                     $pdf->SetFont('helvetica', '', 8);
                    if($post['display_level'] > 1) {
                        $pdf->ln(8);
                        $pdf->SetFillColor(255,255,255);
                        $pdf->Cell(0,8,$level1_display_name,0,0,'',1);
                    }
                    $level1_op_debit = 0;
                    $level1_op_credit = 0;
                    $level1_cur_debit = 0;
                    $level1_cur_credit = 0;
                    $level1_tot_debit = 0;
                    $level1_tot_credit = 0;
                    $level1_balance = 0;


                    foreach($level2 as $level2_display_name => $level3) {
                        if($post['display_level']==3) {

                            $pdf->ln(8);
                            $pdf->SetFillColor(255,255,255);

                            $pdf->Cell(10,8,'',0,0,'',1);
                            $pdf->Cell(175,8,$level2_display_name,0,0,'L',1);
                        }
                        $level2_op_debit = 0;
                        $level2_op_credit = 0;
                        $level2_cur_debit = 0;
                        $level2_cur_credit = 0;
                        $level2_tot_debit = 0;
                        $level2_tot_credit = 0;
                        $level2_balance = 0;

                        foreach($level3 as $record) {

                            if($post['display_level']==3) {
                                $pdf->ln(9);
                                $pdf->Cell(15,8,'','B',0,'',1);

                               $pdf->SetFont('helvetica', '', 8);
                                $pdf->Cell(80, 8, html_entity_decode($record['account']), 'B', false, 'L', 0, '', 1);
                                $pdf->Cell(30, 8, number_format($record['op_debit'],2), 'B', false, 'C', 0, '', 1);
                                $pdf->Cell(30, 8, number_format($record['op_credit'],2), 'B', false, 'C', 0, '', 1);
                                $pdf->Cell(30, 8, number_format($record['cur_debit'],2), 'B', false, 'C', 0, '', 1);
                                $pdf->Cell(30, 8, number_format($record['cur_credit'],2), 'B', false, 'C', 0, '', 1);
                                $pdf->Cell(30, 8, number_format($record['tot_debit'],2), 'B', false, 'C', 0, '', 1);
                                $pdf->Cell(30, 8, number_format($record['tot_credit'],2), 'B', false, 'C', 0, '', 1);

                            }

                            $level2_op_debit += $record['op_debit'];
                            $level2_op_credit += $record['op_credit'];
                            $level2_cur_debit += $record['cur_debit'];
                            $level2_cur_credit += $record['cur_credit'];
                            $level2_tot_debit += $record['tot_debit'];
                            $level2_tot_credit += $record['tot_credit'];


                            $level2_balance = $level2_tot_debit - $level2_tot_credit;
                            $level2_tot_debit = round(( $level2_balance > 0 ? $level2_balance : 0),2);
                            $level2_tot_credit = round(($level2_balance < 0 ? (-1 * $level2_balance) : 0),2);

                            $total_op_debit += $record['op_debit'];
                            $total_op_credit += $record['op_credit'];
                            $total_cur_debit += $record['cur_debit'];
                            $total_cur_credit += $record['cur_credit'];
                            $total_tot_debit += $record['tot_debit'];
                            $total_tot_credit += $record['tot_credit'];
                        }
                        // level 2

                        if($post['display_level'] > 1) {
                            $pdf->ln(9);
                            $pdf->Cell(10,8,'','B',0,'',1);
                            $pdf->Cell(85,8,$level2_display_name,'B',0,'L',1);
                            $pdf->Cell(30,8,number_format($level2_op_debit,2),'B',0,'C',1);
                            $pdf->Cell(30,8,number_format($level2_op_credit,2),'B',0,'C',1);
                            $pdf->Cell(30,8,number_format($level2_cur_debit,2),'B',0,'C',1);
                            $pdf->Cell(30,8,number_format($level2_cur_credit,2),'B',0,'C',1);
                            $pdf->Cell(30,8,number_format($level2_tot_debit,2),'B',0,'C',1);
                            $pdf->Cell(30,8,number_format($level2_tot_credit,2),'B',0,'C',1);
                            $pdf->ln(5);
                        }

                        $level1_op_credit += $level2_op_credit;
                        $level1_op_debit += $level2_op_debit;
                        $level1_cur_credit += $level2_cur_credit;
                        $level1_cur_debit += $level2_cur_debit;
                        $level1_tot_credit += $level2_tot_credit;
                        $level1_tot_debit += $level2_tot_debit;

                        $level1_balance = $level1_tot_debit - $level1_tot_credit;
                        $level1_tot_debit = round(( $level1_balance > 0 ? $level1_balance : 0),2);
                        $level1_tot_credit = round(($level1_balance < 0 ? (-1 * $level1_balance) : 0),2);
                    }
                    // level1
                    $pdf->SetFillColor(255,255,255);
                    $pdf->ln(9);
                    $pdf->SetFont('helvetica', 'B', 9);
                    $pdf->Cell(95,8,$level1_display_name,'B',0,'L',1);
                    $pdf->Cell(30,8,number_format($level1_op_debit,2),'B',0,'C',1);
                    $pdf->Cell(30,8,number_format($level1_op_credit,2),'B',0,'C',1);
                    $pdf->Cell(30,8,number_format($level1_cur_debit,2),'B',0,'C',1);
                    $pdf->Cell(30,8,number_format($level1_cur_credit,2),'B',0,'C',1);
                    $pdf->Cell(30,8,number_format($level1_tot_debit,2),'B',0,'C',1);
                    $pdf->Cell(30,8,number_format($level1_tot_credit,2),'B',0,'C',1);
                    $pdf->ln(10);

                }

            }
            

            $pdf->Ln(10);


            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->setXY($x,$y);
            $pdf->SetFont('helvetica', 'B', 8);

            $pdf->Ln(7);
            $pdf->Cell(111, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(23, 7, number_format($total_op_debit,2), 'T,B,B', false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(5, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(23, 7, number_format($total_op_credit,2), 'T,B,B', false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(5, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(23, 7, number_format($total_cur_debit,2), 'T,B,B', false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(5, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(23, 7, number_format($total_cur_credit,2), 'T,B,B', false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(5, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(23, 7, number_format($total_tot_debit,2), 'T,B,B', false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(5, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(23, 7, number_format($total_tot_credit,2), 'T,B,B', false, 'C', 0, '', 0, false, 'M', 'M');
        }

        //Close and output PDF document
        $pdf->Output('Trial Balance:'.date('YmdHis').'.pdf', 'I');

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
            $this->Image($image_file, 10, 10, 30, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        // Set font
        $this->SetFont('times', 'B,I', 20);
        $this->Ln(5);
        // Title
        $this->Cell(0, 10, $this->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);
        $this->Cell(0, 10, $this->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->SetFont('times', 'B,I', 10);
        $this->Ln(10);
        $this->Cell(0, 10, 'From Date : '.$this->data['from_date'].'     To Date  :  '.$this->data['to_date'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(12);

        $this->SetFont('helvetica', 'B', 8);
        if($this->data['cols'] == 2)
        {
            $this->Cell(10, 7, '', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Cell(205, 7, 'Description', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Cell(30, 7, 'Closing Debit', 'T,B', false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(30, 7, 'Closing Credit', 'T,B', false, 'C', 0, '', 0, false, 'M', 'M');    
        }
        else
        {
            // echo 'hello';
            // print_r($this->data);
            // exit;
            $this->Cell(95, 7, 'Description', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Cell(30, 7, 'Opening Debit', 'T,B', false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(30, 7, 'Opening Credit', 'T,B', false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(30, 7, 'Current Debit', 'T,B', false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(30, 7, 'Current Credit', 'T,B', false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(30, 7, 'Closing Debit', 'T,B', false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(30, 7, 'Closing Credit', 'T,B', false, 'C', 0, '', 0, false, 'M', 'M');
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