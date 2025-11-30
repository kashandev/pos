<?php

class ControllerReportBankReceiptReport extends HController {

    protected function getAlias() {
        return 'report/bank_receipt_report';
    }
    
    protected function getDefaultOrder() {
        return 'bank_receipt_id';
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

        $this->data['href_print_excel'] = $this->url->link($this->getAlias() .'/printReportExcel', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
        $this->data['href_print_report'] = $this->url->link($this->getAlias() .'/printReport', 'token=' . $this->session->data['token'], 'SSL');
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

    public function printReport() {

        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        $this->model['bank_receipt_report'] = $this->load->model('report/bank_receipt_report');

        $post = $this->request->post;
        $session = $this->session->data;

       // d($post);
        $date_f = MySqlDate($post['date_from']);
        $date_s = MySqlDate($post['date_to']);
        $partner_id = $post['partner_id'];
        $partner_type_id = $post['partner_type_id'];

        $this->model['company'] = $this->load->model('setup/company');
        $this->model['company_branch'] = $this->load->model('setup/company_branch');

        $filter= array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'from_date' => MySqlDate($post['date_from']),
            'to_date' =>  MySqlDate($post['date_to']),
            'partner_id' => $post['partner_id'],
            'partner_type_id' => $post['partner_type_id'],
        );
        // d($filter);
        $rows = $this->model['bank_receipt_report']->getBankReceiptReport($filter);
        // d($rows,true);
        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        $branch = $this->model['company_branch']->getRow(array('company_branch_id' => $session['company_branch_id']));

        $arrRows = array();

        foreach($rows as $group) {
            $group_name = $group['name'];
            $arrRows[$group_name][] = array(
                'document_date' => $group['document_date'],
                'document_identity' => $group['document_identity'],
                'ref_document_identity' => $group['ref_document_identity'],
                'bank_amount' => $group['bank_amount'],
                'wht_percent' => $group['wht_percent'],
                'wht_amount' => $group['wht_amount'],
                'net_amount' => $group['net_amount'],
            );
        }

        $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Fahad Siddiqui');
        $pdf->SetTitle('Bank Receipt Report');
        $pdf->SetSubject('Bank Receipt Report');

        $date_from = $post['date_from'];
        $date_to = $post['date_to'];

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'company_address' => $branch['address'],
            'company_phone' => $branch['phone_no'],
            'report_name' => 'Bank Receipt Report',
            'date_from' => $date_from,
            'date_to' => $date_to,
            'company_logo' => $session['company_image'],
        );


        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(15, 55, 5);
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
            $pdf->Cell(150, 10,'' .html_entity_decode($group_name), 0, false, 'L', 0, '', 1);
            $pdf->ln(2);
            $bank_amount = 0;
            $Wht_amount = 0;
                $Net_amount = 0;
            foreach($rows as $delivery_challans) {

                $pdf->SetFont('helvetica', '', 8);

                $pdf->ln(6);

                $bank_amount += $delivery_challans['bank_amount'];
                $Wht_amount += $delivery_challans['wht_amount'];
                $Net_amount += $delivery_challans['net_amount'];

                $pdf->Cell(30, 5,stdDate($delivery_challans['document_date']), 0, false, 'L', 0, '', 1);
                $pdf->Cell(30, 5, html_entity_decode($delivery_challans['document_identity']), 0, false, 'L', 0, '', 1);
                $pdf->Cell(30, 5, $delivery_challans['ref_document_identity'], 0, false, 'L', 0, '', 1);
                $pdf->Cell(25, 5, number_format($delivery_challans['bank_amount'],2), 0, false, 'L', 0, '', 1);
                $pdf->Cell(25, 5, number_format($delivery_challans['wht_percent'],2), 0, false, 'L', 0, '', 1);
                $pdf->Cell(25, 5, number_format($delivery_challans['wht_amount'],2), 0, false, 'L', 0, '', 1);
                $pdf->Cell(25, 5, number_format($delivery_challans['net_amount'],2), 0, false, 'L', 0, '', 1);

            }
            $pdf->ln(6);

            $pdf->Cell(70, 5, "", 'T', false, 'L', 0, '', 1);
            $pdf->Cell(20, 5, "Total", 'T', false, 'L', 0, '', 1);
            $pdf->Cell(25, 5, number_format($bank_amount,2), 'T', false, 'L', 0, '', 1);
            $pdf->Cell(25, 5, "", 'T', false, 'L', 0, '', 1);
            $pdf->Cell(25, 5, number_format($Wht_amount,2), 'T', false, 'L', 0, '', 1);
            $pdf->Cell(25, 5, number_format($Net_amount,2), 'T', false, 'L', 0, '', 1);
            $pdf->ln(6);

        }
//        $pdf->Ln(4);

        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->setXY($x,$y);
        $pdf->SetFont('helvetica', 'B', 7);

        $pdf->Ln(7);
        $pdf->Output('Bank Receipt Report:'.date('YmdHis').'.pdf', 'I');

}

    public function printReportExcel()
    {
        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        $this->model['bank_receipt_report'] = $this->load->model('report/bank_receipt_report');

        $post = $this->request->post;
        $session = $this->session->data;

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

        $rows = $this->model['bank_receipt_report']->getBankReceiptReport($filter);

        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        $branch = $this->model['company_branch']->getRow(array('company_branch_id' => $session['company_branch_id']));

        $arrRows = array();

        foreach($rows as $group) {

            $group_name = $group['name'];

            $arrRows[$group_name][] = array(

                'document_date' => $group['document_date'],
                'document_identity' => $group['document_identity'],
                'ref_document_identity' => $group['ref_document_identity'],
                'bank_amount' => $group['bank_amount'],
                'wht_percent' => $group['wht_percent'],
                'wht_amount' => $group['wht_amount'],
                'net_amount' => $group['net_amount'],
            );
        }


//        echo '<pre>';
//        $group_name;
//        print_r($arrRows);
//        exit;

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getProperties()
            ->setCreator("Farrukh Afaq")
            ->setLastModifiedBy("Hira")
            ->setTitle("Bank Receipt Report");

        $objPHPExcel->data = array(
            'company_name' => $session['company_name'],
            'report_name' => 'Bank Receipt Report'
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
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Bank Receipt Report');
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(17);

        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Document Date');
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'Document ID');
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Ref Doc.');
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'Amount');
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'WHT%');
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'WHT Amount');
        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, 'Net Amount');
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':G'.$rowCount)->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                )
            )
        );
        $rowCount++;


        foreach($arrRows as $key => $value) {
            $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":G".($rowCount));
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $key);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->getFill('ebebeb');
            $rowCount ++;
//            echo '<pre>';
//            print_r($value);


            foreach ($value as $key_1 => $value_1)
            {
//                echo 'key1:'.$key_1;
//                echo 'value_1:'.$value_1[];
                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, stdDate($value_1['document_date']));
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $value_1['document_identity']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value_1['ref_document_identity']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value_1['bank_amount']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value_1['wht_percent']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $value_1['wht_amount']);
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $value_1['net_amount']);
                $rowCount++;
            }
            $rowCount++;
        }


//        foreach ($rows as $key => $value)
//        {
//            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $value['document_date']);
//            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $value['document_identity']);
//            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value['ref_document_identity']);
//            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value['bank_amount']);
//            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value['wht_percent']);
//            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $value['wht_amount']);
//            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $value['net_amount']);
//            $rowCount++;
//        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="bank_receipt_report.xlsx"');
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
        $this->SetFont('helvetica', 'B', 20);
        $this->Ln(5);
        // Title
        $this->Cell(0, 10, $this->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);
        $this->Cell(0, 10, $this->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->SetFont('helvetica', 'B', 10);
        $this->Ln(10);
        $this->Cell(0, 10, 'From Date : '.$this->data['date_from'].'     To Date  :  '.$this->data['date_to'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(12);


        $this->SetFont('helvetica', 'B', 9);
        $this->Cell(30, 5, 'Document Date', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(30, 5, 'Document Identity', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(30, 5, 'REF Document', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(25, 5, 'Amount', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(25, 5, 'WHT %', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(25, 5, 'WHT Amount', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(25, 5, 'Net AMount', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
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