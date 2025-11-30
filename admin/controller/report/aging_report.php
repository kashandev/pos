<?php

class ControllerReportAgingReport extends HController{
    protected function getAlias() {
        return 'report/aging_report';
    }

    protected function getDefaultOrder() {
        return 'quotation_id';
    }

    protected function getList() {
        parent::getList();

        $this->data['partner_types'] = $this->session->data['partner_types'];

        $this->model['supplier'] = $this->load->model('setup/supplier');
        $this->data['suppliers'] = $this->model['supplier']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['product'] = $this->load->model('inventory/product');
        $this->data['products'] = $this->model['product']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);

        $this->data['href_print_report'] = $this->url->link($this->getAlias() .'/printReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_print_excel'] = $this->url->link($this->getAlias() .'/printReportExcel', 'token=' . $this->session->data['token'], 'SSL');

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

//        //        $this->document->addScript("view/javascript/jquery/jquery.dataTables.min.js");
//        $this->document->addLink("view/stylesheet/dataTables.css", "stylesheet");
//        $this->document->addScript("view/js/plugins/dataTables/js/jquery.dataTables.js");
//        $this->document->addScript("view/js/plugins/dataTables/js/jquery.dataTables.columnFilter.js");
////        $this->document->addLink("view/js/plugins/dataTables/css/jquery.dataTables.css", "stylesheet");
//
//
        $this->response->setOutput($this->render());
    }

    public function printReportExcel()
    {
        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        $this->model['aging_report'] = $this->load->model('report/aging_report');
        $this->model['partner_type'] = $this->load->model('common/partner_type');
        $this->model['partner'] = $this->load->model('common/partner');

        //$this->model['trial_balance'] = $this->load->model('report/trial_balance');
        $post = $this->request->post;
        $session = $this->session->data;

        $date_s = MySqlDate($post['date_to']);
        $partner_id = $post['partner_id'];

        $sort_order = "ASC";
        $filter = array(
            'partner_id' => $post['partner_id'],
            'partner_type_id' => $post['partner_type_id'],
            'date_to' => $date_s,
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
        );

        $arrPartner = $this->model['partner']->getArrays('partner_id','name');
        // $grouping = $this->model['goods_received_report']->getTotalGoodsReceived();
        $this->model['company'] = $this->load->model('setup/company');
        $this->model['company_branch'] = $this->load->model('setup/company_branch');

        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        $rows = $this->model['aging_report']->getAging($filter);

        // d($rows,true);
        $branch = $this->model['company_branch']->getRow(array('company_branch_id' => $session['company_branch_id']));

        $arrRows = array();

//        echo '<pre>';
//        print_r($rows);
//        print_r($arrPartner);
//        exit;
        $final_company_name = array();
        foreach($rows as $group) {
            $final_company_name[] = $group_name = $arrPartner[$group['partner_id']];
            $arrRows[$group_name][] = array(
                'document_date' => $group['document_date'],
                'document_identity' => $group['ref_document_identity'],
                'document_amount' => $group['document_amount'],
                '30_days' => $group['30_days'],
                '60_days' => $group['60_days'],
                '90_days' => $group['90_days'],
                'above_90' => $group['above_90'],
            );
        }

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);

        $objPHPExcel->getProperties()
            ->setCreator("Farrukh Afaq")
            ->setLastModifiedBy("Hira")
            ->setTitle("Agining Report");

        $objPHPExcel->data = array(
            'company_name' => $session['company_name'],
            'report_name' => 'Agining Report',
            'date_to' => $date_s
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
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Aging Report');
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
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, '');
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->getFill('ebebeb');
        $rowCount ++;

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':'.'G'.$rowCount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Sr.')->getStyle('A'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Doc. Date')->getStyle('A'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'Doc. Identity')->getStyle('B'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Document Amount')->getStyle('C'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, '0-30 Days')->getStyle('D'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, '31-60 Days')->getStyle('E'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, '61-90 Days')->getStyle('F'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, 'Above 90 Days')->getStyle('G'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':G'.$rowCount)->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'ebebeb')
                )
            )
        );
        $rowCount++;

        $sr =0;
        foreach($arrRows as $detail) {
            $total_amount = 0;
            $total_amount_30_days = 0;
            $total_amount_60_days = 0;
            $total_amount_90_days = 0;
            $total_amount_above_90 = 0;
            $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":G".($rowCount));
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $final_company_name[$sr]);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->getFill('ebebeb');
            $rowCount ++;



            foreach ($detail as $del)
            {

                $rowCount++;
                $sr++;
                $total_amount += $del['document_amount'];
                $total_amount_30_days += $del['30_days'];
                $total_amount_60_days += $del['60_days'];
                $total_amount_90_days += $del['90_days'];
                $total_amount_above_90 += $del['above_90'];
//                echo '<pre>';
//                print_r($detail);

                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, stdDate($detail['document_date']));
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $del['document_identity']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $del['document_amount']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $del['30_days']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $del['60_days']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $del['90_days']);
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $del['above_90']);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':G'.$rowCount)->applyFromArray(
                    array(
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        )
                    )
                );
            }

            $rowCount++;
            //$objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, number_format($total_quantity))->getStyle('G'.$rowCount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, number_format($total_amount))->getStyle('C'.$rowCount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, number_format($total_amount_30_days))->getStyle('D'.$rowCount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, number_format($total_amount_60_days))->getStyle('E'.$rowCount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, number_format($total_amount_90_days))->getStyle('F'.$rowCount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, number_format($total_amount_above_90))->getStyle('G'.$rowCount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':G'.$rowCount)->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'ebebeb')
                    )
                )
            );

            $rowCount+=3;

        }
//        exit;



        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Aging Report.xlsx"');
        header('Cache-Control: max-age=0');
        //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        //$objWriter->save('some_excel_file.xlsx');
        $objWriter->save('php://output');
        exit;


    }

    public function printReport() {

        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        $this->model['aging_report'] = $this->load->model('report/aging_report');
        $this->model['partner_type'] = $this->load->model('common/partner_type');
        $this->model['partner'] = $this->load->model('common/partner');

        //$this->model['trial_balance'] = $this->load->model('report/trial_balance');
        $post = $this->request->post;
        // d($post,true);
        $session = $this->session->data;

        $date_s = MySqlDate($post['date_to']);
        $partner_id = $post['partner_id'];


        $sort_order = "ASC";
        $filter = array(
            'partner_id' => $post['partner_id'],
            'date_to' => $date_s,
            'partner_type_id' => $post['partner_type_id'],
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
        );

        $arrPartner = $this->model['partner']->getArrays('partner_id','name');
        // $grouping = $this->model['goods_received_report']->getTotalGoodsReceived();
        $this->model['company'] = $this->load->model('setup/company');
        $this->model['company_branch'] = $this->load->model('setup/company_branch');

        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        $rows = $this->model['aging_report']->getAging($filter);

       // d($rows,true);
        $branch = $this->model['company_branch']->getRow(array('company_branch_id' => $session['company_branch_id']));

        $arrRows = array();

        foreach($rows as $group) {
                // d($group,true);
                $group_name = $arrPartner[$group['partner_id']];
                if($group['ref_document_type_id'] == 3 || $group['ref_document_type_id'] == 23)
                {
                    $arrRows[$group_name][] = array(
                    'document_date' => $group['document_date'],
                    'document_identity' => $group['ref_document_identity'],
                    'ref_document_type_id' => $group['ref_document_type_id'],
                    'document_amount' => ((-1) * $group['document_amount']),
                    '30_days' => ((-1) * $group['30_days']),
                    '60_days' => ((-1) * $group['60_days']),
                    '90_days' => ((-1) * $group['90_days']),
                    'above_90' => ((-1) * $group['above_90']), 
                    );   
                }
                else
                {
                    $arrRows[$group_name][] = array(
                    'document_date' => $group['document_date'],
                    'document_identity' => $group['ref_document_identity'],
                    'ref_document_type_id' => $group['ref_document_type_id'],
                    'document_amount' => $group['document_amount'],
                    '30_days' => $group['30_days'],
                    '60_days' => $group['60_days'],
                    '90_days' => $group['90_days'],
                    'above_90' => $group['above_90'], 
                    );  
                }
                
            
        }


        $pdf = new PDF('p', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Fahad Siddiqui');
        $pdf->SetTitle('Aging Report');
        $pdf->SetSubject('Aging Report');

        $date_from = $post['date_from'];
        $date_to = $post['date_to'];

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'company_address' => $branch['address'],
            'company_phone' => $branch['phone_no'],
            'report_name' => 'Aging Report',
            'date_to' => $date_to,
            'company_logo' => $session['company_image'],
            'supplier_name'=>$post['supplier_name'],

        );


        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(15, 55, 5);
        $pdf->SetHeaderMargin(2);
        $pdf->SetFooterMargin(2);

        $pdf->SetFont('helvetica', 'B', 10);


        $sr = 0;
        $pdf->Ln(0);


        // add a page
        $pdf->AddPage();
//        d($arrRows,true);
        foreach($arrRows as $group_name => $rows)
        {
            $total_current = 0;
            $total_30 = 0;
            $total_60 = 0;
            $total_90 = 0;
            $total_Above90 = 0;

            $pdf->SetFont('helvetica', 'B,U', 11);
            $pdf->ln(8);
            $pdf->Cell(100, 10,'' .$group_name, 0, false, 'L', 0, '', 1);

            $pdf->ln(2);

            foreach($rows as $row) {

                $pdf->SetFont('helvetica', '', 8);
                $pdf->ln(7);

                $pdf->Cell(25, 7,stdDate($row['document_date']), 0, false, 'L', 0, '', 1);
                $pdf->Cell(30, 7, html_entity_decode($row['document_identity']), 0, false, 'L', 0, '', 1);
                $pdf->Cell(25, 7, number_format($row['document_amount'],2), 0, false, 'R', 0, '', 1);
                $pdf->Cell(25, 7, number_format($row['30_days'],2), 0, false, 'R', 0, '', 1);
                $pdf->Cell(25, 7, number_format($row['60_days'],2), 0, false, 'R', 0, '', 1);
                $pdf->Cell(25, 7, number_format($row['90_days'],2), 0, false, 'R', 0, '', 1);
                $pdf->Cell(25, 7, number_format($row['above_90'],2), 0, false, 'R', 0, '', 1);


                $total_current += $row['document_amount'];
                $total_30 += $row['30_days'];
                $total_60 += $row['60_days'];
                $total_90 += $row['90_days'];
                $total_Above90 += $row['above_90'];

            }

            $pdf->SetFont('helvetica', 'B', 8);

            $pdf->Ln(12);
            $pdf->Cell(55, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(25, 7, number_format($total_current,2), 'T,B,B', false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(2, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(23, 7, number_format($total_30,2), 'T,B,B', false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(2, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(23, 7, number_format($total_60,2), 'T,B,B', false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(2, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(23, 7, number_format($total_90,2), 'T,B,B', false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(2, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(23, 7, number_format($total_Above90,2), 'T,B,B', false, 'R', 0, '', 0, false, 'M', 'M');

        }


        $pdf->Ln(4);

        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->setXY($x,$y);
        //Close and output PDF document
        $pdf->Output('Aging Report :'.date('YmdHis').'.pdf', 'I');

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
        $this->SetFont('times', 'B', 20);
        $this->Ln(5);
        // Title
        $this->Cell(0, 10, $this->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);
        $this->Cell(0, 10, $this->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->SetFont('times', 'B', 10);
        $this->Ln(10);
        $this->Cell(0, 10, 'To Date  :  '.$this->data['date_to'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(12);



        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(25, 7, 'Document Date', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(30, 7, 'Document Identity', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(25, 7, 'Document Amount', 'T,B', false, 'R', 0, '', 0, false, 'M', 'M');
        $this->Cell(25, 7, '0 - 30 Days', 'T,B', false, 'R', 0, '', 0, false, 'M', 'M');
        $this->Cell(25, 7, '31 - 60 Days', 'T,B', false, 'R', 0, '', 0, false, 'M', 'M');
        $this->Cell(25, 7, '61 - 90 Days', 'T,B', false, 'R', 0, '', 0, false, 'M', 'M');
        $this->Cell(25, 7, 'Above 90 Days', 'T,B', false, 'R', 0, '', 0, false, 'M', 'M');


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

