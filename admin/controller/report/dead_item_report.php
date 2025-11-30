<?php

class ControllerReportDeadItemReport extends HController {

    protected function getAlias() {
        return 'report/dead_item_report';
    }

    protected function init() {
        $this->model[$this->getAlias()] = $this->load->model('inventory/opening_stock_detail');
        $this->data['lang'] = $this->load->language($this->getAlias());
        $this->document->setTitle($this->data['lang']['heading_title']);
        $this->data['token'] = $this->session->data['token'];
    }

    protected function getList() {
        parent::getList();

        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->data['warehouses'] = $this->model['warehouse']->getRows(array('company_id' => $this->session->data['company_id'], 'company_branch_id' => $this->session->data['company_branch_id']));

        $this->model['product_category'] = $this->load->model('inventory/product_category');
        $this->data['product_categories'] = $this->model['product_category']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['product'] = $this->load->model('inventory/product');
        $this->data['products'] = $this->model['product']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->data['href_get_detail_report'] = $this->url->link($this->getAlias() .'/getDetailReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_get_summary_report'] = $this->url->link($this->getAlias() .'/getSummaryReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_print_detail_report'] = $this->url->link($this->getAlias() .'/printDetailReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_excel_report'] = $this->url->link($this->getAlias() .'/getExcelReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_print_summary_report'] = $this->url->link($this->getAlias() .'/printSummaryReport', 'token=' . $this->session->data['token'], 'SSL');

        $this->template = $this->getAlias() . '.tpl';
        $this->response->setOutput($this->render());
    }

    public function getDetailReport() {
        $post = $this->request->post;
        //d($post, true);
        $filter = array();
        $filter['company_id'] = $this->session->data['company_id'];
        $filter['company_branch_id'] = $this->session->data['company_branch_id'];
        $filter['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
        $filter['month']=$post['month'];
        $this->model['sale_invoice_detail'] = $this->load->model('inventory/sale_invoice_detail');
        $rows = $this->model['sale_invoice_detail']->get_dead_items($filter);

        $html = '';
      //  d($rows,true);
        foreach($rows as $row) {
            $html .= '<tr>';
            $html .= '<td>'.$row['product_code'].'</td>';
            $html .= '<td>'.$row['name'].'</td>';
            $html .= '<td>'.$row['description'].'</td>';
            $html .= '<td>'.$row['product_category'].'</td>';
            $html .= '<td>'.$row['product_sub_category'].'</td>';
            $html .= '<td>'.$row['unit'].'</td>';
            $html .= '</tr>';
        }
        $json = array(
            'success' => true,
            'html' => $html,
            'opening_stocks' => $rows
        );
        echo json_encode($json);
        exit;
    }


    public  function getExcelReport(){


        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);

        $rowCount = 1;


        $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowCount.':G'.$rowCount);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->getAlignment()->applyFromArray(
            array('font' => array(
                'bold' => true,
                'size' => 14,
            ),'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
        );
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount,'Slow Moving Stock')->getStyle('A'.$rowCount)->getFont()->setBold( true )->setSize(14);
        $rowCount += 2;

        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Sr.')->getStyle('A'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'product_code')->getStyle('B'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Name')->getStyle('B'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'description')->getStyle('B'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'product_category')->getStyle('B'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'product_Sub_category')->getStyle('C'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, 'Unit')->getStyle('D'.$rowCount)->getFont()->setBold( true );

        $rowCount+=2;
        $sr = 1;

        $post = $this->request->post;
       // d($post, true);
        $filter = array();
        $filter['company_id'] = $this->session->data['company_id'];
        $filter['company_branch_id'] = $this->session->data['company_branch_id'];
        $filter['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
        $filter['month']=$post['month_id'];
        $this->model['sale_invoice_detail'] = $this->load->model('inventory/sale_invoice_detail');
        $rows = $this->model['sale_invoice_detail']->get_dead_items($filter);

        $html = '';
        $sr=1;

        foreach($rows as $row) {
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $sr);
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount,$row['product_code']);
           $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount,$row['name']);
           $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount,htmlspecialchars_decode($row['description']));
           $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount,$row['product_category']);
           $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount,$row['product_sub_category']);
           $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount,$row['unit']);
            $rowCount++;
            $sr++;
        }

//        $rowCount += 5;
//        $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowCount.':D'.$rowCount);
//        $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount,'Received the above goods in goods order and conditions');


        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Slow Moving Stock.xlsx"');
        header('Cache-Control: max-age=0');
        //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        //$objWriter->save('some_excel_file.xlsx');
        $objWriter->save('php://output');


    }

    public function printDetailReport() {
        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        //d(array('session' => $this->session->data, 'post' => $this->request->post, 'get' => $this->request->get), true);
        $lang = $this->load->language($this->getAlias());
        $post = $this->request->post;
        $session = $this->session->data;

        $post = $this->request->post;
        //d($post, true);
        $filter = array();
        $filter['company_id'] = $this->session->data['company_id'];
        $filter['company_branch_id'] = $this->session->data['company_branch_id'];
        $filter['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
        $filter['month']=$post['month_id'];


        $this->model['sale_invoice_detail'] = $this->load->model('inventory/sale_invoice_detail');
        $rows = $this->model['sale_invoice_detail']->get_dead_items($filter);
//d($rows, true);
        $pdf = new PDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Dead Item Report');
        $pdf->SetSubject('Dead Item Report');

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $session['company_image']
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(20, 34, -10);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // add a page
        $pdf->AddPage();

        
        //$pdf->Ln(3);
        $pdf->SetFont('helvetica', '', 8);
        $sr = 0;
        $total_amount = 0;
        foreach($rows as $detail) {
            $sr++;
            $pdf->Ln(7);
            $pdf->Cell(10, 7, $sr, 1, false, 'L', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 7,$detail['product_code'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(50, 7, $detail['name'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(50, 7, $detail['description'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(50, 7, $detail['product_category'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(40, 7, $detail['product_sub_category'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(10, 7, $detail['unit'], 1, false, 'L', 0, '', 1, false, 'M', 'M');

        }
        $pdf->Ln(7);
        $pdf->Cell(248, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 7, number_format($total_amount,2), 1, false, 'R', 0, '', 0, false, 'M', 'M');

        //Close and output PDF document
        $pdf->Output('Dead Item Report:'.date('YmdHis').'.pdf', 'I');
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
        $this->Ln(2);
        // Title
        $this->Cell(0, 10, $this->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);
        $this->Cell(0, 10, $this->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(10, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(25, 7, 'Product Code', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(50, 7, 'Name', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(50, 7, 'Description', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(50, 7, 'Product Category', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(40, 7, 'Sub Category', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(10, 7, 'Unit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
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