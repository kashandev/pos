<?php

class ControllerReportOpeningStock extends HController {

    protected function getAlias() {
        return 'report/opening_stock';
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
        $this->data['href_print_summary_report'] = $this->url->link($this->getAlias() .'/printSummaryReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_print_excel'] = $this->url->link($this->getAlias() .'/printReportExcel', 'token=' . $this->session->data['token'], 'SSL');

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
        if($post['warehouse_id'] != '') {
            $filter['warehouse_id'] = $post['warehouse_id'];
        }
        if($post['product_category_id'] != '') {
            $filter['product_category_id'] = $post['product_category_id'];
        }
        if($post['product_id'] != '') {
            $filter['product_id'] = $post['product_id'];
        }

        $this->model['opening_stock'] = $this->load->model('inventory/opening_stock_detail');
        $rows = $this->model['opening_stock']->getRows($filter);
        $html = '';
        foreach($rows as $row) {
            $html .= '<tr>';
            $html .= '<td>'.$row['document_date'].'</td>';
            $html .= '<td>'.$row['document_identity'].'</td>';
            $html .= '<td>'.$row['warehouse'].'</td>';
            $html .= '<td>'.$row['product_category'].'</td>';
            $html .= '<td>'.$row['product_code'].'</td>';
            $html .= '<td>'.$row['product'].'</td>';
            $html .= '<td>'.$row['unit'].'</td>';
            $html .= '<td>'.$row['qty'].'</td>';
            $html .= '<td>'.$row['rate'].'</td>';
            $html .= '<td>'.$row['amount'].'</td>';
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

    public function printDetailReport() {
        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        //d(array('session' => $this->session->data, 'post' => $this->request->post, 'get' => $this->request->get), true);
        $lang = $this->load->language($this->getAlias());
        $post = $this->request->post;
        $session = $this->session->data;

        $filter = array();
        $filter['company_id'] = $session['company_id'];
        $filter['company_branch_id'] = $session['company_branch_id'];
        $filter['fiscal_year_id'] = $session['fiscal_year_id'];
        if($post['warehouse_id'] != '') {
            $filter['warehouse_id'] = $post['warehouse_id'];
        }
        if($post['product_category_id'] != '') {
            $filter['product_category_id'] = $post['product_category_id'];
        }
        if($post['product_id'] != '') {
            $filter['product_id'] = $post['product_id'];
        }

        $this->model['opening_stock'] = $this->load->model('inventory/opening_stock_detail');
        $rows = $this->model['opening_stock']->getRows($filter, array('created_at','sort_order'));
        //d($rows, true);
        $pdf = new PDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Opening Stock');
        $pdf->SetSubject('Opening Stock');

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $session['company_image']
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(15,40, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // add a page
        $pdf->AddPage();

        $pdf->SetFont('helvetica', '', 8);
        $sr = 0;
        $total_amount = 0;
        foreach($rows as $detail) {
            $sr++;
            $pdf->Ln(7);
            $pdf->Cell(7, 7, $sr, 1, false, 'L', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(15, 7, stdDate($detail['document_date']), 1, false, 'L', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(30, 7, $detail['document_identity'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 7, $detail['warehouse'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(40, 7, $detail['product_category'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(100, 7, $detail['product'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(10, 7, $detail['unit'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(12, 7, number_format($detail['qty'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(14, 7, number_format($detail['rate'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 7, number_format($detail['amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $total_amount += $detail['amount'];
        }
        $pdf->Ln(7);
        $pdf->Cell(248, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 7, number_format($total_amount,2), 1, false, 'R', 0, '', 0, false, 'M', 'M');

        //Close and output PDF document
        $pdf->Output('Opening Stock:'.date('YmdHis').'.pdf', 'I');
    }

    public function printReportExcel()
    {
        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        //d(array('session' => $this->session->data, 'post' => $this->request->post, 'get' => $this->request->get), true);
        $lang = $this->load->language($this->getAlias());
        $post = $this->request->post;
        $session = $this->session->data;

        $filter = array();
        $filter['company_id'] = $session['company_id'];
        $filter['company_branch_id'] = $session['company_branch_id'];
        $filter['fiscal_year_id'] = $session['fiscal_year_id'];
        if($post['warehouse_id'] != '') {
            $filter['warehouse_id'] = $post['warehouse_id'];
        }
        if($post['product_category_id'] != '') {
            $filter['product_category_id'] = $post['product_category_id'];
        }
        if($post['product_id'] != '') {
            $filter['product_id'] = $post['product_id'];
        }

        $this->model['opening_stock'] = $this->load->model('inventory/opening_stock_detail');
        $rows = $this->model['opening_stock']->getRows($filter, array('created_at','sort_order'));


//        echo '<pre>';
//        print_r($rows);
//        exit;



        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getProperties()
            ->setCreator("Farrukh Afaq")
            ->setLastModifiedBy("Hira")
            ->setTitle("Opening Stock");
        $objPHPExcel->data = array(
            'company_name' => $session['company_name'],
            'report_name' => 'Opening Stock',
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
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Opening Stock');
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
        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":I".($rowCount));
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
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Doc. Date')->getStyle('A'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'Doc. No.')->getStyle('B'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Warehouse')->getStyle('C'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'Product Category')->getStyle('D'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'Product')->getStyle('E'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'Unit')->getStyle('F'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, 'Qty')->getStyle('G'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, 'Rate')->getStyle('G'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, 'Amount')->getStyle('G'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':I'.$rowCount)->applyFromArray(
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

            foreach ($rows as $key_1 => $value_1)
            {
                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, stdDate($value_1['document_date']));
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $value_1['document_identity']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value_1['warehouse']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value_1['product_category']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value_1['product']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $value_1['unit']);
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $value_1['qty']);
                $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $value_1['rate']);
                $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $value_1['amount']);
                $rowCount++;
            }
            $rowCount++;
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="opening_stock.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
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
        $this->SetFont('helvetica', '', 9);
        $this->Cell(7, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(15, 7, 'Date', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(30, 7, 'Document No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(20, 7, 'Warehouse', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(40, 7, 'Product Category', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(100, 7, 'Product', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(10, 7, 'Unit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(12, 7, 'Qty', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(14, 7, 'Rate', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(20, 7, 'Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
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