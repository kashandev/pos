<?php

class ControllerReportProductReport extends HController {

    protected function getAlias() {
        return 'report/product_report';
    }

    protected function getDefaultOrder() {
        return 'product_master_id';
    }

    protected function getDefaultSort() {
        return 'DESC';
    }

    protected function getList() {
        parent::getList();


        $this->model['product'] = $this->load->model('inventory/product');
        $this->data['products'] = $this->model['product']->getRows(array(),array('name asc'));

        $this->model['product_category'] = $this->load->model('inventory/product_category');
        $this->data['product_categorys'] = $this->model['product_category']->getRows(array(),array('name asc'));
//
//        $this->model['product_group'] = $this->load->model('inventory/product_group');
//        $this->data['product_groups'] = $this->model['product_group']->getRows(array(),array('name asc'));

//        d($this->data['customers'],true);
//        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);

        $this->data['href_print_excel'] = $this->url->link($this->getAlias() .'/printReportExcel', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_print_report'] = $this->url->link($this->getAlias() .'/printReport', 'token=' . $this->session->data['token'], 'SSL');
//        $this->data['href_get_product_master'] = $this->url->link($this->getAlias() .'/GetProductMaster', 'token=' . $this->session->data['token'], 'SSL');

//        $this->data['strValidation'] = "{
//            'rules': {
//                'date_from': {'required': true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},
//                'date_to': {'required': true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},
//
//            },
//            ignore:[],
//        }";

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


    public function GetProductMaster()
    {
        $post = $this->request->post;
        $product_group_id = $post['product_group_id'];

        $this->model['product'] = $this->load->model('inventory/product_master');
        $product_masters = $this->model['product_master']->getRows(array('product_group_id' => $product_group_id),array('name asc'));

        $html = '<option value="">&nbsp;</option>';
        foreach($product_masters as $product_master)
        {
            $html .= '<option value="'.$product_master['product_master_id'].'">'.$product_master['name'].'</option>';
        }

        $json = array(
            'success' => true,
            'html' => $html,
        );

        $this->response->setOutput(json_encode($json));

    }

    public function printReport() {

        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        //$this->model['trial_balance'] = $this->load->model('report/trial_balance');
        $post = $this->request->post;

        $session = $this->session->data;

        $product_category_id = $post['product_category_id'];
        $product_id = $post['product_id'];
        $print_format = $post['print_format'];

//d($post,true);

        $this->model['product_report'] = $this->load->model('report/product_report');
        $rows = $this->model['product_report']->getReports($product_category_id,$product_id);

//d($rows,true);
        foreach($rows as $row) {
            $arrProduct[$row['product_category']][] = $row;
        }

        $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Fahad Siddiqui');
        $pdf->SetTitle('Product Report');
        $pdf->SetSubject('product Report');

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

        //Set Header
        $pdf->data = array(
            'report_name' => 'Product Report',
            'company_name' => $session['company_name'],
            'company_logo' => $company_logo,
            'print_format' => $print_format

        );


        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10, 35, 5);
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

        foreach($arrProduct as $product_group => $product_masters) {

            $pdf->SetFont('helvetica', 'B', 10);

            $pdf->Cell(100, 7,'Product Category : ' .$product_group, 0, false, 'L', 0, '', 1);
            $pdf->ln(8);
            foreach($rows as $goods_detail) {

                $pdf->SetFont('helvetica', '', 8);

                if($print_format == 'all')
                {
                    $pdf->Cell(100, 6,$goods_detail['name'], 1, false, 'L', 0, '', 1);
                    $pdf->Cell(20, 6,$goods_detail['unit'], 1, false, 'L', 0, '', 1);
                    $pdf->Cell(25, 6, number_format($goods_detail['sale_price'],2), 1, false, 'R', 0, '', 1);
                    $pdf->Cell(25, 6, number_format($goods_detail['wholesale_price'],2), 1, false, 'R', 0, '', 1);
                    $pdf->Cell(25, 6, number_format($goods_detail['minimum_price'],2), 1, false, 'R', 0, '', 1);
                    $pdf->ln(6);

                }
                if($print_format == 'sale_price')
                {
                    $pdf->Cell(100, 6,$goods_detail['name'], 1, false, 'L', 0, '', 1);
                    $pdf->Cell(20, 6,$goods_detail['unit'], 1, false, 'L', 0, '', 1);
                    $pdf->Cell(25, 6, number_format($goods_detail['sale_price'],2), 1, false, 'R', 0, '', 1);
                    $pdf->ln(6);
                }
                if($print_format == 'wholesale_price')
                {
                    $pdf->Cell(100, 6,$goods_detail['name'], 1, false, 'L', 0, '', 1);
                    $pdf->Cell(20, 6,$goods_detail['unit'], 1, false, 'L', 0, '', 1);
                    $pdf->Cell(25, 6, number_format($goods_detail['wholesale_price'],2), 1, false, 'R', 0, '', 1);
                    $pdf->ln(6);

                }
                if($print_format == 'minimum_price')
                {
                    $pdf->Cell(100, 6,$goods_detail['name'], 1, false, 'L', 0, '', 1);
                    $pdf->Cell(20, 6,$goods_detail['unit'], 1, false, 'L', 0, '', 1);
                    $pdf->Cell(25, 6, number_format($goods_detail['minimum_price'],2), 1, false, 'R', 0, '', 1);
                    $pdf->ln(6);
                }
            }
        }


        $pdf->Ln(4);

        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->setXY($x,$y);
        $pdf->SetFont('helvetica', 'B', 8);

        //Close and output PDF document
        $pdf->Output('Product Report:'.date('YmdHis').'.pdf', 'I');
    }

    public function printReportExcel() {
        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        //$this->model['trial_balance'] = $this->load->model('report/trial_balance');
        $post = $this->request->post;

        $session = $this->session->data;

        $product_category_id = $post['product_category_id'];
        $product_id = $post['product_id'];
        $print_format = $post['print_format'];

//d($post,true);

        $this->model['product_report'] = $this->load->model('report/product_report');
        $rows = $this->model['product_report']->getReports($product_category_id,$product_id);

//d($rows,true);
        foreach($rows as $row) {
            $arrProduct[$row['product_category']][] = $row;
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

        $session = $this->session->data;
        $company_logo = $setting['value'];

//        echo '<pre>';
//        print_r($arrProduct);
//        exit;

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getProperties()
            ->setCreator("Farrukh Afaq")
            ->setLastModifiedBy("Hira")
            ->setTitle("Product Report");
        $objPHPExcel->data = array(
            'company_name' => $session['company_name'],
            'report_name' => 'Product Report',
        );
        $rowCount = 1;
        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":E".($rowCount));
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
        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":E".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Product Report');
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
        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":E".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, '');
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->getFill('ebebeb');
        $rowCount ++;
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':'.'E'.$rowCount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Description')->getStyle('A'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'Unit')->getStyle('B'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Sale Price')->getStyle('C'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'Wholesale Price')->getStyle('D'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'Minimum Price')->getStyle('E'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':E'.$rowCount)->applyFromArray(
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


        foreach ($arrProduct as $key_1 => $value_1)
        {

            foreach ($value_1 as $key_2 => $value_2) {
//                echo 'key'.$key_2;
//                echo 'value'.$value_2['description'];
                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $value_2['description']);
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $value_2['unit']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value_2['sale_price']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value_2['wholesale_price']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value_2['minimum_price']);
                $rowCount++;
            }

        }
//        exit;
        $rowCount++;
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="product_report.xlsx"');
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
        $this->Ln(5);
        // Title
        $this->Cell(0, 10, $this->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);
        $this->SetFont('times', 'B', 16);
        $this->Cell(0, 10, $this->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->Ln(15);

        $this->SetFont('helvetica', 'B', 9);
        if($this->data['print_format'] == 'all')
        {
            $this->Cell(100, 7, ' Description', 1, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Cell(20, 7, ' Unit', 1, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, ' Sale Price', 1, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, ' Wholesale Price', 1, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, ' Minimum Price', 1, false, 'L', 0, '', 0, false, 'M', 'M');

        }
        if($this->data['print_format'] == 'sale_price')
        {
            $this->Cell(100, 7, ' Description', 1, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Cell(20, 7, ' Unit', 1, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, ' Sale Price', 1, false, 'L', 0, '', 0, false, 'M', 'M');
        }
        if($this->data['print_format'] == 'wholesale_price')
        {
            $this->Cell(100, 7, ' Description', 1, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Cell(20, 7, ' Unit', 1, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, ' Wholesale Price', 1, false, 'L', 0, '', 0, false, 'M', 'M');

        }
        if($this->data['print_format'] == 'minimum_price')
        {
            $this->Cell(100, 7, ' Description', 1, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Cell(20, 7, ' Unit', 1, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, ' Minimum Price', 1, false, 'L', 0, '', 0, false, 'M', 'M');
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