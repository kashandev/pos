<?php

class ControllerReportPurchaseOrderReport extends HController {

    protected function getAlias() {
        return 'report/purchase_order_report';
    }
    
    protected function getDefaultOrder() {
        return 'purchase_invoice_id';
    }

    protected function getDefaultSort() {
        return 'DESC';
    }

    protected function getList() {
        parent::getList();

        $this->data['partner_types'] = $this->session->data['partner_types'];

        $this->model['partner'] = $this->load->model('common/partner');
        $this->data['suppliers'] = $this->model['partner']->getRows(array('company_id' => $this->session->data['company_id'], 'partner_type_id' => 1));

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
            $this->response->setOutput($this->render());
    }

    public function printReportExcel(){
            $lang = $this->load->language($this->getAlias());
            $this->init();
            $filter = array();
        
            if($this->request->post['date_from'])
            {
                $filter['GTE']['document_date'] = MySqlDate($this->request->post['date_from']);
            }
            if($this->request->post['date_to'])
            {
                $filter['LTE']['document_date'] = MySqlDate($this->request->post['date_to']);
            }
            if($this->request->post['partner_id'])
            {
                $filter['EQ']['partner_id'] = $this->request->post['partner_id'];
            }
            if($this->request->post['product_id'])
            {
                $filter['EQ']['product_id'] = $this->request->post['product_id'];
            }

            $cond = parent::getFilterString($filter);

            $this->model['product'] = $this->load->model('inventory/product');
            $this->model['partner_type'] = $this->load->model('common/partner_type');
            $this->model['partner'] = $this->load->model('common/partner');
            $product = $this->model['product']->getArrays('product_id','name', array('company_id' => $this->session->data['company_id']));
            $arrProducts = $this->model['product']->getRow(array('product_id' => $this->request->post['product_id']));
            $partnerType = $this->model['partner_type']->getRow(array('partner_type_id' => $this->request->post['partner_type_id']));
            $arrPartnerType = $this->model['partner_type']->getArrays('partner_type_id','name');
            $partner = $this->model['partner']->getRow(array('partner_id' => $this->request->post['partner_id']));
            $arrPartner = $this->model['partner']->getArrays('partner_id','name');
            $this->model['currency'] = $this->load->model('setup/currency');
            $arrCurrency = $this->model['currency']->getArrays('currency_id','currency_code', array('company_id' => $this->session->data['company_id']));


            $arrfilter = array(
                'from_date' => $this->request->post['date_from'],
                'to_date' => $this->request->post['date_to'],
                'product' => $arrProducts['name'],
                'partner_type' => $partnerType['name'],
                'partner' => $partner['name'],
            );

            $this->model['company'] = $this->load->model('setup/company');
            $company=$this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));

            $this->model['company_branch'] = $this->load->model('setup/company_branch');
            $company_branch=$this->model['company_branch']->getRow(array('company_id' => $this->session->data['company_id']));

            $this->model['purchase_order_report'] = $this->load->model('report/purchase_order_report');
            $rows = $this->model['purchase_order_report']->getRows($cond,array('document_date ASC'));

            $arrRows = array();
            foreach($rows as $row) {
                $row['document_date'] = stdDate($row['document_date']);
                if($this->request->post['group_by'] == 'partner') {
                    $group_name = $arrPartner[$row['partner_id']];
                } elseif($this->request->post['group_by'] == 'product') {
                    $group_name = $product[$row['product_id']];
                } elseif($this->request->post['group_by'] == 'document_date') {
                    $group_name = $row['document_date'];
                } else {
                    $group_name = '';
                }

                $groupBy = $this->request->post['group_by'];
                $arrRows[$group_name][] = array(
                    'document_type_id' => $row['document_type_id'],
                    'document_id' => $row['document_id'],
                    'voucher_date' => $row['document_date'],
                    'voucher_no' => $row['document_identity'],
                    'currency' => $arrCurrency[$row['document_currency_id']],
                    'conversion_rate' => $row['conversion_rate'],
                    'document_identity' => $row['document_identity'],
                    'warehouse_id' => $row['warehouse_id'],
                    'product_category_id' => $row['product_category_id'],
                    'product_id' => $row['product_id'],
                    'product' => $product[$row['product_id']],
                    'partner_id' => $row['partner_id'],
                    'partner' => $arrPartner[$row['partner_id']],
                    'partner_type_id' => $row['partner_type_id'],
                    'partner_type' => $arrPartnerType[$row['partner_type_id']],
                    'unit_id' => $row['unit_id'],
                    'qty' => $row['qty'],
                    'rate' => ($row['qty']==0?0:($row['amount']/$row['qty'])),
                    'amount' => $row['amount']
                );
            }
            // d($arrRows,true);
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getProperties()
            ->setCreator("Hira Anwer")
            ->setTitle("Purchase Order Report");
            $objPHPExcel->data = array(
                'company_name' => $session['company_name'],
                'report_name' => 'Purchase Order Report',
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
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Purchase Order Report');
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
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Doc. Date')->getStyle('A'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'Doc. Identity')->getStyle('B'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Partner Name')->getStyle('C'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'Product')->getStyle('D'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'Qty')->getStyle('E'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'Rate')->getStyle('F'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, 'Amount')->getStyle('G'.$rowCount)->getFont()->setBold( true );
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

            foreach($arrRows as $key => $value) {
                $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":G".($rowCount));
                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $key);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->getFill('ebebeb');
                $rowCount ++;

                foreach ($value as $key_1 => $value_1)
                {
                    $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, stdDate($value_1['voucher_date']));
                    $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $value_1['voucher_no']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value_1['partner']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value_1['product']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value_1['qty']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $value_1['rate']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $value_1['amount']);
                    $rowCount++;
                }
                $rowCount++;
            }
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="Purchase Order Report.xlsx"');
            header('Cache-Control: max-age=0');
            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $objWriter->save('php://output');
            exit;
    }

        public function printReport() {
            $lang = $this->load->language($this->getAlias());
            $this->init();
            $filter = array();
        // d($this->request->post);
            if($this->request->post['date_from'])
            {
                $filter['GTE']['document_date'] = MySqlDate($this->request->post['date_from']);
            }
            if($this->request->post['date_to'])
            {
                $filter['LTE']['document_date'] = MySqlDate($this->request->post['date_to']);
            }
            if($this->request->post['partner_id'])
            {
                $filter['EQ']['partner_id'] = $this->request->post['partner_id'];
            }
            if($this->request->post['product_id'])
            {
                $filter['EQ']['product_id'] = $this->request->post['product_id'];
            }
        // d('hello');
            $cond = parent::getFilterString($filter);
        // d($cond,true);
            $this->model['product'] = $this->load->model('inventory/product');
            $this->model['partner_type'] = $this->load->model('common/partner_type');
            $this->model['partner'] = $this->load->model('common/partner');
            $product = $this->model['product']->getArrays('product_id','name', array('company_id' => $this->session->data['company_id']));
            $arrProducts = $this->model['product']->getRow(array('product_id' => $this->request->post['product_id']));
            $partnerType = $this->model['partner_type']->getRow(array('partner_type_id' => $this->request->post['partner_type_id']));
            $arrPartnerType = $this->model['partner_type']->getArrays('partner_type_id','name');
            $partner = $this->model['partner']->getRow(array('partner_id' => $this->request->post['partner_id']));
            $arrPartner = $this->model['partner']->getArrays('partner_id','name');
            $this->model['currency'] = $this->load->model('setup/currency');
            $arrCurrency = $this->model['currency']->getArrays('currency_id','currency_code', array('company_id' => $this->session->data['company_id']));
//d($product,true);

            $arrfilter = array(
                'from_date' => $this->request->post['date_from'],
                'to_date' => $this->request->post['date_to'],
                'product' => $arrProducts['name'],
                'partner_type' => $partnerType['name'],
                'partner' => $partner['name'],
            );
//d($arrfilter,true);
            $this->model['company'] = $this->load->model('setup/company');
            $company=$this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));

            $this->model['company_branch'] = $this->load->model('setup/company_branch');
            $company_branch=$this->model['company_branch']->getRow(array('company_id' => $this->session->data['company_id']));

            $this->model['purchase_order_report'] = $this->load->model('report/purchase_order_report');
            $rows = $this->model['purchase_order_report']->getRows($cond,array('document_date ASC'));
       // d($rows,true);
            $arrRows = array();
            foreach($rows as $row) {
                $row['document_date'] = stdDate($row['document_date']);
                if($this->request->post['group_by'] == 'partner') {
                    $group_name = $arrPartner[$row['partner_id']];
                } elseif($this->request->post['group_by'] == 'product') {
                    $group_name = $product[$row['product_id']];
                } elseif($this->request->post['group_by'] == 'document_date') {
                    $group_name = $row['document_date'];
                } else {
                    $group_name = '';
                }

                $groupBy = $this->request->post['group_by'];
                $arrRows[$group_name][] = array(
                    'document_type_id' => $row['document_type_id'],
                    'document_id' => $row['document_id'],
                    'voucher_date' => $row['document_date'],
                    'voucher_no' => $row['document_identity'],
                    'currency' => $arrCurrency[$row['document_currency_id']],
                    'conversion_rate' => $row['conversion_rate'],
                    'document_identity' => $row['document_identity'],
                    'warehouse_id' => $row['warehouse_id'],
                    'product_category_id' => $row['product_category_id'],
                    'product_id' => $row['product_id'],
                    'product' => $product[$row['product_id']],
                    'partner_id' => $row['partner_id'],
                    'partner' => $arrPartner[$row['partner_id']],
                    'partner_type_id' => $row['partner_type_id'],
                    'partner_type' => $arrPartnerType[$row['partner_type_id']],
                    'unit_id' => $row['unit_id'],
                    'qty' => $row['qty'],
                    'rate' => ($row['qty']==0?0:($row['amount']/$row['qty'])),
                    'amount' => $row['amount']
                );
            }
        // d($arrRows,true);

            $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Hira Anwer');
            $pdf->SetTitle('Purchase Order Report');
            $pdf->SetSubject('Purchase Order Report');

            $pdf->data = array(
                'company_name' => $session['company_name'],
                'report_name' => $lang['heading_title'],
                'company_logo' => $company_logo,
                'from_date' => stdDate($post['date_from']),
                'to_date' => stdDate($post['date_to']),
            );

            $pdf->SetMargins(3, 50, 5);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            $pdf->AddPage();

            foreach ($arrRows as $key => $value) {
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->Cell(0,10,$key);
                $pdf->ln(10);
                $sr = 1;
                $total_amount = 0;
                foreach ($value as $key1 => $detail) {
                    $total_amount += $detail['amount'];
                    $pdf->SetFont('helvetica', '', 8);
                    if(strlen($detail['product'])<=35) {
                        $pdf->Cell(7, 7, $sr, 1, false, 'R');
                        $pdf->Cell(20, 7, $detail['voucher_date'], 1, false, 'L');
                        $pdf->Cell(30, 7, $detail['voucher_no'], 1, false, 'L');
                        $pdf->Cell(40, 7, $detail['partner'], 1, false, 'L');
                        $pdf->Cell(50, 7, $detail['product'], 1, false, 'L');
                        $pdf->Cell(15, 7, $detail['qty'], 1, false, 'L');
                        $pdf->Cell(20, 7, $detail['rate'], 1, false, 'L');
                        $pdf->Cell(20, 7, $detail['amount'], 1, false, 'R');
                        $pdf->ln(7);
                    }
                    else
                    {
                        $arrRemarks = str_split($detail['product'], 35);
                        foreach($arrRemarks as $index => $product) {
                            if($index==0) {
                                $pdf->Cell(7, 7, $sr, 'TLR', false, 'R');
                                $pdf->Cell(20, 7, $detail['voucher_date'], 'TLR', false, 'L');
                                $pdf->Cell(30, 7, $detail['voucher_no'], 'TLR', false, 'L');
                                $pdf->Cell(40, 7, $detail['partner'], 'TLR', false, 'L');
                                $pdf->Cell(50, 7, $product, 'TLR', false, 'L');
                                $pdf->Cell(15, 7, $detail['qty'], 'TLR', false, 'L');
                                $pdf->Cell(20, 7, $detail['rate'], 'TLR', false, 'L');
                                $pdf->Cell(20, 7, $detail['amount'], 'TLR', false, 'R');
                                $pdf->ln(7);
                            }
                            elseif($index==count($arrRemarks)-1) {
                                $pdf->Cell(7, 7, '', 'BLR', false, 'R');
                                $pdf->Cell(20, 7, '', 'BLR', false, 'L');
                                $pdf->Cell(30, 7, '', 'BLR', false, 'L');
                                $pdf->Cell(40, 7, '', 'BLR', false, 'L');
                                $pdf->Cell(50, 7, $product, 'BLR', false, 'L');
                                $pdf->Cell(15, 7, '', 'BLR', false, 'L');
                                $pdf->Cell(20, 7, '', 'BLR', false, 'L');
                                $pdf->Cell(20, 7, '', 'BLR', false, 'L');
                                $pdf->ln(7);
                            }
                            else {
                                $pdf->Cell(7, 7, '', 'LR', false, 'R');
                                $pdf->Cell(20, 7, '', 'LR', false, 'L');
                                $pdf->Cell(30, 7, '', 'LR', false, 'L');
                                $pdf->Cell(40, 7, '', 'LR', false, 'L');
                                $pdf->Cell(50, 7, $product, 'LR', false, 'L');
                                $pdf->Cell(15, 7, '', 'LR', false, 'L');
                                $pdf->Cell(20, 7,'', 'LR', false, 'L');
                                $pdf->Cell(20, 7, '', 'LR', false, 'L');
                                $pdf->ln(7);
                            }
                        }   
                    }

                    $sr++;
                }

                $pdf->SetFont('helvetica', 'B', 8);
                $pdf->Cell(182, 7, 'Total Amount : ', 1, false, 'R');
                $pdf->Cell(20, 7, $total_amount, 1, false, 'R');
                $pdf->ln(7);
            }

        //Close and output PDF document
            $pdf->Output('Purchase Order Report'.date('YmdHis').'.pdf', 'I');

        //d($row,true);

        // $data = array(
        //     'company' => $company,
        //     'company_branch' => $company_branch,
        //     'filter' => $arrfilter,
        //     'lang' => $lang,
        //     'rows' => $arrRows,
        //     'group' => $groupBy,
        //     'group_name' => $group_name
        // );

        // d($data,true);
        // try
        // {
        //     $pdf=new mPDF();

        //     $pdf->SetDisplayMode('fullpage');
        //     $pdf->mPDF('utf-8','A4','','','15','15','45','18');
        //     $pdf->setHTMLHeader($this->getPDFHeader($data));
        //     $pdf->setHTMLFooter($this->getPDFFooter($data));
        //     $pdf->WriteHTML($this->getPDFStyle($data));
        //     $pdf->WriteHTML($this->getPDFBodySummary($data));

        //     $pdf->Output();
        // }
        // catch(Exception $e) {
        //     echo $e;
        //     exit;
        // }
        // exit;

        }

        private function getPDFStyle($data) {
            $html = '';
            $html .= '<style type="text/css">';
            $html .= 'body {';
            $html .= 'background: #FFFFFF;';
            $html .= '}';
            $html .= 'body, td, th, input, select, textarea, option, optgroup {';
            $html .= 'font-family: Arial, Helvetica, sans-serif;';
            $html .= 'font-size: 10px;';
            $html .= 'color: #000000;';
            $html .= '}';
            $html .= 'h1 {';
            $html .= 'text-transform: uppercase;';
            $html .= 'text-align: center;';
            $html .= 'font-size: 24px;';
            $html .= 'font-weight: normal;';
            $html .= 'margin: 5px 0;';
            $html .= '}';
            $html .= 'h2 {';
            $html .= 'text-transform: uppercase;';
            $html .= 'text-align: center;';
            $html .= 'font-size: 18px;';
            $html .= 'font-weight: normal;';
            $html .= 'padding: 0;';
            $html .= 'margin: 0;';
            $html .= '}';
            $html .= 'h3 {';
            $html .= 'text-align: center;';
            $html .= 'font-size: 16px;';
            $html .= 'font-weight: normal;';
            $html .= 'padding: 0;';
            $html .= 'margin: 5px 0 0 0;';
            $html .= '}';
            $html .= 'table.page_header {width: 100%; border: none; background-color: #DDDDFF; border-bottom: solid 1mm #AAAADD; padding: 2mm }';
            $html .= 'table.page_body {width: 100%; border: solid 1px #DDDDDD; border-collapse: collapse; align="center" }';
            $html .= 'table.page_body th {border: solid 1px #000000; border-collapse: collapse; background-color: #CDCDCD; text-align: center; font-size: 12px; padding: 5px;}';
            $html .= 'table.page_body td {border: solid 1px #000000; border-collapse: collapse;font-size: 10px; padding: 5px;}';
            $html .= 'table.page_footer {width: 100%; border: none; background-color: #DDDDFF; border-top: solid 1mm #AAAADD; padding: 2mm}';
            $html .= '</style>';

            return $html;
        }
        private function getPDFHeader($data) {
            $filter = $data['filter'];
            $lang = $data['lang'];
            $company = $data['company'];
            $companyBranch = $data['company_branch'];
            $this->model['image'] = $this->load->model('tool/image');
            $company_image = $this->model['image']->resize($company['company_logo'],50,50);

            $html  = '';
            $html .= '<table class="page_header">';
            $html .= '<tr>';
            $html .= '<td style="width: 33%; text-align: left;">';
            if($company['company_logo']) {
                $html .= '<img src="' . $company_image . '" alt="Logo"/>';
            }
            $html .= '</td>';
            $html .= '<td style="width: 34%; text-align: center">';
            $html .= '<h1>' . $company['name'] .'</h1>';
            $html .= '<h2>' . $companyBranch['name'] . '</h2>';
            $html .= '<h3>' . $lang['heading_title'] . '</h3>';
            $html .= '</td>';
            $html .= '<td style="width: 33%;">';
            $html .= '<table>';
            $html .= '<tr>';
            $html .= '<td style="text-align: right; font-weight: bold">' . $lang['entry_from_date'] . '</td><td style="text-align: left;">' . $filter['from_date'] . '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td style="text-align: right; font-weight: bold">' . $lang['entry_to_date'] . '</td><td style="text-align: left;">' . $filter['to_date'] . '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td style="text-align: right; font-weight: bold">' . $lang['entry_partner_type'] . '</td><td style="text-align: left;">' . $filter['partner_type'] . '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td style="text-align: right; font-weight: bold">' . $lang['entry_partner_name'] . '</td><td style="text-align: left;">' . $filter['partner'] . '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td style="text-align: right; font-weight: bold">' . $lang['entry_product'] . '</td><td style="text-align: left;">' . $filter['product'] . '</td>';
            $html .= '</tr>';
            $html .= '</table>';
            $html .= '</td>';
            $html .= '</tr>';
            $html .= '</table>';

            return $html;
        }

        private function getPDFFooter($data) {
            $html = '';
            $html .= '<table class="page_footer">';
            $html .= '<tr>';
            $html .= '<td style="width: 33%; text-align: left;">';
            $html .= '&nbsp;';
            $html .= '</td>';
            $html .= '<td style="width: 34%; text-align: center">';
            $html .= 'Page: {PAGENO}';
            $html .= '</td>';
            $html .= '<td style="width: 33%; text-align: right">';
            $html .= 'Date: {DATE '.STD_DATETIME.'}';
            $html .= '</td>';
            $html .= '</tr>';
            $html .= '</table>';

            return $html;
        }

        private function getPDFBodyDetail($data) {
            $stocks = $data['rows'];
            $lang = $data['lang'];

            $html = '';
            foreach($stocks as $warehouse => $categories) {
                foreach($categories as $category => $products) {
                    foreach($products as $product => $rows) {
                        $total_qty = 0;
                        $total_amount =0;

                        $html .= '<div style="padding-top: 10px;">';
                        $html .= '<div style="font-size: 12px;"><strong>' . $lang['entry_warehouse'] . '</strong>' . $warehouse . '</div>';
                        $html .= '<div style="font-size: 12px;"><strong>' . $lang['entry_product_category'] . '</strong>' . $category . '</div>';
                        $html .= '<div style="font-size: 12px;"><strong>' . $lang['entry_product'] . '</strong>' . $product . '</div>';
                        $html .= '<table class="page_body">';
                        $html .= '<thead>';
                        $html .= '<tr>';
                        $html .= '<th style="width: 20%;">' . $lang['column_document_date'] . '</th>';
                        $html .= '<th style="width: 20%;">' . $lang['column_document_no'] . '</th>';
                        $html .= '<th style="width: 10%;">' . $lang['column_qty'] . '</th>';
                        $html .= '<th style="width: 10%;">' . $lang['column_rate'] . '</th>';
                        $html .= '<th style="width: 10%;">' . $lang['column_amount'] . '</th>';
                        $html .= '<th style="width: 10%;">' . $lang['column_total_qty'] . '</th>';
                        $html .= '<th style="width: 10%;">' . $lang['column_total_amount'] . '</th>';
                        $html .= '<th style="width: 10%;">' . $lang['column_avg_rate'] . '</th>';
                        $html .= '</tr>';
                        $html .= '</thead>';
                        $html .= '<tbody>';
                        foreach($rows as $row) {
                            $total_qty += $row['qty'];
                            $total_amount += $row['amount'];
                            $html .= '<tr>';
                            $html .= '<td style="text-align:left;">' . $row['document_date'] . '</td>';
                            $html .= '<td style="text-align:left;">' . $row['document_identity'] . '</td>';
                            $html .= '<td style="text-align:right;">' . number_format($row['qty'],0) . '</td>';
                            $html .= '<td style="text-align:right;">' . number_format($row['rate'],2) . '</td>';
                            $html .= '<td style="text-align:right;">' . number_format($row['amount'],2) . '</td>';
                            $html .= '<td style="text-align:right;">' . number_format($total_qty,0) . '</td>';
                            $html .= '<td style="text-align:right;">' . number_format($total_amount,2) . '</td>';
                            $html .= '<td style="text-align:right;">' . number_format(($total_amount/$total_qty),2) . '</td>';
                            $html .= '</tr>';
                        }
                        $html .= '</tbody>';
                        $html .= '<tfoot>';
                        $html .= '<tr>';
                        $html .= '<th colspan="2">&nbsp;</th>';
                        $html .= '<th style="text-align:right;">' . number_format($total_qty,0) . '</th>';
                        $html .= '<th style="text-align:right;">&nbsp;</th>';
                        $html .= '<th style="text-align:right;">' . number_format($total_amount,2) . '</th>';
                        $html .= '<th colspan="3">&nbsp;</th>';
                        $html .= '</tr>';
                        $html .= '</tfoot>';
                        $html .= '</table>';
                        $html .= '</div>';
                        $html .= '<pagebreak />';
                    }
                }
            }
            $html = substr($html,0,strlen($html)-13);
            return $html;
        }

        private function getPDFBodySummary($data) {
            $stocks = $data['rows'];
            $lang = $data['lang'];
            $group = $data['group'];
            $group_name = $data['group_name'];
//d($group_name,true);
            $html = '';
            foreach($stocks as $group_name => $products) {
                $total_qty = 0;
                $total_amount =0;
                $base_amount = 0;
                $total_base_amount = 0;

                $html .= '<div style="padding-top: 10px;">';
                if($group == 'partner')
                {
                    $html .= '<div style="font-size: 12px;"><strong>' . $lang['entry_partner_name'] . '</strong>' .$group_name . '</div>';
                }
                elseif($group == 'product')
                {
                    $html .= '<div style="font-size: 12px;"><strong>' . $lang['entry_product'] . '</strong>' . $group_name . '</div>';
                }
                elseif($group == 'document_date')
                {
                    $html .= '<div style="font-size: 12px;"><strong>' . $lang['entry_date'] . '</strong>' . $group_name . '</div>';
                }
                $html .= '<table class="page_body">';
                $html .= '<thead>';
                $html .= '<tr>';
                $html .= '<th style="width: 10%;">' . $lang['column_voucher_date'] . '</th>';
                $html .= '<th style="width: 10%;">' . $lang['column_voucher_no'] . '</th>';
                $html .= '<th style="width: 18%;">' . $lang['partner_name'] . '</th>';
                $html .= '<th style="width: 18%;">' . $lang['column_product'] . '</th>';
                $html .= '<th style="width: 8%;">' . $lang['column_qty'] . '</th>';
                $html .= '<th style="width: 10%;">' . $lang['column_rate'] . '</th>';
                $html .= '<th style="width: 14%;">' . $lang['column_amount'] . '</th>';
//            $html .= '<th style="width: 15%;">' . $lang['column_currency'] . '</th>';
//            $html .= '<th style="width: 8%;">' . $lang['column_conversion'] . '</th>';
//            $html .= '<th style="width: 14%;">' . $lang['column_base_amount'] . '</th>';

                $html .= '</tr>';
                $html .= '</thead>';
                $html .= '<tbody>';
                foreach($products as $product => $row) {
                    $total_qty += $row['qty'];
                    $total_amount += $row['amount'];
                    $base_amount = $row['amount'] * $row ['conversion_rate'] ;
                    $total_base_amount += $base_amount;

                    $html .= '<tr>';
                    $html .= '<td style="text-align:left;">' . $row['voucher_date'] . '</td>';
                    $html .= '<td style="text-align:left;">' . $row['voucher_no'] . '</td>';
                    $html .= '<td style="text-align:left;">' . $row['partner'] . '</td>';
                    $html .= '<td style="text-align:left;">' . $row['product'] . '</td>';
                    $html .= '<td style="text-align:right;">' . number_format($row['qty'],0) . '</td>';
                    $html .= '<td style="text-align:right;">' . number_format($row['rate'],2) . '</td>';
                    $html .= '<td style="text-align:right;">' . number_format($row['amount'],2) . '</td>';
//                $html .= '<td style="text-align:left;">' . $row['currency'] . '</td>';
//                $html .= '<td style="text-align:right;">' . number_format($row['conversion_rate'],2) . '</td>';
//                $html .= '<td style="text-align:right;">' . number_format($base_amount,2) . '</td>';
                    $html .= '</tr>';
                }
                $html .= '</tbody>';
                $html .= '<tfoot>';
                $html .= '<tr>';
                $html .= '<th colspan="4">&nbsp;</th>';
                $html .= '<th style="text-align:right;">' . number_format($total_qty,0) . '</th>';
                $html .= '<th style="text-align:right;">&nbsp;</th>';
                $html .= '<th style="text-align:right;">' . number_format($total_amount,2) . '</th>';
//            $html .= '<th style="text-align:right;">&nbsp;</th>';
//            $html .= '<th style="text-align:right;">&nbsp;</th>';
//            $html .= '<th style="text-align:right;">' . number_format($total_base_amount,2) . '</th>';
                $html .= '</tr>';
                $html .= '</tfoot>';
                $html .= '</table>';
                $html .= '</div>';
                $html .= '<pagebreak />';
            }
            $html = substr($html,0,strlen($html)-13);
            return $html;
        }


    }
////d($arrRows,true);
//        $data = $arrRows;
//        $pdf = new PDF();
//        $pdf->company_name = $company['name'];
//        $pdf->company_branch_name = $company_branch['name'];
//        $pdf->group_heading = $this->request->post['group_by'];
//        $pdf->filter = $arrfilter;
//        $pdf->AliasNbPages();
//        $pdf->SetFont('Arial','',14);
//        $pdf->AddPage();
//        $pdf->Body($data);
//        $pdf->Output();
//        exit;
//    }
//}
//
//class PDF extends FPDF
//{
//    var $company_name;
//    var $company_branch_name;
//    var $filter;
//    var $group_heading;
//// Page header
//    function Header()
//    {
//        // Logo
//        $this->Image('image/data/apple_logo.jpg',10,6,30);
//        // Arial bold 15
//        $this->SetFont('times','B',30);
//        // Move to the right
//        $this->Cell(70);
//        // Title
//        $this->Cell(70,10,html_entity_decode($this->company_name),0,0,'C');
//        $this->SetFont('Arial','B',8);
//        $this->Cell(50, 10,'From Date = '.$this->filter['from_date'], 0, 0, 'L');
//        $this->Ln(6);
//
//        $this->Cell(140);
//        $this->Cell(50, 10,'To Date = '.$this->filter['to_date'], 0, 0, 'L');
//        $this->SetFont('Arial','B',15);
//        $this->Ln(6);
//        // Move to the right
//        $this->Cell(70);
//        // Title
//        $this->Cell(70,10,html_entity_decode($this->company_branch_name),0,0,'C');
//
//        $this->SetFont('Arial','B',8);
//
//        $this->Cell(50, 10,'Product ='.$this->filter['product'], 0,0    , 'L');
//        $this->Ln(6);
//        $this->Cell(140);
//        $this->Cell(50, 10,'Supplier ='.$this->filter['supplier'], 0,0 , 'L');
//
//        // Line break
//        $this->Ln(5);
//        // Arial bold 15
//        $this->SetFont('Arial','B',15);
//        // Move to the right
//        $this->Cell(70);
//        // Title
//        $this->Cell(70,10,'Purchase Order Report',0,0,'C');
//        // Line break
//        $this->Ln(15);
//    }
//
//// Page footer
//    function Footer()
//    {
//        // Position at 1.5 cm from bottom
//        $this->SetY(-15);
//        // Arial italic 8
//        $this->SetFont('Arial','I',8);
//        // Page number
//        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
//    }
//
//    function Body($data) {
//        $header = array('Date', 'Invoice', 'Supplier', 'Category', 'Product', 'Qty', 'Rate', 'Currency', 'Conversion Rate', 'Amount');
//        $w = array(15, 15, 23, 25, 20, 15,15, 20, 22,20);
//
//        $previous_group = '';
//        foreach($data as $group => $rows) {
//            if($previous_group != $group && $previous_group != '') {
//                $this->AddPage();
//            }
//            $previous_group = $group;
//            $this->SetFont('helvetica','B',11);
//            $this->SetTextColor(0);
//
//            $this->Ln(7);
//            if($this->group_heading == 'document_date'){
//            $this->Cell(30,10,'Invoice Date :',0,0,'L');
//            $this->Cell(30,10,stdDate($group),0,0,'L');
//            }
//            else if($this->group_heading == 'supplier'){
//                $this->Cell(20,10,'Supplier :',0,0,'L');
//                $this->Cell(30,10,$group,0,0,'L');
//            }
//            else if($this->group_heading == 'product'){
//                $this->Cell(20,10,'Product :',0,0,'L');
//                $this->Cell(30,10,$group,0,0,'L');
//            }
//            // Line break
//            $this->Ln(7);
//
//            // Colors, line width and bold font
//            $this->SetFillColor(93,123,157);
//            $this->SetTextColor(255);
//            $this->SetDrawColor(128,0,0);
//            $this->SetLineWidth(.3);
//            $this->SetFont('','B','7');
//            // Header
//            for($i=0;$i<count($header);$i++)
//                $this->Cell($w[$i],7,$header[$i],1,0,'C',true);
//            $this->Ln();
//            // Color and font restoration
//            $this->SetFillColor(224,235,255);
//            $this->SetTextColor(0);
//            $this->SetFont('','',6);
//            // Data
//            $fill = false;
//            $total_amount = 0;
//            foreach($rows as $row)
//            {
//                $this->Cell($w[0],6,$row['document_date'],'LR',0,'L',$fill);
//                $this->Cell($w[1],6,$row['document_identity'],'LR',0,'L',$fill);
//                $this->Cell($w[2],6,$row['supplier'],'LR',0,'L',$fill);
//                $this->Cell($w[3],6,$row['product_category'],'LR',0,'L',$fill);
//                $this->Cell($w[4],6,$row['product'],'LR',0,'L',$fill);
//                $this->Cell($w[5],6,$row['qty'] . ' ' . $row['unit'] ,'LR',0,'L',$fill);
//                $this->Cell($w[6],6,number_format($row['rate'],2),'LR',0,'R',$fill);
//                $this->Cell($w[7],6,$row['currency'],'LR',0,'R',$fill);
//                $this->Cell($w[8],6,number_format($row['conversion_rate'],2),'LR',0,'R',$fill);
//                $this->Cell($w[9],6,number_format($row['amount'],2),'LR',0,'R',$fill);
//                $this->Ln();
//                $fill = !$fill;
//                $total_amount += $row['amount'];
//            }
//            //d($row,true);
//            $this->SetFillColor(255,255,255);
//            $this->SetTextColor(0);
//            //$this->SetDrawColor(128,0,0);
//            $this->SetLineWidth(.3);
//            $this->SetFont('','B','7');
//            $this->Cell($w[0] + $w[1] + $w[2] + $w[3] + $w[4] + $w[5] + $w[6] + $w[7] +$w[8],6,'',1,0,'L',true);
//            $this->Cell($w[9],6,number_format($total_amount,2),1,0,'R',true);
//            $this->Ln();
//            // Closing line
//            $this->Cell(array_sum($w),0,'','T');
//        }
//    }
//}



    class PDF extends TCPDF {
        public $data = array();

    //Page header
        public function Header() {
        // Logo
            if($this->data['company_logo'] != '') {
                $image_file = DIR_IMAGE.$this->data['company_logo'];
            //$this->Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);
                $this->Image($image_file, 10, 10, 30, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);
            }
        // Set font
            $this->SetFont('times', 'B', 20);
            $this->Ln(2);
        // Title
            $this->Cell(0, 10, $this->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Ln(10);
            $this->Cell(0, 10, $this->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->SetFont('times', 'B', 12);
            $this->Ln(12);
            $this->Cell(0, 10, 'From : '. $this->data['from_date'].' | '.'To : '. $this->data['to_date'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->ln(12);
            $this->SetFont('helvetica', 'B', 8);
            $this->Cell(  7, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell( 20, 7, 'Doc. Date', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell( 30, 7, 'Doc. No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell( 40, 7, 'Partner', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell( 50, 7, 'Product', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell( 15, 7, 'Qty', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell( 20, 7, 'Rate', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell( 20, 7, 'Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
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