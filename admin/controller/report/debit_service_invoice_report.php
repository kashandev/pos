<?php

class ControllerReportDebitServiceInvoiceReport extends HController {

    protected function getAlias() {
        return 'report/debit_service_invoice_report';
    }

    protected function getDefaultOrder() {
        return 'debit_invoice_id';
    }

    protected function getDefaultSort() {
        return 'DESC';
    }

    protected function getList() {
        parent::getList();
        $this->data['partner_types'] = $this->session->data['partner_types'];
        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
        $this->data['href_print_report'] = $this->url->link($this->getAlias() .'/printReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_print_excel'] = $this->url->link($this->getAlias() .'/printExcelReport', 'token=' . $this->session->data['token'], 'SSL');
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
        $this->response->setOutput($this->render());
    }

    public function printReport() {
        $this->init();
        ini_set('memory_limit','1024M');
        $post = $this->request->post;
        $session = $this->session->data;
        $date_from = MySqlDate($post['date_from']);
        $date_to = MySqlDate($post['date_to']);
        $partner_id = $post['partner_id'];
        // d($post);

        $filter= array(
                'date_from' => MySqlDate($post['date_from']),
                'date_to' =>  MySqlDate($post['date_to']),
                'partner_id' => $post['partner_id']
        );

        $this->model['debit_invoice'] = $this->load->model('report/debit_service_invoice_report');
        $rows = $this->model['debit_invoice']->getDebitInvoiceReport($filter);
        // d($rows,true);

        $arrRows = array();
        foreach($rows as $key => $value) {
            $arrRows[$value['document_identity']][] = array(
                'document_date' => $value['document_date'],
                'document_identity' => $value['document_identity'],
                'detail_remarks' => $value['detail_remarks'],
                'tax_amount' => $value['tax_amount'],
                'net_amount' => $value['net_amount'],
                'base_amount' => $value['base_amount'],
                'amount' => $value['amount'],
                'account' => $value['account'],
                'partner_name' => $value['partner_name']
            );
        }

        // d($arrRows,true);

        $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Hira Anwer');
        $pdf->SetTitle('Debit Service Invoice Report');
        $pdf->SetSubject('Debit Service Invoice Report');
        $date_from = $post['date_from'];
        $date_to = $post['date_to'];

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => 'Debit Service Invoice Report',
            'date_from' => $date_from,
            'date_to' => $date_to,
            'company_logo' => $session['company_image'],
        );
        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(5, 47, 5);
        $pdf->SetHeaderMargin(2);
        $pdf->SetFooterMargin(2);

        // set font
        $pdf->SetFont('helvetica', 'B', 10);
        $sr = 0;
        // add a page
        $pdf->AddPage();
//        d($arrRows,true);

        foreach($arrRows as $key => $rows)
        {
            $pdf->SetFont('helvetica', 'B,U', 10);

            $pdf->ln(1);
            $pdf->Cell(150, 10,'' .$key, 0, false, 'L', 0, '', 1);
            $pdf->ln(2);

                $bank_amount = 0;
                $Wht_amount = 0;
                $Net_amount = 0;
                $sr = 1;
            foreach($rows as $detail) {
                $pdf->SetFont('helvetica', '', 8);
                $pdf->ln(6);
                $pdf->Cell(7, 7,$sr, 1, false, 'L', 0, '', 1);
                $pdf->Cell(25, 7,stdDate($detail['document_date']), 1, false, 'L', 0, '', 1);
                $pdf->Cell(30, 7, html_entity_decode($detail['document_identity']), 1, false, 'L', 0, '', 1);
                $pdf->Cell(30, 7, $detail['partner_name'], 1, false, 'L', 0, '', 1);
                $pdf->Cell(40, 7,  html_entity_decode($detail['account']), 1, false, 'L', 0, '', 1);
                $pdf->Cell(25, 7, number_format($detail['tax_amount'],2), 1, false, 'L', 0, '', 1);
                $pdf->Cell(25, 7, number_format($detail['net_amount'],2), 1, false, 'L', 0, '', 1);
                $sr++;
            }

            $pdf->ln(6);
        }

        $pdf->Ln(7);
        $pdf->Output('Debit Invoice Report:'.date('YmdHis').'.pdf', 'I');

    }

    public function printExcelReport() {
        $this->init();
        ini_set('memory_limit','1024M');
        $post = $this->request->post;
        $session = $this->session->data;
        $date_from = MySqlDate($post['date_from']);
        $date_to = MySqlDate($post['date_to']);
        $partner_id = $post['partner_id'];
        // d($post);

        $filter= array(
                'date_from' => MySqlDate($post['date_from']),
                'date_to' =>  MySqlDate($post['date_to']),
                'partner_id' => $post['partner_id']
        );

        $this->model['debit_invoice'] = $this->load->model('report/debit_service_invoice_report');
        $rows = $this->model['debit_invoice']->getDebitInvoiceReport($filter);
        // d($rows,true);

        $arrRows = array();
        foreach($rows as $key => $value) {
            $arrRows[$value['document_identity']][] = array(
                'document_date' => $value['document_date'],
                'document_identity' => $value['document_identity'],
                'detail_remarks' => $value['detail_remarks'],
                'tax_amount' => $value['tax_amount'],
                'net_amount' => $value['net_amount'],
                'base_amount' => $value['base_amount'],
                'amount' => $value['amount'],
                'account' => $value['account'],
                'partner_name' => $value['partner_name']
            );
        }

        // d($arrRows,true);

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getProperties()
            ->setCreator("Hira Anwer")
            ->setLastModifiedBy("Hira Anwer")
            ->setTitle("Debit Service Invoice Report");

        $objPHPExcel->data = array(
            'company_name' => $session['company_name'],
            'report_name' => 'Debit Service Invoice Report'
        );
        $rowCount = 1;
        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":F".($rowCount));
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

        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":F".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Debit Service Invoice Report');
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);

        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Document Date');
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'Document No.');
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Partner Name');
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'Account');
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'Tax Amount');
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'Net Amount');
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':F'.$rowCount)->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                )
            )
        );
        $rowCount++;

        foreach($arrRows as $key => $value) {
            $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":F".($rowCount));
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $key);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->getFill('ebebeb');
            $rowCount ++;

            foreach ($value as $key_1 => $value_1)
            {
                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, stdDate($value_1['document_date']));
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $value_1['document_identity']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value_1['partner_name']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value_1['account']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value_1['tax_amount']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $value_1['net_amount']);
                $rowCount++;
            }
            $rowCount++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Debit Service Invoice.xlsx"');
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
            $this->Image($image_file, 10, 10, 30, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        // Set font
        $this->SetFont('freesans', 'B', 20);
        $this->Ln(2);
        // Title
        $this->Cell(0, 10, $this->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);
        $this->Cell(0, 10, $this->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);
        $this->SetFont('freesans', 'B', 14);
        $this->Cell(0, 10, $this->data['date_from'].' - '.$this->data['date_to'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(7);

            $this->SetFont('freesans', '', 8);
            $this->ln(7);
            $this->Cell(7, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, 'Doc. Date', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(30, 7, 'Doc. No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(30, 7, 'Partner Name', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(40, 7, 'Account', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, 'Tax Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, 'Net Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('freesans', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}
?>