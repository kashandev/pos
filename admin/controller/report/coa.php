<?php

class ControllerReportCOA extends HController {

    protected function getAlias() {
        return 'report/coa';
    }

    protected function getDefaultOrder() {
        return 'cao_level1_id';
    }

    protected function getList() {
        parent::getList();

        $this->model['coa_level1'] = $this->load->model('gl/coa_level1');
        $this->data['coa_levels1'] = $this->model['coa_level1']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['coa_level2'] = $this->load->model('gl/coa_level2');
        $this->data['coa_levels2'] = $this->model['coa_level2']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['coa_level3'] = $this->load->model('gl/coa_level3');
        $this->data['coa_levels3'] = $this->model['coa_level3']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['coa'] = $this->load->model('gl/coa');
        $rows = $this->model['coa']->getRows(array('company_id' => $this->session->data['company_id']), array('level1_code', 'level2_code', 'level3_code'));

        foreach($rows as $row){
            $arrCOA[$row['level1_display_name']][$row['level2_display_name']][]=$row['level3_display_name'];
        }

        //d($arrCOA,true);

        $this->data['href_print_excel'] = $this->url->link($this->getAlias() .'/printReportExcel', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['action_print'] = $this->url->link($this->getAlias() .'/printReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_get_coa_level2'] = $this->url->link($this->getAlias() .'/getCOALevel2', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_get_coa_level3'] = $this->url->link($this->getAlias() .'/getCOALevel3', 'token=' . $this->session->data['token'], 'SSL');

        $this->template = $this->getAlias() . '.tpl';
        $this->response->setOutput($this->render());
    }

    public function getCOALevel2() {
        $coa_level1_id = $this->request->post['coa_level1_id'];
        $this->model['coa'] = $this->load->model('gl/coa');
        $rows = $this->model['coa']->getRows(array('company_id' => $this->session->data['company_id'], 'coa_level1_id' => $coa_level1_id));
//        d($rows,true);
        $arrLevel2s = array();
        foreach($rows as $row) {
            $arrLevel2s[$row['coa_level2_id']] = $row['level2_display_name'];
        }

        $html = "";
        $html .= '<option value="">&nbsp;</option>';
        foreach($arrLevel2s as $value => $caption) {
            $html .= '<option value="'.$value.'">'.$caption.'</option>';
        }

        $json = array('success' => true, 'html' => $html);
        echo json_encode($json);
    }

    public function getCOALevel3() {
        $coa_level2_id = $this->request->post['coa_level2_id'];
        $this->model['coa'] = $this->load->model('gl/coa');
        $filter = array('company_id' => $this->session->data['company_id'], 'coa_level2_id' => $coa_level2_id);
        $rows = $this->model['coa']->getRows($filter);
        $arrLevel3s = array();
        foreach($rows as $row) {
            $arrLevel3s[$row['coa_level3_id']] = $row['level3_display_name'];
        }

        $html = "";
        $html .= '<option value="">&nbsp;</option>';
        foreach($arrLevel3s as $value => $caption) {
            $html .= '<option value="'.$value.'">'.$caption.'</option>';
        }

        $json = array('success' => true, 'html' => $html);
        echo json_encode($json);
    }

    public function printReportExcel()
    {
        $this->init();
        $lang = $this->load->language($this->getAlias());
        $post = $this->request->post;
        $session = $this->session->data;

        //d($post, true);
        $filter = array();
        $filter['company_id'] = $this->session->data['company_id'];
        if($post['coa_level1_id'] != '') {
            $filter['coa_level1_id'] = $post['coa_level1_id'];
        }
        if($post['coa_level2_id'] != '') {
            $filter['coa_level2_id'] = $post['coa_level2_id'];
        }
        if($post['coa_level3_id'] != '') {
            $filter['coa_level3_id'] = $post['coa_level3_id'];
        }
        $this->model['coa'] = $this->load->model('gl/coa');
        $rows = $this->model['coa']->getRows($filter, array('level1_code', 'level2_code', 'level3_code'));
        $data = array();
        foreach($rows as $row) {
            $data[$row['level1_display_name']][$row['level2_display_name']][] = $row['level3_display_name'];
        }

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);

        $objPHPExcel->getProperties()
            ->setCreator("Farrukh Afaq")
            ->setLastModifiedBy("Hira")
            ->setTitle("COA");

        $objPHPExcel->data = array(
            'company_name' => $session['company_name'],
            'report_name' => 'COA'
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
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'COA Report');
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

        foreach ($data as $key => $value)
        {
            $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":G".($rowCount));
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $key);
            $rowCount ++;
            foreach ($value as $key_1 => $val_1)
            {
                $objPHPExcel->getActiveSheet()->mergeCells('B'.($rowCount).":G".($rowCount));
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $key_1);
                $rowCount ++;

                foreach ($val_1 as $key_2 => $val_2)
                {
                    $objPHPExcel->getActiveSheet()->mergeCells('C'.($rowCount).":G".($rowCount));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $val_2);
                    $rowCount ++;
                }

            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="COA_Report.xlsx"');
        header('Cache-Control: max-age=0');
        //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        //$objWriter->save('some_excel_file.xlsx');
        $objWriter->save('php://output');
        exit;

    }

    public function printReport() {
        $this->init();
        $lang = $this->load->language($this->getAlias());
        $post = $this->request->post;
        $session = $this->session->data;
        //d($post, true);
        $filter = array();
        $filter['company_id'] = $this->session->data['company_id'];
        if($post['coa_level1_id'] != '') {
            $filter['coa_level1_id'] = $post['coa_level1_id'];
        }
        if($post['coa_level2_id'] != '') {
            $filter['coa_level2_id'] = $post['coa_level2_id'];
        }
        if($post['coa_level3_id'] != '') {
            $filter['coa_level3_id'] = $post['coa_level3_id'];
        }
        $this->model['coa'] = $this->load->model('gl/coa');
        $rows = $this->model['coa']->getRows($filter, array('level1_code', 'level2_code', 'level3_code'));
        $data = array();
        foreach($rows as $row) {
            $data[$row['level1_display_name']][$row['level2_display_name']][] = $row['level3_display_name'];
        }
        //d(array($post, $rows, $data), true);

        $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Chart of Accounts');
        $pdf->SetSubject('Chart of Accounts');

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $session['company_image']
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(15, 35, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set font
        $pdf->SetFont('Helvetica', '', 14);

        // add a page
        $pdf->AddPage();
        // d($data,true);
        foreach($data as $level1_display_name => $level2) {
            $pdf->ln(8);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(0,8,$level1_display_name,'B',0,'L',1);
            if($post['display_level'] >= 2) {
                foreach($level2 as $level2_display_name => $level3) {
                    $pdf->ln(9);
//                    $pdf->SetFillColor(100,125,155);
                    $pdf->Cell(15,8,'',0,0,'',1);
//                    $pdf->SetFillColor(150,175,205);
                    $pdf->Cell(175,8,$level2_display_name,'B',0,'L',1);
                    
                    if($post['display_level']==3) {
                        foreach($level3 as $level3_display_name) {
                            $pdf->ln(9);
//                            $pdf->SetFillColor(100,125,155);
                            $pdf->Cell(15,8,'',0,0,'',1);
//                            $pdf->SetFillColor(150,175,205);
                            $pdf->Cell(15,8,'',0,0,'',1);
//                            $pdf->SetFillColor(200,225,255);
                            $pdf->Cell(160,8,$level3_display_name,'B',0,'L',1);
                        }
                    }
                }
            }
        }

        //Close and output PDF document
        $pdf->Output('Chart of Accounts:'.date('YmdHis').'.pdf', 'I');

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