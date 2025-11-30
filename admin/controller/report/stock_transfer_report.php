<?php

class ControllerReportStockTransferReport extends HController{
    protected function getAlias() {
        return 'report/stock_transfer_report';
    }

    protected function getDefaultOrder() {
        return 'stock_transfer_id';
    }

    protected function getList() {
        parent::getList();

        $this->data['partner_types'] = $this->session->data['partner_types'];

        //$this->model['supplier'] = $this->load->model('setup/supplier');
        //$this->data['suppliers'] = $this->model['supplier']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['product'] = $this->load->model('inventory/product');
        $this->data['products'] = $this->model['product']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $this->data['company_branchs'] = $this->model['company_branch']->getRows(array('company_id' => $this->session->data['company_id']));



        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
        $this->data['href_print_report'] = $this->url->link($this->getAlias() .'/printReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_print_excel_report'] = $this->url->link($this->getAlias() .'/printExcelReport', 'token=' . $this->session->data['token'], 'SSL');
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

    public function printExcelReport()
    {
        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        $this->model['stock_transfer_report'] = $this->load->model('report/stock_transfer_report');
        $this->model['stock_transfer_detail'] = $this->load->model('inventory/stock_transfer_detail');
        $this->model['partner_type'] = $this->load->model('common/partner_type');
        $this->model['partner'] = $this->load->model('common/partner');



        //$this->model['trial_balance'] = $this->load->model('report/trial_balance');
        $post = $this->request->post;
        $session = $this->session->data;

        $date_f = MySqlDate($post['date_from']);
        $date_s = MySqlDate($post['date_to']);
        $partner_id = $post['partner_id'];
        $product_id = $post['product_id'];

        $sort_order = "ASC";

        $where =  "document_date >='$date_f'";
        $where .= " AND document_date <='$date_s'";
        $where .= " AND company_id ='".$session['company_id']."'";
        $where .= " AND company_branch_id ='".$session['company_branch_id']."'";

        if($product_id != ''){
            $where .= " AND `product_id`='$product_id'";
        }
        else{
            $where .= "";
        }

               $this->model['company'] = $this->load->model('setup/company');
               $this->model['company_branch'] = $this->load->model('setup/company_branch');

                $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
                $rows = $this->model['stock_transfer_detail']->getRows($where,array('document_date asc,document_identity'));


        $arrRows = array();

        foreach($rows as $group) {
            $row['document_date'] = stdDate($group['document_date']);

            if($this->request->post['group_by'] == 'warehouse'){
                $group_name = $group['warehouse'];
            }
            elseif($this->request->post['group_by'] == 'product') {
                $group_name = $group['product_name'];
            }
            elseif($this->request->post['group_by'] == 'document_date') {
                $group_name = stdDate($group['document_date']);
            }
            else {
                $group_name = '';
            }

            $groupBy = $this->request->post['group_by'];

            $arrRows[$group_name][] = array(

                'document_date' => $group['document_date'],
                'document_identity' => $group['document_identity'],
                'warehouse_name' => $group['warehouse'],
                'product_name' => $group['product_name'],
                'partner_name' => $group['partner_name'],
                'unit_name' => $group['unit'],
                'qty' => $group['qty'],
                'rate' => ($group['qty']==0?0:($group['amount']/$group['qty'])),
                'amount' => $group['amount'],
            'to_company_branch_id'=> $group['to_company_branch_id']
            );
        }

        // d($arrRows,true);

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);

        $rowCount = 1;


        $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowCount.':H'.$rowCount);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->getAlignment()->applyFromArray(
            array('font' => array(
                'bold' => true,
                'size' => 14,
            ),'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
        );
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount,'Stock Transfer Report')->getStyle('A'.$rowCount)->getFont()->setBold( true )->setSize(14);
        $rowCount += 2;

        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Document Date')->getStyle('A'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'Document Identity')->getStyle('B'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Branch Name')->getStyle('C'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'Product')->getStyle('D'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'Unit')->getStyle('E'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'Quantity')->getStyle('F'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, 'Rate')->getStyle('G'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, 'Amount')->getStyle('H'.$rowCount)->getFont()->setBold( true );

        $rowCount+=2;
        

        foreach($arrRows as $key => $value) {
            foreach ($value as $key_1 => $value_1) {
                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $value_1['document_date']);
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount,$value_1['document_identity']);
           $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount,$value_1['to_company_branch_id']);
           $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount,$value_1['product_name']);
           $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount,$value_1['unit_name']);
           $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount,$value_1['qty']);
           $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount,$value_1['rate']);
           $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount,$value_1['amount']);
            $rowCount++;
            $sr++;
                
            }
            
        }

//        $rowCount += 5;
//        $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowCount.':D'.$rowCount);
//        $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount,'Received the above goods in goods order and conditions');


        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="stock_transfer_report.xlsx"');
        header('Cache-Control: max-age=0');
        //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        //$objWriter->save('some_excel_file.xlsx');
        $objWriter->save('php://output');






    }

    public function printReport() {

        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        $this->model['stock_transfer_report'] = $this->load->model('report/stock_transfer_report');
        $this->model['stock_transfer_detail'] = $this->load->model('inventory/stock_transfer_detail');
        $this->model['partner_type'] = $this->load->model('common/partner_type');
        $this->model['partner'] = $this->load->model('common/partner');



        //$this->model['trial_balance'] = $this->load->model('report/trial_balance');
        $post = $this->request->post;
        $session = $this->session->data;

        $date_f = MySqlDate($post['date_from']);
        $date_s = MySqlDate($post['date_to']);
        $partner_id = $post['partner_id'];
        $product_id = $post['product_id'];

        $sort_order = "ASC";

        $where =  "document_date >='$date_f'";
        $where .= " AND document_date <='$date_s'";
        $where .= " AND company_id ='".$session['company_id']."'";
        $where .= " AND company_branch_id ='".$session['company_branch_id']."'";

        if($product_id != ''){
            $where .= " AND `product_id`='$product_id'";
        }
        else{
            $where .= "";
        }

               $this->model['company'] = $this->load->model('setup/company');
               $this->model['company_branch'] = $this->load->model('setup/company_branch');

                $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
                $rows = $this->model['stock_transfer_detail']->getRows($where,array('document_date asc,document_identity'));


        $arrRows = array();

        foreach($rows as $group) {
            $row['document_date'] = stdDate($group['document_date']);

            if($this->request->post['group_by'] == 'warehouse'){
                $group_name = $group['warehouse'];
            }
            elseif($this->request->post['group_by'] == 'product') {
                $group_name = $group['product_name'];
            }
            elseif($this->request->post['group_by'] == 'document_date') {
                $group_name = stdDate($group['document_date']);
            }
            else {
                $group_name = '';
            }

            $groupBy = $this->request->post['group_by'];

            $arrRows[$group_name][] = array(

                'document_date' => $group['document_date'],

                'document_identity' => $group['document_identity'],
                'warehouse_name' => $group['warehouse'],
                'product_name' => $group['product_name'],
                'partner_name' => $group['partner_name'],
                'unit_name' => $group['unit'],
                'qty' => $group['qty'],
                'rate' => ($group['qty']==0?0:($group['amount']/$group['qty'])),
                'amount' => $group['amount'],
            'to_company_branch_id'=> $group['to_company_branch_id']
            );
        }
        $pdf = new PDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Fahad Siddiqui');
        $pdf->SetTitle('Stock Transfer Report');
        $pdf->SetSubject('Stock Transfer Report');
        $date_from = $post['date_from'];
        $date_to = $post['date_to'];

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => 'Stock Transfer Report',
            'date_from' => $date_from,
            'date_to' => $date_to,
            'company_logo' => $session['company_image'],
            'supplier_name'=>$post['supplier_name'],
        );
        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(15, 47, 5);
        $pdf->SetHeaderMargin(2);
        $pdf->SetFooterMargin(2);
        // set auto page breaks
        //$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // set font
        $pdf->SetFont('helvetica', 'B', 10);
        $sr = 0;
        $pdf->Ln(0);
        $total_quantity = 0;
        $total_amount = 0;
        // add a page
        $pdf->AddPage();
//        d($arrRows,true);
        foreach($arrRows as $group_name => $rows)
        {
            $pdf->SetFont('helvetica', 'B,U', 9);
            $pdf->Cell(150, 8,'Group Name :   ' .html_entity_decode($group_name), 0, false, 'L', 0, '', 1);
            $pdf->ln(2);
            foreach($rows as $stock_transfers) {
                $branch = $this->model['company_branch']->getRow(array('company_branch_id' => $stock_transfers['to_company_branch_id']));
                $pdf->SetFont('helvetica', '', 8);
                $pdf->ln(6);
                $pdf->Cell(25, 5,stdDate($stock_transfers['document_date']), 0, false, 'L', 0, '', 1);
                $pdf->Cell(35, 5, html_entity_decode($stock_transfers['document_identity']), 0, false, 'L', 0, '', 1);
                $pdf->Cell(30, 5, html_entity_decode($branch['name']), 0, false, 'L', 0, '', 1);
                $pdf->Cell(80, 5, $stock_transfers['product_name'], 0, false, 'L', 0, '', 1);
                $pdf->Cell(20, 5, $stock_transfers['unit_name'], 0, false, 'L', 0, '', 1);
                $pdf->Cell(20, 5, number_format($stock_transfers['qty'],2), 0, false, 'R', 0, '', 1);
                $pdf->Cell(20, 5, number_format($stock_transfers['rate'],2), 0, false, 'R', 0, '', 1);
                $pdf->Cell(25, 5, number_format($stock_transfers['amount'],2), 0, false, 'R', 0, '', 1);
                $total_quantity += $stock_transfers['qty'];
                $total_amount += $stock_transfers['amount'];
                //$total_cur_debit += $LedgerDetail['cur_debit'];
                //$total_cur_credit += $LedgerDetail['cur_credit'];
                //$total_tot_debit += $LedgerDetail['tot_debit'];
                //$total_tot_credit += $LedgerDetail['tot_credit'];
            }
            $pdf->ln(8);
        }
        $pdf->Ln(4);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->setXY($x,$y);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Ln(7);
        $pdf->Cell(190, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        // $pdf->Cell(23, 7, number_format($total_op_debit,2), 'T,B,B', false, 'R', 0, '', 0, false, 'M', 'M');
        //$pdf->Cell(5, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        //$pdf->Cell(23, 7, number_format($total_op_credit,2), 'T,B,B', false, 'R', 0, '', 0, false, 'M', 'M');
        //$pdf->Cell(5, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        //$pdf->Cell(23, 7, number_format($total_cur_debit,2), 'T,B,B', false, 'R', 0, '', 0, false, 'M', 'M');
        //$pdf->Cell(5, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        //$pdf->Cell(23, 7, number_format($total_cur_credit,2), 'T,B,B', false, 'R', 0, '', 0, false, 'M', 'M');

        $pdf->Cell(10, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(23, 7, number_format($total_quantity,2), 'T,B,B', false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(23, 7, number_format($total_amount,2), 'T,B,B', false, 'R', 0, '', 0, false, 'M', 'M');
        //Close and output PDF document
        $pdf->Output('Stock Transfer Report :'.date('YmdHis').'.pdf', 'I');
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
        $this->Cell(0, 10, 'From Date : '.$this->data['date_from'].'     To Date  :  '.$this->data['date_to'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(12);
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(25, 5, 'Document Date', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(35, 5, 'Document Identity', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(30, 5, 'Branch Name', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(80, 5, 'Product', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(20, 5, 'Unit', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(20, 5, 'Quantity', 'T,B', false, 'R', 0, '', 0, false, 'M', 'M');
        $this->Cell(20, 5, 'Rate', 'T,B', false, 'R', 0, '', 0, false, 'M', 'M');
        $this->Cell(25, 5, 'Amount', 'T,B', false, 'R', 0, '', 0, false, 'M', 'M');
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

