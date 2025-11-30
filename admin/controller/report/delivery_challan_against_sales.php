<?php

class ControllerReportDeliveryChallanAgainstSales extends HController {

    protected function getAlias() {
        return 'report/delivery_challan_against_sales';
    }
    
    protected function getDefaultOrder() {
        return 'delivery_challan_id';
    }

    protected function getDefaultSort() {
        return 'DESC';
    }

    protected function getList() {
        parent::getList();

        $this->data['partner_types'] = $this->session->data['partner_types'];

        $this->data['partner_type_id'] = 2;
        $this->model['customer'] = $this->load->model('setup/customer');
        $this->data['customers'] = $this->model['customer']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['product'] = $this->load->model('inventory/product');
        $this->data['products'] = $this->model['product']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
        $this->data['href_print_report'] = $this->url->link($this->getAlias() .'/printReport', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['href_export_excel_report'] = $this->url->link($this->getAlias() .'/ExportExcelReport', 'token=' . $this->session->data['token'], 'SSL');
        

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


    public function ExportExcelReport()
    {
        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        $this->model['delivery_challan_against_sales'] = $this->load->model('report/delivery_challan_against_sales');

        $post = $this->request->post;
        $session = $this->session->data;
        $date_f = MySqlDate($post['date_from']);
        $date_s = MySqlDate($post['date_to']);
        $partner_id = $post['partner_id'];
        $product_id = $post['product_id'];

        $this->model['company'] = $this->load->model('setup/company');
        $this->model['company_branch'] = $this->load->model('setup/company_branch');


            $filter= array(
                'company_id' => $this->session->data['company_id'],
                'company_branch_id' => $this->session->data['company_branch_id'],
                'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                'from_date' => MySqlDate($post['date_from']),
                'to_date' =>  MySqlDate($post['date_to']),
                'partner_id' => $post['partner_id']
        );

//        d($filter,true);
        if($post['challan_type'] == 'GST' && $post['group_by'] == 'with_invoice')
        {
            $rows = $this->model['delivery_challan_against_sales']->getGstWithInvoice($filter);
        }
        if($post['challan_type'] == 'GST' && $post['group_by'] == 'without_invoice')
        {
            $rows = $this->model['delivery_challan_against_sales']->getGstWithOutInvoice($filter);

        }
        if($post['challan_type'] == 'Non GST' && $post['group_by'] == 'with_invoice')
        {
            $rows = $this->model['delivery_challan_against_sales']->getNonGstWithInvoice($filter);

        }
        if($post['challan_type'] == 'Non GST' && $post['group_by'] == 'without_invoice')
        {
            $rows = $this->model['delivery_challan_against_sales']->getNonGstWithOutInvoice($filter);

        }

        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        $branch = $this->model['company_branch']->getRow(array('company_branch_id' => $session['company_branch_id']));

        $arrRows = array();

        foreach($rows as $group) {
            $group_name = $group['name'];
            $arrRows[$group_name][] = array(
                'document_date' => $group['document_date'],
                'document_identity' => $group['document_identity'],
                'ref_document_date' => $group['sale_date'],
                'ref_document_identity' => $group['sale_no'],
                'po_no' => $group['po_no'],
                'po_date' => $group['po_date'],
            );
        }

        // d($arrRows,true);

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);

        $rowCount = 1;


        $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowCount.':F'.$rowCount);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->getAlignment()->applyFromArray(
            array('font' => array(
                'bold' => true,
                'size' => 14,
            ),'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
        );
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount,'Delivery Challan Against Sales')->getStyle('A'.$rowCount)->getFont()->setBold( true )->setSize(14);
        $rowCount += 2;

        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Document Date')->getStyle('A'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'Document Identity')->getStyle('B'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'PO Date')->getStyle('C'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'PO NO.')->getStyle('D'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'Invoice Date')->getStyle('E'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'Invoice No.')->getStyle('F'.$rowCount)->getFont()->setBold( true );

        $rowCount+=2;
        $sr = 1;

        $sr=1;

        foreach($arrRows as $key => $value) {
            foreach ($value as $key_1 => $value_1) {
                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $value_1['document_date']);
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount,$value_1['document_identity']);
           $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount,$value_1['po_date']);
           $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount,$value_1['po_no']);
           $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount,$value_1['ref_document_date']);
           $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount,$value_1['ref_document_identity']);
           
            $rowCount++;
            $sr++;
            }
            
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="delivery_challan_against_sales.xlsx"');
        header('Cache-Control: max-age=0');
        //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        //$objWriter->save('some_excel_file.xlsx');
        $objWriter->save('php://output');



    }




    public function printReport() {

        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        $this->model['delivery_challan_against_sales'] = $this->load->model('report/delivery_challan_against_sales');

        $post = $this->request->post;
        $session = $this->session->data;
//        echo '<pre>';
//        print_r($post);
//        exit;

//        d($post,true);
        $date_f = MySqlDate($post['date_from']);
        $date_s = MySqlDate($post['date_to']);
        $partner_id = $post['partner_id'];
        $product_id = $post['product_id'];

        $this->model['company'] = $this->load->model('setup/company');
        $this->model['company_branch'] = $this->load->model('setup/company_branch');


            $filter= array(
                'company_id' => $this->session->data['company_id'],
                'company_branch_id' => $this->session->data['company_branch_id'],
                'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                'from_date' => MySqlDate($post['date_from']),
                'to_date' =>  MySqlDate($post['date_to']),
                'partner_id' => $post['partner_id']
        );

//        d($filter,true);
        if($post['challan_type'] == 'GST' && $post['group_by'] == 'with_invoice')
        {
            $rows = $this->model['delivery_challan_against_sales']->getGstWithInvoice($filter);
//            print_r($rows);
//            exit;

        }
        if($post['challan_type'] == 'GST' && $post['group_by'] == 'without_invoice')
        {
            $rows = $this->model['delivery_challan_against_sales']->getGstWithOutInvoice($filter);
//            print_r($rows);
//            exit;

        }
        if($post['challan_type'] == 'Non GST' && $post['group_by'] == 'with_invoice')
        {
            $rows = $this->model['delivery_challan_against_sales']->getNonGstWithInvoice($filter);
//            print_r($rows);
//            exit;
        }
        if($post['challan_type'] == 'Non GST' && $post['group_by'] == 'without_invoice')
        {
            $rows = $this->model['delivery_challan_against_sales']->getNonGstWithOutInvoice($filter);
//            print_r($rows);
//            exit;
        }

        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        $branch = $this->model['company_branch']->getRow(array('company_branch_id' => $session['company_branch_id']));

        $arrRows = array();

        foreach($rows as $group) {


            $group_name = $group['name'];

            $arrRows[$group_name][] = array(

                'document_date' => $group['document_date'],
                'document_identity' => $group['document_identity'],
                'ref_document_date' => $group['sale_date'],
                'ref_document_identity' => $group['sale_no'],
                'po_no' => $group['po_no'],
                'po_date' => $group['po_date'],
            );
        }

//        echo '<pre>';
//        print_r($arrRows);
//        exit;

        $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Fahad Siddiqui');
        $pdf->SetTitle('Delivery Challan Against Sales');
        $pdf->SetSubject('Delivery Challan Against Sales');

        $date_from = $post['date_from'];
        $date_to = $post['date_to'];

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'company_address' => $branch['address'],
            'company_phone' => $branch['phone_no'],
            'report_name' => 'Delivery Challan Against Sales Report',
            'date_from' => $date_from,
            'date_to' => $date_to,
            'company_logo' => $session['company_image'],
        );


        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(15, 45, 5);
        $pdf->SetHeaderMargin(2);
        $pdf->SetFooterMargin(2);

        // set auto page breaks
        //$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set font
        $pdf->SetFont('helvetica', 'B', 10);


        $sr = 0;
        $pdf->Ln(0);

        // add a page
        $pdf->AddPage();
//        d($arrRows,true);

        foreach($arrRows as $group_name => $rows)
        {
            $pdf->SetFont('helvetica', 'B,U', 10);

            $pdf->ln(1);
            $pdf->Cell(150, 7,'' .html_entity_decode($group_name), 0, false, 'L', 0, '', 1);
//            $pdf->ln(10);

            foreach($rows as $delivery_challans) {

                $pdf->SetFont('helvetica', '', 8);

                $pdf->ln(6);

                $pdf->Cell(30, 5,stdDate($delivery_challans['document_date']), 0, false, 'L', 0, '', 1);
                $pdf->Cell(30, 5, html_entity_decode($delivery_challans['document_identity']), 0, false, 'L', 0, '', 1);
                $pdf->Cell(30, 5,stdDate($delivery_challans['po_date']), 0, false, 'L', 0, '', 1);
                $pdf->Cell(40, 5, html_entity_decode($delivery_challans['po_no']), 0, false, 'L', 0, '', 1);
                $pdf->Cell(30, 5,stdDate($delivery_challans['ref_document_date']), 0, false, 'L', 0, '', 1);
                $pdf->Cell(30, 5, html_entity_decode($delivery_challans['ref_document_identity']), 0, false, 'L', 0, '', 1);

            }
            $pdf->ln(6);
        }
//        $pdf->Ln(4);

        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->setXY($x,$y);
        $pdf->SetFont('helvetica', 'B', 7);

        $pdf->Ln(7);
        $pdf->Output('Delivery Challan Against Sales:'.date('YmdHis').'.pdf', 'I');

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


        $this->SetFont('helvetica', 'B', 9);
        $this->Cell(30, 5, 'Document Date', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(30, 5, 'Document Identity', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(30, 5, 'Po Date', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(40, 5, 'Po No', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(30, 5, 'Invoice Date', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(30, 5, 'Invoice No', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
        $this->ln(6);

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