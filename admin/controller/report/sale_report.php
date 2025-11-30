<?php

class ControllerReportSaleReport extends HController {

    protected function getAlias() {
        return 'report/sale_report';
    }

    protected function getDefaultOrder() {
        return 'sale_invoice_id';
    }

    protected function getDefaultSort() {
        return 'DESC';
    }

    protected function getList() {
        parent::getList();

        $this->data['partner_types'] = $this->session->data['partner_types'];

        $this->model['supplier'] = $this->load->model('setup/supplier');
        $this->data['suppliers'] = $this->model['supplier']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->data['partner_type_id'] = "2";
        $this->model['customer'] = $this->load->model('setup/customer');

        $where = "company_id=" . $this->session->data['company_id'];
        $where .= " AND partner_category_id!=" .'1';

        $this->data['partners'] = $this->model['customer']->getRows($where,array('name'));

        $this->model['brand'] = $this->load->model('inventory/brand');
        $this->data['brands'] = $this->model['brand']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['product'] = $this->load->model('inventory/product');
        $this->data['products'] = $this->model['product']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->data['warehouses'] = $this->model['warehouse']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
        $this->data['href_get_product_json'] = $this->url->link($this->getAlias() . '/getProductJson', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_get_detail_report'] = $this->url->link($this->getAlias() .'/getDetailReport', 'token=' . $this->session->data['token'], 'SSL');
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
        $this->response->setOutput($this->render());
    }

    public function getProductJson() {
        $search = $this->request->post['q'];
        $page = $this->request->post['page'];

        $this->model['product'] = $this->load->model('inventory/product');
        $rows = $this->model['product']->getProductJson($search, $page);

        echo json_encode($rows);
    }

    public function getDetailReport() {
        $post = $this->request->post;
        $session = $this->session->data;
        $this->model['sale_invoice_detail'] = $this->load->model('inventory/sale_invoice_detail');
        $arrWhere = array();
        $arrWhere[] = "`company_id` = '".$session['company_id']."'";
        $arrWhere[] = "`company_branch_id` = '".$session['company_branch_id']."'";
        $arrWhere[] = "`fiscal_year_id` = '".$session['fiscal_year_id']."'";
        if($post['date_from'] != '') {
            $arrWhere[] = "`document_date` >= '".MySqlDate($post['date_from'])."'";
        }
        if($post['date_to'] != '') {
            $arrWhere[] = "`document_date` <= '".MySqlDate($post['date_to'])."'";
        }
        if($post['partner_type_id'])
            $arrWhere[] = "`partner_type_id` = '" . $post['partner_type_id'] . "'";
        if($post['partner_id'])
            $arrWhere[] = "`partner_id` = '" . $post['partner_id'] . "'";
        if($post['warehouse_id'])
            $arrWhere[] = "`warehouse_id` = '" . $post['warehouse_id'] . "'";
        if($post['product_id'])
            $arrWhere[] = "`product_id` = '" . $post['product_id'] . "'";
        if($post['brand_id'])
            $arrWhere[] = "`brand_id` = '" . $post['brand_id'] . "'";
        if($post['container_no'])
            $arrWhere[] = "`container_no` LIKE '" . $post['container_no'] . "%'";

        $where = implode(' AND ', $arrWhere);
        $rows = $this->model['sale_invoice_detail']->getRows($where);
        $html = '';
        foreach($rows as $row) {
            $href = $this->url->link('inventory/sale_invoice'.'/update', 'token=' . $this->session->data['token'] . '&' . 'sale_invoice_id' . '=' . $row['sale_invoice_id'], 'SSL');
            $html .= '<tr>';
            $html .= '<td data-sort="'.$row['document_date'].'">'.stdDate($row['document_date']).'</td>';
            $html .= '<td><a target="_blank" href="'.$href.'">'.$row['document_identity'].'</a></td>';
            $html .= '<td>'.$row['warehouse'].'</td>';
            $html .= '<td>'.$row['partner_name'].'</td>';
            $html .= '<td>'.$row['product_name'].'</td>';
            $html .= '<td>'.$row['qty'].'</td>';
            $html .= '<td>'.$row['rate'].'</td>';
            $html .= '<td>'.$row['amount'].'</td>';
            $html .= '</tr>';
        }

        $json = array(
            'success' => true,
            'post' => $post,
            'html' => $html,
            'rows' => $rows
        );
        //d($json,true);
        $this->response->setOutput(json_encode($json));
    }

    public function printReport() {
        $this->init();
        ini_set('memory_limit','1024M');
        $post = $this->request->post;

        $filter = array();
        $filter[] = "`company_id` = '".$this->session->data['company_id']."'";
        $filter[] = "`company_branch_id` = '".$this->session->data['company_branch_id']."'";
        $filter[] = "`fiscal_year_id` = '".$this->session->data['fiscal_year_id']."'";
        if(isset($post['date_from']) && $post['date_from'] != '') {
            $filter[] = "`document_date` >= '".MySqlDate($post['date_from'])."'";
        }
        if(isset($post['date_to']) && $post['date_to'] != '') {
            $filter[] = "`document_date` <= '".MySqlDate($post['date_to'])."'";
        }
        if(isset($post['partner_type_id']) && $post['partner_type_id'] != '') {
            $filter[] = "`partner_type_id` = '".$post['partner_type_id']."'";
        }
        if(isset($post['partner_id']) && $post['partner_id'] != '') {
            $filter[] = "`partner_id` = '".$post['partner_id']."'";
        }

        if($post['warehouse_id']){
            $filter[] = "`warehouse_id` = '" . $post['warehouse_id'] . "'";
        }
        if($post['product_id']){
            $filter[] = "`product_id` = '" . $post['product_id'] . "'";
        }
        if($post['brand_id']){
            $filter[] = "`brand_id` = '" . $post['brand_id'] . "'";
        }

        $where = implode(' AND ', $filter);
        //d($post, true);
        $this->model['sale_invoice_detail'] = $this->load->model('inventory/sale_invoice_detail');
        $rows = $this->model['sale_invoice_detail']->getRows($where, array('created_at'));
        if($post['group_by']=='document') {
            $this->pdfDocumentWise($rows);
        } elseif($post['group_by']=='partner') {
            $this->pdfPartnerWise($rows);
        } elseif($post['group_by']=='warehouse') {
            $this->pdfWarehouseWise($rows);
        } elseif($post['group_by']=='product') {
            $this->pdfProductWise($rows);
        }elseif($post['group_by']=='brand') {
            $this->pdfBrandWise($rows);
        }
    }

    private function pdfDocumentWise($rows) {
        
        //d($rows, true);
        $invoices = array();
        foreach($rows as $row) {
            if(!isset($invoices[$row['document_identity']])) {
                $invoices[$row['document_identity']] = array(
                    'document_date' => $row['document_date'],
                    'document_identity' => $row['document_identity'],
                    'data' => array()
                );
            }
            $invoices[$row['document_identity']]['data'][] = $row;
        }

        $lang = $this->load->language($this->getAlias());
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
        $post = $this->request->post;
        // d($post,true);
        $company_logo = $setting['value'];

        $pdf = new PDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Farrukh Afaq');
        $pdf->SetTitle('Sale Invoice Report');
        $pdf->SetSubject('Sale Invoice Report');


        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->data['warehouses'] = $this->model['warehouse']->getRow(array('warehouse_id' => $post['warehouse_id']));

        $this->model['partner'] = $this->load->model('common/partner');
        $this->data['partner'] = $this->model['partner']->getRow(array('partner_id' => $post['partner_id']));

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $company_logo,
            'date_from' => $post['date_from'],
            'date_to' => $post['date_to'],
            'warehouse' => $this->data['warehouses']['name'],
            'partner_id' => $this->data['partner']['name']
        );

        if($post['output'] == 'Excel'){
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->setActiveSheetIndex(0);

            $objPHPExcel->getProperties()
                ->setCreator('Farrukh Afaq')
                ->setLastModifiedBy('Farrukh Afaq')
                ->setTitle('Sale Invoice Report');

            $objPHPExcel->data = array(
                'company_name' => $session['company_name'],
                'report_name' => $lang['heading_title'],
                'company_logo' =>$company_logo
            );

            $rowcount = 1;

            $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':I'.$rowcount);
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,$session['company_name']);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount)->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                    ),
                    'font' => array(
                        'size' => 25
                    ),
                    'fill' =>array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array( 'rgb' => 'ebebeb')
                    )
                )
            );
            $rowcount++;

            $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':I'.$rowcount);
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount, $lang['heading_title']);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount)->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                    ),
                    'font' => array(
                        'size' => 20
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array( 'rgb' => 'ebebeb' )
                    )
                )
            );
            $rowcount++;
            $grand_total = 0;
            foreach($invoices as $row) {
                $rowcount++;
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':I'.$rowcount);
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,'Document Date:  '. $row['document_date']);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount)->applyFromArray(
                    array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
                        ),
                        'font' => array(
                            'size' => 14
                        )
                    )
                );
                $rowcount++;

                $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':I'.$rowcount);
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,'Document No:  '. $row['document_identity']);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount)->applyFromArray(
                    array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
                        ),
                        'font' => array(
                            'size' => 14
                        )
                    )
                );
                $rowcount++;

                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(70);
                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(55);
                $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':'.'I'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,'Sr. No.')->getStyle('A'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount,'Product')->getStyle('D'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,'Customer')->getStyle('E'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,'Quantity')->getStyle('F'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,'Unit')->getStyle('G'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,'Rate')->getStyle('H'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount,'Amount')->getStyle('I'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount,'Disc. Amount')->getStyle('J'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount,'Total Amount')->getStyle('K'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':I'.$rowcount)->applyFromArray(
                    array(
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        ),
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'ebebeb')
                        ),
                    )
                );

                $sr =0;
                $total_qty = 0;
                $amount = 0;
                $total_dis_amount = 0;
                $total_amount = 0;

                foreach($row['data'] as $detail) {
                    $rowcount++;
                    $sr++;

                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,$sr)->getStyle('A'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount,$detail['product_name'])->getStyle('B'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,$detail['partner_name'])->getStyle('C'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,number_format($detail['qty'],2))->getStyle('D'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,$detail['unit'])->getStyle('E'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,number_format($detail['rate'],2))->getStyle('F'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount,number_format($detail['amount'],2))->getStyle('G'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount,number_format($detail['discount_amount'],2))->getStyle('H'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount,number_format($detail['total_amount'],2))->getStyle('I'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':I'.$rowcount)->applyFromArray(
                        array(
                            'borders' => array(
                                'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                            )
                        )
                    );
                    $total_qty += $detail['qty'];
                    $amount += $detail['amount'];
                    $total_dis_amount += $detail['discount_amount'];
                    $total_amount += $detail['total_amount'];
                }
                $rowcount++;

                $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount, number_format($total_qty,2))->getStyle('D'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount, number_format($amount,2))->getStyle('G'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount, number_format($total_dis_amount,2))->getStyle('H'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount, number_format($total_amount),2)->getStyle('I'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':I'.$rowcount)->applyFromArray(
                    array(
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        ),
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'ebebeb')
                        ),
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
                        )
                    )
                );
                $grand_total += $total_amount;
                $rowcount +=3;
            }
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount, 'Total')->getStyle('H'.$rowcount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount, number_format($grand_total),2)->getStyle('I'.$rowcount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':I'.$rowcount)->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'ebebeb')
                    ),
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
                    )
                )
            );

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="SaleInvoice|Report.xlsx"');
            header('Cache-Control: max-age=0');
            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $objWriter->save('php://output');
            exit;

        }
        else{

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10, 55, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // set font
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 8);

        $grand_total = 0;
        foreach($invoices as $row) {


            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(201,201,201);
            $pdf->Cell(141, 6,'Document Date: ' . stdDate($row['document_date']), 1, false, 'C', 1, '', 0, false, 'M', 'M');
            $pdf->Cell(141, 6, 'Document No: ' . $row['document_identity'], 1, false, 'C', 1, '', 0, false, 'M', 'M');

            // $pdf->Cell(0,9,'Document Date: ' . stdDate($row['document_date']));
            // $pdf->ln(5);
            // $pdf->Cell(0,9,'Document No: ' . $row['document_identity']);
            $pdf->SetFont('helvetica', '', 8);

            $pdf->ln(10);
            $pdf->Cell(7, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(80, 7, 'Product', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(65, 7, 'Customer', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, 'Quantity', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(15, 7, 'Unit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, 'Rate', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(25, 7, 'Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(25, 7, 'Dis Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(25, 7, 'Total Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');

            $sr =0;
            $total_qty = 0;
            $amount = 0;
            $total_dis_amount = 0;
            $total_amount = 0;
            $pdf->Ln(1);
            foreach($row['data'] as $detail) {
                $sr++;
                $pdf->Ln(6);
                $pdf->Cell(7, 6, $sr, 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(80, 6, $detail['product_name'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(65, 6, html_entity_decode($detail['partner_name']), 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, number_format($detail['qty'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6, $detail['unit'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, number_format($detail['rate'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, number_format($detail['amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, number_format($detail['discount_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, number_format($detail['total_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');

                $total_qty += $detail['qty'];
                $amount += $detail['amount'];
                $total_dis_amount += $detail['discount_amount'];
                $total_amount += $detail['total_amount'];
            }
            $pdf->Ln(6);
            $pdf->Cell(152, 6,'', 0, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 6, number_format($total_qty,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(35, 6, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 6, number_format($amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 6, number_format($total_dis_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 6, number_format($total_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $grand_total += $total_amount;
            $pdf->ln(10);
        }
        //$pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(257, 6,'TOTAL', 0, false, 'R', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(25, 6, number_format($grand_total,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');

        //Close and output PDF document
        $pdf->Output('Sale Invoice Report:'.date('YmdHis').'.pdf', 'I');
    }
    }

    private function pdfPartnerWise($rows) {
        //d($rows, true);
        $invoices = array();
        foreach($rows as $row) {
            if(!isset($invoices[$row['partner_type'].'-'.$row['partner_name']])) {
                $invoices[$row['partner_type'].'-'.$row['partner_name']] = array(
                    'partner_type' => $row['partner_type'],
                    'partner_name' => $row['partner_name'],
                    'data' => array()
                );
            }
            $invoices[$row['partner_type'].'-'.$row['partner_name']]['data'][] = $row;
        }
        //d($invoices, true);
        $lang = $this->load->language($this->getAlias());
        $this->model['image'] = $this->load->model('tool/image');
        $this->model['setting'] = $this->load->model('common/setting');
        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'general',
            'field' => 'company_logo',
        ));
        $post = $this->request->post;
        $session = $this->session->data;
        $company_logo = $setting['value'];

        $pdf = new PDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Sale Invoice Report');
        $pdf->SetSubject('Sale Invoice Report');


        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->data['warehouses'] = $this->model['warehouse']->getRow(array('warehouse_id' => $post['warehouse_id']));

        $this->model['partner'] = $this->load->model('common/partner');
        $this->data['partner'] = $this->model['partner']->getRow(array('partner_id' => $post['partner_id']));

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $company_logo,
            'date_from' => $post['date_from'],
            'date_to' => $post['date_to'],
            'warehouse' => $this->data['warehouses']['name'],
            'partner_id' => $this->data['partner']['name']
        );

        if($post['output'] == 'Excel'){
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->setActiveSheetIndex(0);

            $objPHPExcel->getProperties()
                ->setCreator('Farrukh Afaq')
                ->setLastModifiedBy('Farrukh Afaq')
                ->setTitle('Sale Invoice Report');

            $objPHPExcel->data = array(
                'company_name' => $session['company_name'],
                'report_name' => $lang['heading_title'],
                'company_logo' =>$company_logo
            );

            $rowcount = 1;

            $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':J'.$rowcount);
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,$session['company_name']);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount)->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                    ),
                    'font' => array(
                        'size' => 25
                    ),
                    'fill' =>array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array( 'rgb' => 'ebebeb')
                    )
                )
            );
            $rowcount++;

            $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':J'.$rowcount);
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount, $lang['heading_title']);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount)->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                    ),
                    'font' => array(
                        'size' => 20
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array( 'rgb' => 'ebebeb' )
                    )
                )
            );
            $rowcount++;
            $grand_total = 0;
            foreach($invoices as $row) {
                $rowcount++;
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':J'.$rowcount);
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,'Customer:  '. $row['partner_name']);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount)->applyFromArray(
                    array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
                        ),
                        'font' => array(
                            'size' => 14
                        )
                    )
                );
                $rowcount++;


                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(18);
                $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(70);
                $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':'.'J'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,'Sr. No.')->getStyle('A'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount,'Document Date')->getStyle('B'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,'Document No')->getStyle('C'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,'Product')->getStyle('D'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,'Quantity')->getStyle('E'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,'Unit')->getStyle('F'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount,'Rate')->getStyle('G'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount,'Amount')->getStyle('H'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount,'Disc. Amount')->getStyle('I'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('J'.$rowcount,'Total Amount')->getStyle('J'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':J'.$rowcount)->applyFromArray(
                    array(
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        ),
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'ebebeb')
                        ),
                    )
                );

                $sr =0;
                $total_qty = 0;
                $amount = 0;
                $total_dis_amount = 0;
                $total_amount = 0;

                foreach($row['data'] as $detail) {
                    $rowcount++;
                    $sr++;

                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,$sr)->getStyle('A'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount,stdDate($detail['document_date']))->getStyle('B'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,$detail['document_identity'])->getStyle('C'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,$detail['product_name'])->getStyle('D'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,number_format($detail['qty'],2))->getStyle('E'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,$detail['unit'])->getStyle('F'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount,number_format($detail['rate'],2))->getStyle('G'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount,number_format($detail['amount'],2))->getStyle('H'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount,number_format($detail['discount_amount'],2))->getStyle('I'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$rowcount,number_format($detail['total_amount'],2))->getStyle('J'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':J'.$rowcount)->applyFromArray(
                        array(
                            'borders' => array(
                                'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                            )
                        )
                    );
                    $total_qty += $detail['qty'];
                    $amount += $detail['amount'];
                    $total_dis_amount += $detail['discount_amount'];
                    $total_amount += $detail['total_amount'];
                }
                $rowcount++;

                $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount, number_format($total_qty,2))->getStyle('E'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount, number_format($amount,2))->getStyle('H'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount, number_format($total_dis_amount,2))->getStyle('I'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('J'.$rowcount, number_format($total_amount),2)->getStyle('J'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':J'.$rowcount)->applyFromArray(
                    array(
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        ),
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'ebebeb')
                        ),
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
                        )
                    )
                );
                $grand_total += $total_amount;
                $rowcount +=3;
            }
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount, 'Total')->getStyle('I'.$rowcount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->setCellValue('J'.$rowcount, number_format($grand_total),2)->getStyle('J'.$rowcount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':J'.$rowcount)->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'ebebeb')
                    ),
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
                    )
                )
            );

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="SaleInvoice|Report.xlsx"');
            header('Cache-Control: max-age=0');
            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $objWriter->save('php://output');
            exit;

        }
        else{

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10, 55, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->AddPage();
        // set font
        $pdf->SetFont('helvetica', '', 8);
        $grand_total = 0;
        foreach($invoices as $row) {

            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(201,201,201);
            $pdf->Cell(282, 6,'Customer : ' . html_entity_decode($row['partner_name']), 1, false, 'C', 1, '', 0, false, 'M', 'M');

            // $pdf->Cell(0,9,'Customer : ' . html_entity_decode($row['partner_name']));

            $pdf->SetFont('helvetica', '', 8);
            $pdf->ln(10);
            $pdf->Cell(7, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(15, 7, 'Doc. Date', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(25, 7, 'Doc. No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(100, 7, 'Product', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, 'Quantity', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, 'Unit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, 'Rate', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(25, 7, 'Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(25, 7, 'Dis Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(25, 7, 'Total Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');

            $sr =0;
            $total_qty = 0;
            $amount = 0;
            $total_dis_amount = 0;
            $total_amount = 0;

            $pdf->Ln(1);
            foreach($row['data'] as $detail) {
                $sr++;
                $pdf->Ln(6);
                $pdf->Cell(7, 6, $sr, 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6, stdDate($detail['document_date']), 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, $detail['document_identity'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(100, 6, $detail['product_name'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, number_format($detail['qty'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, $detail['unit'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, number_format($detail['rate'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, number_format($detail['amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, number_format($detail['discount_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, number_format($detail['total_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');

                $total_qty += $detail['qty'];
                $amount += $detail['amount'];
                $total_dis_amount += $detail['discount_amount'];
                $total_amount += $detail['total_amount'];
            }
            $pdf->Ln(6);
            $pdf->Cell(147, 6,'', 0, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 6, number_format($total_qty,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(40, 6, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 6, number_format($amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 6, number_format($total_dis_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 6, number_format($total_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $grand_total += $total_amount;
            $pdf->ln(10);
        }

        //$pdf->Ln(6);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(257, 6,'TOTAL', 0, false, 'R', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(25, 6, number_format($grand_total,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');

        //Close and output PDF document
        $pdf->Output('Sale Invoice Report:'.date('YmdHis').'.pdf', 'I');
    }
    }

    private function pdfWarehouseWise($rows) {
        //d($rows, true);
        $invoices = array();
        foreach($rows as $row) {
            if(!isset($invoices[$row['warehouse']])) {
                $invoices[$row['warehouse']] = array(
                    'warehouse' => $row['warehouse'],
                    'data' => array()
                );
            }
            $invoices[$row['warehouse']]['data'][] = $row;
        }
        //d($invoices, true);
        $lang = $this->load->language($this->getAlias());
        $this->model['image'] = $this->load->model('tool/image');
        $this->model['setting'] = $this->load->model('common/setting');
        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'general',
            'field' => 'company_logo',
        ));

        $post = $this->request->post;
        $session = $this->session->data;
        $company_logo = $setting['value'];

        $pdf = new PDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Sale Invoice Report');
        $pdf->SetSubject('Sale Invoice Report');

        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->data['warehouses'] = $this->model['warehouse']->getRow(array('warehouse_id' => $post['warehouse_id']));

        $this->model['partner'] = $this->load->model('common/partner');
        $this->data['partner'] = $this->model['partner']->getRow(array('partner_id' => $post['partner_id']));

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $company_logo,
            'date_from' => $post['date_from'],
            'date_to' => $post['date_to'],
            'warehouse' => $this->data['warehouses']['name'],
            'partner_id' => $this->data['partner']['name']
        );

        if($post['output'] == 'Excel'){
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->setActiveSheetIndex(0);

            $objPHPExcel->getProperties()
                ->setCreator('Farrukh Afaq')
                ->setLastModifiedBy('Farrukh Afaq')
                ->setTitle('Sale Invoice Report');

            $objPHPExcel->data = array(
                'company_name' => $session['company_name'],
                'report_name' => $lang['heading_title'],
                'company_logo' =>$company_logo
            );

            $rowcount = 1;

            $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':J'.$rowcount);
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,$session['company_name']);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount)->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                    ),
                    'font' => array(
                        'size' => 25
                    ),
                    'fill' =>array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array( 'rgb' => 'ebebeb')
                    )
                )
            );
            $rowcount++;

            $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':J'.$rowcount);
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount, $lang['heading_title']);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount)->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                    ),
                    'font' => array(
                        'size' => 20
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array( 'rgb' => 'ebebeb' )
                    )
                )
            );
            $rowcount++;
            $grand_total = 0;
            foreach($invoices as $row) {
                $rowcount++;
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':J'.$rowcount);
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,'Warehouse:  '. $row['warehouse']);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount)->applyFromArray(
                    array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
                        ),
                        'font' => array(
                            'size' => 14
                        )
                    )
                );
                $rowcount++;


                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(18);
                $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(70);
                $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':'.'J'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,'Sr. No.')->getStyle('A'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount,'Document Date')->getStyle('B'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,'Document No')->getStyle('C'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,'Product')->getStyle('D'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,'Quantity')->getStyle('E'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,'Unit')->getStyle('F'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount,'Rate')->getStyle('G'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount,'Amount')->getStyle('H'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount,'Disc. Amount')->getStyle('I'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('J'.$rowcount,'Total Amount')->getStyle('J'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':J'.$rowcount)->applyFromArray(
                    array(
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        ),
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'ebebeb')
                        ),
                    )
                );

                $sr =0;
                $total_qty = 0;
                $amount = 0;
                $total_dis_amount = 0;
                $total_amount = 0;

                foreach($row['data'] as $detail) {
                    $rowcount++;
                    $sr++;

                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,$sr)->getStyle('A'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount,stdDate($detail['document_date']))->getStyle('B'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,$detail['document_identity'])->getStyle('C'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,$detail['product_name'])->getStyle('D'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,number_format($detail['qty'],2))->getStyle('E'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,$detail['unit'])->getStyle('F'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount,number_format($detail['rate'],2))->getStyle('G'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount,number_format($detail['amount'],2))->getStyle('H'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount,number_format($detail['discount_amount'],2))->getStyle('I'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$rowcount,number_format($detail['total_amount'],2))->getStyle('J'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':J'.$rowcount)->applyFromArray(
                        array(
                            'borders' => array(
                                'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                            )
                        )
                    );
                    $total_qty += $detail['qty'];
                    $amount += $detail['amount'];
                    $total_dis_amount += $detail['discount_amount'];
                    $total_amount += $detail['total_amount'];
                }
                $rowcount++;

                $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount, number_format($total_qty,2))->getStyle('E'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount, number_format($amount,2))->getStyle('H'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount, number_format($total_dis_amount,2))->getStyle('I'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('J'.$rowcount, number_format($total_amount),2)->getStyle('J'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':J'.$rowcount)->applyFromArray(
                    array(
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        ),
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'ebebeb')
                        ),
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
                        )
                    )
                );
                $grand_total += $total_amount;
                $rowcount +=3;
            }
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount, 'Total')->getStyle('I'.$rowcount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->setCellValue('J'.$rowcount, number_format($grand_total),2)->getStyle('J'.$rowcount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':J'.$rowcount)->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'ebebeb')
                    ),
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
                    )
                )
            );

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="SaleInvoice|Report.xlsx"');
            header('Cache-Control: max-age=0');
            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $objWriter->save('php://output');
            exit;

        }
        else{

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10, 55, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->AddPage();
        // set font
        $pdf->SetFont('helvetica', '', 8);
        $grand_total = 0;
        foreach($invoices as $row) {

            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(201,201,201);
            $pdf->Cell(282, 6,'Warehouse : ' . $row['warehouse'], 1, false, 'C', 1, '', 0, false, 'M', 'M');

            // $pdf->Cell(0,9,'Warehouse : ' . $row['warehouse']);
            $pdf->SetFont('helvetica', '', 8);

            $pdf->ln(10);
            $pdf->Cell(7, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(15, 7, 'Doc. Date', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(25, 7, 'Doc. No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(100, 7, 'Product', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, 'Quantity', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, 'Unit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, 'Rate', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(25, 7, 'Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(25, 7, 'Dis Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(25, 7, 'Total Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');

            $sr =0;
            $total_qty = 0;
            $amount = 0;
            $total_dis_amount = 0;
            $total_amount = 0;
            $pdf->Ln(1);
            foreach($row['data'] as $detail) {
                $sr++;
                $pdf->Ln(6);
                $pdf->Cell(7, 6, $sr, 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6, stdDate($detail['document_date']), 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, $detail['document_identity'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(100, 6, $detail['product_name'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, number_format($detail['qty'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, $detail['unit'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, number_format($detail['rate'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, number_format($detail['amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, number_format($detail['discount_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, number_format($detail['total_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');

                $total_qty += $detail['qty'];
                $amount += $detail['amount'];
                $total_dis_amount += $detail['discount_amount'];
                $total_amount += $detail['total_amount'];

            }
            $pdf->Ln(6);
            $pdf->Cell(147, 6,'', 0, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 6, number_format($total_qty,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(40, 6, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 6, number_format($amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 6, number_format($total_dis_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 6, number_format($total_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $grand_total += $total_amount;
            $pdf->ln(10);
        }

        //$pdf->Ln(6);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(257, 6,'TOTAL', 0, false, 'R', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(25, 6, number_format($grand_total,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');

        //Close and output PDF document
        $pdf->Output('Sale Invoice Report:'.date('YmdHis').'.pdf', 'I');
    }
    }

    private function pdfBrandWise($rows) {
        //d($rows, true);
        $invoices = array();
        foreach($rows as $row) {
            //d($row,true);
            if(!isset($invoices[$row['brand_id']])) {
                $invoices[$row['brand_id']] = array(
                    'product_code' => $row['product_code'],
                    'product_name' => $row['product_name'],
                    'cubic_meter' => $row['cubic_meter'],
                    'cubic_feet' => $row['cubic_feet'],
                    'brand' => $row['brand'],
                    'data' => array()
                );
            }
            $invoices[$row['brand_id']]['data'][] = $row;
        }

        $lang = $this->load->language($this->getAlias());
        $this->model['image'] = $this->load->model('tool/image');
        $this->model['setting'] = $this->load->model('common/setting');
        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'general',
            'field' => 'company_logo',
        ));

        $post = $this->request->post;
        $session = $this->session->data;
        $company_logo = $setting['value'];

        $pdf = new PDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Sale Invoice Report');
        $pdf->SetSubject('Sale Invoice Report');

        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->data['warehouses'] = $this->model['warehouse']->getRow(array('warehouse_id' => $post['warehouse_id']));

        $this->model['partner'] = $this->load->model('common/partner');
        $this->data['partner'] = $this->model['partner']->getRow(array('partner_id' => $post['partner_id']));

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $company_logo,
            'date_from' => $post['date_from'],
            'date_to' => $post['date_to'],
            'warehouse' => $this->data['warehouses']['name'],
            'partner_id' => $this->data['partner']['name']
        );

        if($post['output'] == 'Excel'){
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->setActiveSheetIndex(0);

            $objPHPExcel->getProperties()
                ->setCreator('Farrukh Afaq')
                ->setLastModifiedBy('Farrukh Afaq')
                ->setTitle('Sale Invoice Report');

            $objPHPExcel->data = array(
                'company_name' => $session['company_name'],
                'report_name' => $lang['heading_title'],
                'company_logo' =>$company_logo
            );

            $rowcount = 1;

            $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':J'.$rowcount);
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,$session['company_name']);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount)->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                    ),
                    'font' => array(
                        'size' => 25
                    ),
                    'fill' =>array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array( 'rgb' => 'ebebeb')
                    )
                )
            );
            $rowcount++;

            $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':J'.$rowcount);
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount, $lang['heading_title']);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount)->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                    ),
                    'font' => array(
                        'size' => 20
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array( 'rgb' => 'ebebeb' )
                    )
                )
            );
            $rowcount++;
            $grand_total = 0;
            foreach($invoices as $row) {
                $rowcount++;
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':J'.$rowcount);
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,'Brand:  '. $row['brand']);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount)->applyFromArray(
                    array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
                        ),
                        'font' => array(
                            'size' => 14
                        )
                    )
                );
                $rowcount++;


                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(18);
                $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(70);
                $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':'.'J'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,'Sr. No.')->getStyle('A'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount,'Document Date')->getStyle('B'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,'Document No')->getStyle('C'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,'Product')->getStyle('D'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,'Quantity')->getStyle('E'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,'Unit')->getStyle('F'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount,'Rate')->getStyle('G'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount,'Amount')->getStyle('H'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount,'Disc. Amount')->getStyle('I'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('J'.$rowcount,'Total Amount')->getStyle('J'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':J'.$rowcount)->applyFromArray(
                    array(
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        ),
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'ebebeb')
                        ),
                    )
                );

                $sr =0;
                $total_qty = 0;
                $amount = 0;
                $total_dis_amount = 0;
                $total_amount = 0;

                foreach($row['data'] as $detail) {
                    $rowcount++;
                    $sr++;

                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,$sr)->getStyle('A'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount,stdDate($detail['document_date']))->getStyle('B'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,$detail['document_identity'])->getStyle('C'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,$detail['product_name'])->getStyle('D'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,number_format($detail['qty'],2))->getStyle('E'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,$detail['unit'])->getStyle('F'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount,number_format($detail['rate'],2))->getStyle('G'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount,number_format($detail['amount'],2))->getStyle('H'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount,number_format($detail['discount_amount'],2))->getStyle('I'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$rowcount,number_format($detail['total_amount'],2))->getStyle('J'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':J'.$rowcount)->applyFromArray(
                        array(
                            'borders' => array(
                                'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                            )
                        )
                    );
                    $total_qty += $detail['qty'];
                    $amount += $detail['amount'];
                    $total_dis_amount += $detail['discount_amount'];
                    $total_amount += $detail['total_amount'];
                }
                $rowcount++;

                $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount, number_format($total_qty,2))->getStyle('E'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount, number_format($amount,2))->getStyle('H'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount, number_format($total_dis_amount,2))->getStyle('I'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('J'.$rowcount, number_format($total_amount),2)->getStyle('J'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':J'.$rowcount)->applyFromArray(
                    array(
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        ),
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'ebebeb')
                        ),
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
                        )
                    )
                );
                $grand_total += $total_amount;
                $rowcount +=3;
            }
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount, 'Total')->getStyle('I'.$rowcount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->setCellValue('J'.$rowcount, number_format($grand_total),2)->getStyle('J'.$rowcount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':J'.$rowcount)->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'ebebeb')
                    ),
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
                    )
                )
            );

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="SaleInvoice|Report.xlsx"');
            header('Cache-Control: max-age=0');
            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $objWriter->save('php://output');
            exit;

        }
        else{

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10, 55, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->AddPage();
        // set font
        $pdf->SetFont('helvetica', '', 8);
        $grand_total = 0;
        foreach($invoices as $row) {
            //d($row,true);

            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(201,201,201);
            $pdf->Cell(141, 6,'Brand: ' . $row['brand'], 1, false, 'C', 1, '', 0, false, 'M', 'M');



            $pdf->Cell(0,9,'Brand: ' . $row['brand']);

            $pdf->SetFont('helvetica', '', 8);
            $pdf->ln(10);
            $pdf->Cell(7, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, 'Doc. Date', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(30, 7, 'Doc. No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            //$pdf->Cell(25, 7, 'Brand.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(110, 7, 'Product', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, 'Quantity', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, 'Unit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, 'Rate', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(18, 7, 'Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(18, 7, 'Dis Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(18, 7, 'Total Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');

            $sr =0;
            $total_qty = 0;
            $amount = 0;
            $total_dis_amount = 0;
            $total_amount = 0;
            $pdf->Ln(1);
            foreach($row['data'] as $detail) {
                $sr++;
                $pdf->Ln(6);
                $pdf->Cell(7, 6, $sr, 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, stdDate($detail['document_date']), 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(30, 6, $detail['document_identity'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                //$pdf->Cell(25, 6, $detail['brand'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(110, 6, html_entity_decode($detail['partner_name']), 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, number_format($detail['qty'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, $detail['unit'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, number_format($detail['rate'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(18, 6, number_format($detail['amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(18, 6, number_format($detail['discount_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(18, 6, number_format($detail['total_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');

                $total_qty += $detail['qty'];
                $amount += $detail['amount'];
                $total_dis_amount += $detail['discount_amount'];
                $total_amount += $detail['total_amount'];
            }
            $pdf->Ln(6);
            $pdf->Cell(167, 6,'', 0, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 6, number_format($total_qty,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(40, 6, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(18, 6, number_format($amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(18, 6, number_format($total_dis_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(18, 6, number_format($total_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $grand_total += $total_amount;
            $pdf->ln(10);
        }

        //$pdf->Ln(6);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(263, 6,'TOTAL', 0, false, 'R', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(18, 6, number_format($grand_total,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');

        //Close and output PDF document
        $pdf->Output('Sale Invoice Report:'.date('YmdHis').'.pdf', 'I');
    }
    }

    private function pdfProductWise($rows) {
        //d($rows, true);
        $invoices = array();
        foreach($rows as $row) {
            if(!isset($invoices[$row['product_id']])) {
                $invoices[$row['product_id']] = array(
                    'product_code' => $row['product_code'],
                    'product_name' => $row['product_name'],
                    'cubic_meter' => $row['cubic_meter'],
                    'cubic_feet' => $row['cubic_feet'],
                    'data' => array()
                );
            }
            $invoices[$row['product_id']]['data'][] = $row;
        }

        $lang = $this->load->language($this->getAlias());
        $this->model['image'] = $this->load->model('tool/image');
        $this->model['setting'] = $this->load->model('common/setting');
        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'general',
            'field' => 'company_logo',
        ));

        $post = $this->request->post;
        $session = $this->session->data;
        $company_logo = $setting['value'];

        $pdf = new PDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Sale Invoice Report');
        $pdf->SetSubject('Sale Invoice Report');

        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->data['warehouses'] = $this->model['warehouse']->getRow(array('warehouse_id' => $post['warehouse_id']));

        $this->model['partner'] = $this->load->model('common/partner');
        $this->data['partner'] = $this->model['partner']->getRow(array('partner_id' => $post['partner_id']));

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $company_logo,
            'date_from' => $post['date_from'],
            'date_to' => $post['date_to'],
            'warehouse' => $this->data['warehouses']['name'],
            'partner_id' => $this->data['partner']['name']
        );

        if($post['output'] == 'Excel'){
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->setActiveSheetIndex(0);

            $objPHPExcel->getProperties()
                ->setCreator('Farrukh Afaq')
                ->setLastModifiedBy('Farrukh Afaq')
                ->setTitle('Sale Invoice Report');

            $objPHPExcel->data = array(
                'company_name' => $session['company_name'],
                'report_name' => $lang['heading_title'],
                'company_logo' =>$company_logo
            );

            $rowcount = 1;

            $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':J'.$rowcount);
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,$session['company_name']);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount)->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                    ),
                    'font' => array(
                        'size' => 25
                    ),
                    'fill' =>array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array( 'rgb' => 'ebebeb')
                    )
                )
            );
            $rowcount++;

            $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':J'.$rowcount);
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount, $lang['heading_title']);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount)->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                    ),
                    'font' => array(
                        'size' => 20
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array( 'rgb' => 'ebebeb' )
                    )
                )
            );
            $rowcount++;
            $grand_total = 0;
            foreach($invoices as $row) {
                $rowcount++;
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':J'.$rowcount);
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,'Product Code:  '. $row['product_code']);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount)->applyFromArray(
                    array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
                        ),
                        'font' => array(
                            'size' => 14
                        )
                    )
                );
                $rowcount++;

                $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':J'.$rowcount);
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,'Product Name:  '. $row['product_name']);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount)->applyFromArray(
                    array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
                        ),
                        'font' => array(
                            'size' => 14
                        )
                    )
                );
                $rowcount++;


                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(18);
                $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(55);
                $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':'.'J'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,'Sr. No.')->getStyle('A'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount,'Document Date')->getStyle('B'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,'Document No')->getStyle('C'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,'Partner')->getStyle('D'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,'Quantity')->getStyle('E'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,'Unit')->getStyle('F'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount,'Rate')->getStyle('G'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount,'Amount')->getStyle('H'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount,'Disc. Amount')->getStyle('I'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('J'.$rowcount,'Total Amount')->getStyle('J'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':J'.$rowcount)->applyFromArray(
                    array(
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        ),
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'ebebeb')
                        ),
                    )
                );

                $sr =0;
                $total_qty = 0;
                $amount = 0;
                $total_dis_amount = 0;
                $total_amount = 0;

                foreach($row['data'] as $detail) {
                    $rowcount++;
                    $sr++;

                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,$sr)->getStyle('A'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount,stdDate($detail['document_date']))->getStyle('B'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,$detail['document_identity'])->getStyle('C'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,$detail['partner_name'])->getStyle('D'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,number_format($detail['qty'],2))->getStyle('E'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,$detail['unit'])->getStyle('F'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount,number_format($detail['rate'],2))->getStyle('G'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount,number_format($detail['amount'],2))->getStyle('H'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount,number_format($detail['discount_amount'],2))->getStyle('I'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$rowcount,number_format($detail['total_amount'],2))->getStyle('J'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':J'.$rowcount)->applyFromArray(
                        array(
                            'borders' => array(
                                'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                            )
                        )
                    );
                    $total_qty += $detail['qty'];
                    $amount += $detail['amount'];
                    $total_dis_amount += $detail['discount_amount'];
                    $total_amount += $detail['total_amount'];
                }
                $rowcount++;

                $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount, number_format($total_qty,2))->getStyle('E'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount, number_format($amount,2))->getStyle('H'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount, number_format($total_dis_amount,2))->getStyle('I'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('J'.$rowcount, number_format($total_amount),2)->getStyle('J'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':J'.$rowcount)->applyFromArray(
                    array(
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        ),
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'ebebeb')
                        ),
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
                        )
                    )
                );
                $grand_total += $total_amount;
                $rowcount +=3;
            }
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount, 'Total')->getStyle('I'.$rowcount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->setCellValue('J'.$rowcount, number_format($grand_total),2)->getStyle('J'.$rowcount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':J'.$rowcount)->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'ebebeb')
                    ),
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
                    )
                )
            );

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="SaleInvoice|Report.xlsx"');
            header('Cache-Control: max-age=0');
            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $objWriter->save('php://output');
            exit;

        }
        else{


        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10, 55, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->AddPage();
        // set font
        $pdf->SetFont('helvetica', '', 8);
        $grand_total = 0;
        foreach($invoices as $row) {

            $pdf->SetFont('helvetica', 'B', 10);

            $pdf->SetFillColor(201,201,201);
            $pdf->Cell(141, 6,'Product Name: ' . $row['product_name'], 1, false, 'C', 1, '', 0, false, 'M', 'M');
            $pdf->Cell(141, 6,'Product Code: ' . $row['product_code'], 1, false, 'C', 1, '', 0, false, 'M', 'M');



            // $pdf->Cell(0,9,'Product Name: ' . $row['product_name']);
            // $pdf->ln(5);
            // $pdf->Cell(50,9,'Product Code: ' . $row['product_code']);

            $pdf->SetFont('helvetica', '', 8);
            $pdf->ln(10);
            $pdf->Cell(7, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, 'Doc. Date', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(30, 7, 'Doc. No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(90, 7, 'Partner', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, 'Quantity', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, 'Unit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, 'Rate', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(25, 7, 'Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(25, 7, 'Dis Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(25, 7, 'Total Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');

            $sr =0;
            $total_qty = 0;
            $amount = 0;
            $total_dis_amount = 0;
            $total_amount = 0;
            $pdf->Ln(1);
            foreach($row['data'] as $detail) {
                $sr++;
                $pdf->Ln(6);
                $pdf->Cell(7, 6, $sr, 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, stdDate($detail['document_date']), 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(30, 6, $detail['document_identity'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(90, 6, html_entity_decode($detail['partner_name']), 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, number_format($detail['qty'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, $detail['unit'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, number_format($detail['rate'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, number_format($detail['amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, number_format($detail['discount_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, number_format($detail['total_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');

                $total_qty += $detail['qty'];
                $amount += $detail['amount'];
                $total_dis_amount += $detail['discount_amount'];
                $total_amount += $detail['total_amount'];
            }
            $pdf->Ln(6);
            $pdf->Cell(147, 6,'', 0, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 6, number_format($total_qty,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(40, 6, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 6, number_format($amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 6, number_format($total_dis_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 6, number_format($total_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $grand_total += $total_amount;
            $pdf->ln(10);
        }

        $pdf->Ln(6);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(257, 6,'TOTAL', 0, false, 'R', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(25, 6, number_format($grand_total,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');

        //Close and output PDF document
        $pdf->Output('Sale Invoice Report:'.date('YmdHis').'.pdf', 'I');
    }
    }

    private function pdfContainerWise($rows) {
        //d($rows, true);
        $invoices = array();
        foreach($rows as $row) {
            if(!isset($invoices[$row['container_no']])) {
                $invoices[$row['container_no']] = array(
                    'container_no' => $row['container_no'],
                    'data' => array()
                );
            }
            $invoices[$row['container_no']]['data'][] = $row;
        }
        //d($invoices, true);
        $lang = $this->load->language($this->getAlias());
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

        $pdf = new PDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Sale Invoice Report');
        $pdf->SetSubject('Sale Invoice Report');

        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->data['warehouses'] = $this->model['warehouse']->getRow(array('warehouse_id' => $post['warehouse_id']));

        $this->model['partner'] = $this->load->model('common/partner');
        $this->data['partner'] = $this->model['partner']->getRow(array('partner_id' => $post['partner_id']));

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $company_logo,
            'date_from' => $post['date_from'],
            'date_to' => $post['date_to'],
            'warehouse' => $this->data['warehouses']['name'],
            'partner_id' => $this->data['partner']['name']
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10, 55, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // set font
        $pdf->SetFont('helvetica', '', 8);
        foreach($invoices as $row) {
            $pdf->AddPage();

            $pdf->SetFillColor(201,201,201);
            $pdf->Cell(141, 6,'Container No: ' . $row['container_no'], 1, false, 'C', 1, '', 0, false, 'M', 'M');
            // $pdf->Cell(0,10,'Container No: ' . $row['container_no']);

            $pdf->ln(10);
            $pdf->Cell(7, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(15, 7, 'Doc. Date', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(25, 7, 'Doc. No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(15, 7, 'Warehouse', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(120, 7, 'Product', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(15, 7, 'Batch', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(15, 7, 'Quantity', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(14, 7, 'Meter', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(14, 7, 'Feet', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(10, 7, 'Cost', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, 'Total Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');

            $sr =0;
            $total_qty = 0;
            $total_cubic_meter = 0;
            $total_cubic_feet = 0;
            $total_amount = 0;
            $pdf->Ln(1);
            foreach($row['data'] as $detail) {
                $sr++;
                $pdf->Ln(6);
                $pdf->Cell(7, 6, $sr, 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6, $detail['document_date'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, $detail['document_identity'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6, $detail['warehouse'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(120, 6, $detail['product_name'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6, $detail['batch_no'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6, number_format($detail['qty'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(14, 6, number_format($detail['qty'] * $detail['cubic_meter'],4), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(14, 6, number_format($detail['qty'] * $detail['cubic_feet'],4), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(10, 6, number_format($detail['rate'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, number_format($detail['amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $total_qty += $detail['qty'];
                $total_cubic_meter += $detail['total_cubic_meter'];
                $total_cubic_feet += $detail['total_cubic_feet'];
                $total_amount += $detail['amount'];
            }
            $pdf->Ln(6);
            $pdf->Cell(197, 6,'', 0, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(15, 6, number_format($total_qty,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(14, 6, number_format($total_cubic_meter,4), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(14, 6, number_format($total_cubic_feet,4), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(10, 6, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 6, number_format($total_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
        }

        //Close and output PDF document
        $pdf->Output('Sale Invoice Report:'.date('YmdHis').'.pdf', 'I');
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
            $this->Image($image_file, 10, 7, 23, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        // Set font
        $this->SetFont('helvetica', 'B', 20);
        $this->Ln(2);
        // Title
        $this->Cell(0, 10, $this->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);
        $this->Cell(0, 10, $this->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(7);
        $this->SetFont('helvetica', 'B', 14);
        $this->Cell(0, 10, $this->data['date_from'].' - '.$this->data['date_to'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(6);
        $this->Cell(0, 10, 'Warehouse: '.$this->data['warehouse'].' | Partner: '.$this->data['partner_id'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
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