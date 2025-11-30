<?php

class ControllerReportPurchaseInvoiceReport extends HController {

    protected function getAlias() {
        return 'report/purchase_invoice_report';
    }

    protected function getDefaultOrder() {
        return 'purchase_invoice_id';
    }

    protected function getDefaultSort() {
        return 'DESC';
    }

    protected function getList() {
        parent::getList();
        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->data['warehouses'] = $this->model['warehouse']->getRows(array('company_id' => $this->session->data['company_id'], 'company_branch_id' => $this->session->data['company_branch_id']));
        $this->data['partner_types'] = $this->session->data['partner_types'];
        // $this->model['supplier'] = $this->load->model('setup/supplier');
        // $this->data['suppliers'] = $this->model['supplier']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['partner'] = $this->load->model('common/partner');
        $this->data['suppliers'] = $this->model['partner']->getRows(array('company_id' => $this->session->data['company_id'], 'partner_type_id' => 1));

        $this->model['product'] = $this->load->model('inventory/product');
        $this->data['products'] = $this->model['product']->getRows(array('company_id' => $this->session->data['company_id']));
        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
        $this->data['href_get_product_json'] = $this->url->link($this->getAlias() . '/getProductJson', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_get_detail_report'] = $this->url->link($this->getAlias() .'/getDetailReport', 'token=' . $this->session->data['token'], 'SSL');
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
        $this->model['purchase_invoice_detail'] = $this->load->model('inventory/purchase_invoice_detail');
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
        // if($post['container_no'])
        //     $arrWhere[] = "`container_no` LIKE '" . $post['container_no'] . "%'";

        $where = implode(' AND ', $arrWhere);

        $rows = $this->model['purchase_invoice_detail']->getRows($where);
        $html = '';
        foreach($rows as $row) {
            $href = $this->url->link($row['route'].'/update',$row['primary_key_field'].'='.$row['primary_key_value'].'&token='.$this->session->data['token']);
            $html .= '<tr>';
            $html .= '<td data-sort="'.$row['document_date'].'" >'.stdDate($row['document_date']).'</td>';
            $html .= '<td><a target="_blank" href="'.$href.'">'.$row['document_identity'].'</a></td>';
            $html .= '<td>'.$row['warehouse'].'</td>';
            // $html .= '<td>'.$row['partner_name'].'</td>';
            // $html .= '<td>'.$row['container_no'].'</td>';
            // $html .= '<td>'.$row['batch_no'].'</td>';
            $html .= '<td>'.$row['product_name'].'</td>';
            $html .= '<td>'.$row['qty'].'</td>';
            // $html .= '<td>'.$row['total_cubic_feet'].'</td>';
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
        $this->response->setOutput(json_encode($json));
    }

    public function printReport() {
        $this->init();
        ini_set('memory_limit','1024M');
        $post = $this->request->post;
        // d($post);
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
        if(isset($post['product_id']) && $post['product_id'] != '') {
            $product_id = implode("','", $post['product_id']);
            $filter[] = "`product_id` IN ('".$product_id."')";
        }
        if(isset($post['warehouse_id']) && $post['warehouse_id'] != '') {
            $filter[] = "`warehouse_id` = '".$post['warehouse_id']."'";
        }
        // d($post,true);
        // d($filter);
        $where = implode(' AND ', $filter);
        // d($where);
        $this->model['purchase_invoice_detail'] = $this->load->model('inventory/purchase_invoice_detail');
        $rows = $this->model['purchase_invoice_detail']->getRows($where, array('created_at'));
        // d($rows,true);
        if($post['group_by']=='document') {
            $this->pdfDocumentWise($rows,$post);
        } elseif($post['group_by']=='partner') {
            $this->pdfPartnerWise($rows,$post);
        } elseif($post['group_by']=='warehouse') {
            $this->pdfWarehouseWise($rows,$post);
        } elseif($post['group_by']=='product') {
            $this->pdfProductWise($rows,$post);
        }
    }

    private function pdfDocumentWise($rows,$post) {
        // d($post,true);
        $invoices = array();
        foreach($rows as $row) {
            if(!isset($invoices[$row['document_identity']])) {
                $invoices[$row['document_identity']] = array(
                    'document_date' => $row['document_date'],
                    'warehouse' => $row['warehouse'],
                    'document_identity' => $row['document_identity'],
                    'data' => array()
                );
            }
            $invoices[$row['document_identity']]['data'][] = $row;
        }

        // d($invoices,true);

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
        $post = $this->request->post;

        $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Purchase Invoice Report');
        $pdf->SetSubject('Purchase Invoice Report');

        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->data['warehouses'] = $this->model['warehouse']->getRow(array('warehouse_id' => $post['warehouse_id']));

        $this->model['partner'] = $this->load->model('common/partner');
        $this->data['partner'] = $this->model['partner']->getRow(array('partner_id' => $post['partner_id']));
        // d($this->data['partner'],true);

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $company_logo,
            'date_from' => $post['date_from'],
            'date_to' => $post['date_to'],
            'warehouse' => $this->data['warehouses']['name'],
            'partner_id' => $this->data['partner']['name'],
            'print' => 'document'
        );

        if($post['output'] == 'Excel'){
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->setActiveSheetIndex(0);

            $objPHPExcel->getProperties()
                ->setCreator('Farrukh Afaq')
                ->setLastModifiedBy('Farrukh Afaq')
                ->setTitle('Stock Ledger Report');

            $objPHPExcel->data = array(
                'company_name' => $session['company_name'],
                'report_name' => $lang['heading_title'],
                'company_logo' =>$company_logo
            );

            $rowcount = 1;

            $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':G'.$rowcount);
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

            $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':G'.$rowcount);
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

            foreach($invoices as $row) {
                $rowcount++;
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':G'.$rowcount);
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

                $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':G'.$rowcount);
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

                $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':G'.$rowcount);
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
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(45);
                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':'.'G'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,'Sr. No.')->getStyle('A'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount,'Product')->getStyle('D'.$rowcount)->getFont()->setBold(true);
                // $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,'Container')->getStyle('E'.$rowcount)->getFont()->setBold(true);
                // $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,'Batch')->getStyle('F'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,'Qty')->getStyle('C'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,'Unit')->getStyle('D'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,'Discount')->getStyle('E'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,'Rate')->getStyle('F'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount,'Total Amount')->getStyle('G'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':G'.$rowcount)->applyFromArray(
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

                $sr = 0;
                $total_qty = 0;
                $total_cubic_meter = 0;
                $total_cubic_feet = 0;
                $total_amount = 0;

                foreach($row['data'] as $detail) {
                    $rowcount++;
                    $sr++;

                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,$sr)->getStyle('A'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount,$detail['product_name'])->getStyle('B'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    // $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,$detail['container_no'])->getStyle('C'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    // $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,$detail['batch_no'])->getStyle('D'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,number_format($detail['qty'],2))->getStyle('C'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,$detail['unit'])->getStyle('D'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,$detail['discount_amount'])->getStyle('E'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,number_format($detail['rate']),2)->getStyle('F'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount,number_format($detail['amount']),2)->getStyle('G'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':G'.$rowcount)->applyFromArray(
                        array(
                            'borders' => array(
                                'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                            )
                        )
                    );
                    $total_qty += $detail['qty'];
                    $total_cubic_meter += $detail['total_cubic_meter'];
                    $total_cubic_feet += $detail['total_cubic_feet'];
                    $total_amount += $detail['amount'];
                }
                $rowcount++;

                $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount, number_format($total_qty,2))->getStyle('C'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount, number_format($total_cubic_meter,2))->getStyle('D'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount, number_format($total_cubic_feet,2))->getStyle('E'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount, number_format($total_amount),2)->getStyle('G'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':G'.$rowcount)->applyFromArray(
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
                $rowcount +=3;
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="PurchaseInvoice|Report.xlsx"');
            header('Cache-Control: max-age=0');
            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $objWriter->save('php://output');
            exit;

        }
        else{
            // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10, 55, 3);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            $pdf->AddPage();
            // $pdf->ln(10);
        // set font
        

        foreach($invoices as $row) {
            $pdf->ln(5);

        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('freesans', 'B', 8);
            $pdf->Cell(60, 7, 'Document Date: ' . stdDate($row['document_date']), 0, false, 'L', 1, '', 0, false, 'M', 'M');
            $pdf->ln(5);
            $pdf->Cell(60, 7, 'Document No: ' . $row['document_identity'], 0, false, 'L', 1, '', 0, false, 'M', 'M');
            $pdf->ln(5);
            $pdf->Cell(60, 7, 'Warehouse: ' . $row['warehouse'], 0, false, 'L', 1, '', 0, false, 'M', 'M');
            $pdf->SetFont('freesans', '', 8);
            $sr =0;
            $total_qty = 0;
            $total_cubic_meter = 0;
            $total_cubic_feet = 0;
            $total_amount = 0;
            // $pdf->Ln(1);
            foreach($row['data'] as $detail) {
                $sr++;
                $pdf->Ln(6);
                $pdf->Cell(7, 6, $sr, 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(100, 6, $detail['product_name'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                // $pdf->Cell(20, 6, $detail['container_no'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                // $pdf->Cell(20, 6, $detail['batch_no'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6, number_format($detail['qty'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(14, 6, $detail['unit'], 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(14, 6, $detail['discount_amount'], 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(10, 6, number_format($detail['rate'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, number_format($detail['amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $total_qty += $detail['qty'];
                $total_cubic_meter += $detail['total_cubic_meter'];
                $total_cubic_feet += $detail['total_cubic_feet'];
                $total_amount += $detail['amount'];
            }
            $pdf->Ln(6);
            $pdf->Cell(107, 6,'', 0, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(15, 6, number_format($total_qty,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(14, 6, number_format($total_cubic_meter,4), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(14, 6, number_format($total_cubic_feet,4), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(10, 6, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 6, number_format($total_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            
        }

        //Close and output PDF document
        $pdf->Output('Purchase Invoice Report:'.date('YmdHis').'.pdf', 'I');
    }
    }

    private function pdfPartnerWise($rows,$post) {
        $invoices = array();
        foreach($rows as $row) {
            if(!isset($invoices[$row['partner_type'].'-'.$row['partner_name']])) {
                $invoices[$row['partner_type'].'-'.$row['partner_name']] = array(
                    'warehouse' => $row['warehouse'],
                    'partner_type' => $row['partner_type'],
                    'partner_name' => $row['partner_name'],
                    'data' => array()
                );
            }
            $invoices[$row['partner_type'].'-'.$row['partner_name']]['data'][] = $row;
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
        $company_logo = $setting['value'];
        $post = $this->request->post;

        if($post['output'] == 'Excel'){
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->setActiveSheetIndex(0);

            $objPHPExcel->getProperties()
                ->setCreator('Farrukh Afaq')
                ->setLastModifiedBy('Farrukh Afaq')
                ->setTitle('Purchase Invocie Report');

            $objPHPExcel->data = array(
                'company_name' => $session['company_name'],
                'report_name' => $lang['heading_title'],
                'company_logo' =>$company_logo
            );

            $rowcount = 1;

            $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':K'.$rowcount);
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

            $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':K'.$rowcount);
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

            foreach($invoices as $row) {
                $rowcount++;
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':K'.$rowcount);
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,'Supplier:  '. $row['partner_name']);
                $rowcount++;
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
                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(45);
                $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':'.'K'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,'Sr. No.')->getStyle('A'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount,'Doc. Date')->getStyle('B'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,'Doc. No.')->getStyle('C'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,'Product')->getStyle('D'.$rowcount)->getFont()->setBold(true);
                // $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,'Container')->getStyle('E'.$rowcount)->getFont()->setBold(true);
                // $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,'Batch')->getStyle('F'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,'Qty')->getStyle('E'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,'Unit')->getStyle('F'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount,'Discount')->getStyle('G'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount,'Rate')->getStyle('H'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount,'Total Amount')->getStyle('I'.$rowcount)->getFont()->setBold(true);
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

                $sr = 0;
                $total_qty = 0;
                $total_cubic_meter = 0;
                $total_cubic_feet = 0;
                $total_amount = 0;

                foreach($row['data'] as $detail) {
                    $rowcount++;

                    $sr++;

                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,$sr)->getStyle('A'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount,stdDate($detail['document_date']))->getStyle('B'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,$detail['document_identity'])->getStyle('C'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,$detail['product_name'])->getStyle('D'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    // $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,$detail['container_no'])->getStyle('E'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    // $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,$detail['batch_no'])->getStyle('F'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,number_format($detail['qty'],2))->getStyle('E'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,$detail['unit'])->getStyle('F'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount,$detail['discount_amount'])->getStyle('G'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount,number_format($detail['rate']),2)->getStyle('H'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount,number_format($detail['amount']),2)->getStyle('I'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':I'.$rowcount)->applyFromArray(
                        array(
                            'borders' => array(
                                'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                            )
                        )
                    );
                    $total_qty += $detail['qty'];
                    $total_cubic_meter += $detail['total_cubic_meter'];
                    $total_cubic_feet += $detail['total_cubic_feet'];
                    $total_amount += $detail['amount'];
                }

                $rowcount++;

                $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount, number_format($total_qty,2))->getStyle('G'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount, number_format($total_cubic_meter,2))->getStyle('H'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount, number_format($total_cubic_feet,2))->getStyle('I'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount, number_format($total_amount),2)->getStyle('K'.$rowcount)->getFont()->setBold(true);
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
                $rowcount +=3;
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="PurchaseInvoice|Report.xlsx"');
            header('Cache-Control: max-age=0');
            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $objWriter->save('php://output');
            exit;
        }
        else{

        $pdf = new PDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Purchase Invoice Report');
        $pdf->SetSubject('Purchase Invoice Report');

        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->data['warehouses'] = $this->model['warehouse']->getRow(array('warehouse_id' => $post['warehouse_id']));

        $this->model['partner'] = $this->load->model('common/partner');
        $this->data['partner'] = $this->model['partner']->getRow(array('partner_id' => $post['partner_id']));
        // d($this->data['partner'],true);

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $company_logo,
            'date_from' => $post['date_from'],
            'date_to' => $post['date_to'],
            'warehouse' => $this->data['warehouses']['name'],
            'partner_id' => $this->data['partner']['name'],
            'print' => 'partner'
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10,58,3);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // set font
        $pdf->SetFont('freesans', 'B', 8);
        foreach($invoices as $row) {
            $pdf->AddPage();
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(135, 7,'Supplier: ' . $row['partner_name'], 0, false, 'L', 1, '', 0, false, 'M', 'M');
            $pdf->ln(5);
            $pdf->Cell(135, 7, 'Warehouse: ' . $row['warehouse'], 0, false, 'L', 1, '', 0, false, 'M', 'M');
            // $pdf->ln(9);
            $sr =0;
            $total_qty = 0;
            $total_cubic_meter = 0;
            $total_cubic_feet = 0;
            $total_amount = 0;
            $pdf->Ln(1);
            foreach($row['data'] as $detail) {
                $sr++;
                $pdf->Ln(6);
                $pdf->SetFont('freesans', '', 8);
                $pdf->Cell(7, 6, $sr, 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6, $detail['document_date'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, $detail['document_identity'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(150, 6, $detail['product_name'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                // $pdf->Cell(15, 6, $detail['container_no'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                // $pdf->Cell(15, 6, $detail['batch_no'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6, number_format($detail['qty'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(14, 6, $detail['unit'], 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(14, 6, $detail['discount_amount'], 1, false, 'R', 0, '', 1, false, 'M', 'M');
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
        $pdf->Output('Purchase Invoice Report:'.date('YmdHis').'.pdf', 'I');
    }
    }

    private function pdfWarehouseWise($rows,$post) {
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
        $post = $this->request->post;

        if($post['output'] == 'Excel'){
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->setActiveSheetIndex(0);

            $objPHPExcel->getProperties()
                ->setCreator('Farrukh Afaq')
                ->setLastModifiedBy('Farrukh Afaq')
                ->setTitle('Purchase Invocie Report');

            $objPHPExcel->data = array(
                'company_name' => $session['company_name'],
                'report_name' => $lang['heading_title'],
                'company_logo' =>$company_logo
            );

            $rowcount = 1;

            $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':K'.$rowcount);
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

            $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':K'.$rowcount);
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

            foreach($invoices as $row) {
                $rowcount++;
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':K'.$rowcount);
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
                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(45);
                $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':'.'K'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,'Sr. No.')->getStyle('A'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount,'Doc. Date')->getStyle('B'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,'Doc. No.')->getStyle('C'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,'Product')->getStyle('D'.$rowcount)->getFont()->setBold(true);
                // $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,'Container')->getStyle('E'.$rowcount)->getFont()->setBold(true);
                // $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,'Batch')->getStyle('F'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,'Qty')->getStyle('E'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,'Unit')->getStyle('F'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount,'Discount')->getStyle('G'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount,'Rate')->getStyle('H'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount,'Total Amount')->getStyle('I'.$rowcount)->getFont()->setBold(true);
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

                $sr = 0;
                $total_qty = 0;
                $total_cubic_meter = 0;
                $total_cubic_feet = 0;
                $total_amount = 0;

                foreach($row['data'] as $detail) {
                    $rowcount++;

                    $sr++;

                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,$sr)->getStyle('A'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount,stdDate($detail['document_date']))->getStyle('B'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,$detail['document_identity'])->getStyle('C'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,$detail['product_name'])->getStyle('D'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    // $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,$detail['container_no'])->getStyle('E'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    // $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,$detail['batch_no'])->getStyle('F'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,number_format($detail['qty'],2))->getStyle('E'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,$detail['unit'])->getStyle('F'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount,$detail['discount_amount'])->getStyle('G'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount,number_format($detail['rate']),2)->getStyle('H'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount,number_format($detail['amount']),2)->getStyle('I'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':I'.$rowcount)->applyFromArray(
                        array(
                            'borders' => array(
                                'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                            )
                        )
                    );
                    $total_qty += $detail['qty'];
                    $total_cubic_meter += $detail['total_cubic_meter'];
                    $total_cubic_feet += $detail['total_cubic_feet'];
                    $total_amount += $detail['amount'];
                }

                $rowcount++;

                $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount, number_format($total_qty,2))->getStyle('G'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount, number_format($total_cubic_meter,2))->getStyle('H'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount, number_format($total_cubic_feet,2))->getStyle('I'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount, number_format($total_amount),2)->getStyle('K'.$rowcount)->getFont()->setBold(true);
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
                $rowcount +=3;

            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="PurchaseInvoice|Report.xlsx"');
            header('Cache-Control: max-age=0');
            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $objWriter->save('php://output');
            exit;
        }
        else{

        $pdf = new PDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Purchase Invoice Report');
        $pdf->SetSubject('Purchase Invoice Report');

         $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->data['warehouses'] = $this->model['warehouse']->getRow(array('warehouse_id' => $post['warehouse_id']));

        $this->model['partner'] = $this->load->model('common/partner');
        $this->data['partner'] = $this->model['partner']->getRow(array('partner_id' => $post['partner_id']));
        // d($this->data['partner'],true);

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $company_logo,
            'date_from' => $post['date_from'],
            'date_to' => $post['date_to'],
            'warehouse' => $this->data['warehouses']['name'],
            'partner_id' => $this->data['partner']['name'],
            'print' => 'warehouse'
        );
        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10,55,3);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            $pdf->AddPage();
        // set font
        
        foreach($invoices as $row) {
            $pdf->ln(3);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetFont('freesans', 'B', 8);
            $pdf->Cell(150, 6,'Warehouse: ' . $row['warehouse'], 0, false, 'L', 1, '', 0, false, 'M', 'M');
            $sr = 0;
            $total_qty = 0;
            $total_cubic_meter = 0;
            $total_cubic_feet = 0;
            $total_amount = 0;
            // $pdf->Ln(1);
            foreach($row['data'] as $detail) {
                $sr++;
                $pdf->Ln(6);
                $pdf->SetFont('freesans', '', 8);
                $pdf->Cell(7, 6, $sr, 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6, $detail['document_date'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, $detail['document_identity'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(150, 6, $detail['product_name'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                // $pdf->Cell(15, 6, $detail['container_no'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                // $pdf->Cell(15, 6, $detail['batch_no'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6, number_format($detail['qty'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(14, 6, $detail['unit'], 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(14, 6, $detail['discount_amount'], 1, false, 'R', 0, '', 1, false, 'M', 'M');
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
        $pdf->Output('Purchase Invoice Report:'.date('YmdHis').'.pdf', 'I');
    }
    }

    private function pdfProductWise($rows,$post) {
        $invoices = array();
        foreach($rows as $row) {
            if(!isset($invoices[$row['product_id']])) {
                $invoices[$row['product_id']] = array(
                    'warehouse' => $row['warehouse'],
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

        $session = $this->session->data;
        $company_logo = $setting['value'];
        $post = $this->request->post;

        if($post['output'] == 'Excel'){
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->setActiveSheetIndex(0);

            $objPHPExcel->getProperties()
                ->setCreator('Farrukh Afaq')
                ->setLastModifiedBy('Farrukh Afaq')
                ->setTitle('Purchase Invocie Report');

            $objPHPExcel->data = array(
                'company_name' => $session['company_name'],
                'report_name' => $lang['heading_title'],
                'company_logo' =>$company_logo
            );

            $rowcount = 1;

            $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':K'.$rowcount);
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

            $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':K'.$rowcount);
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

            foreach($invoices as $row) {
                $rowcount++;
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':K'.$rowcount);
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

                $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':K'.$rowcount);
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

                $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':K'.$rowcount);
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
                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':'.'J'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,'Sr. No.')->getStyle('A'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount,'Doc. Date')->getStyle('B'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,'Doc. No.')->getStyle('C'.$rowcount)->getFont()->setBold(true);
                // $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,'Container')->getStyle('E'.$rowcount)->getFont()->setBold(true);
                // $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,'Batch')->getStyle('F'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,'Qty')->getStyle('D'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,'Unit')->getStyle('E'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,'Discount')->getStyle('F'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount,'Rate')->getStyle('G'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount,'Total Amount')->getStyle('H'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':H'.$rowcount)->applyFromArray(
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

                $sr = 0;
                $total_qty = 0;
                $total_cubic_meter = 0;
                $total_cubic_feet = 0;
                $total_amount = 0;

                foreach($row['data'] as $detail) {
                    $rowcount++;
                    $sr++;

                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,$sr)->getStyle('A'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount,stdDate($detail['document_date']))->getStyle('B'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,$detail['document_identity'])->getStyle('C'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    // $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,$detail['container_no'])->getStyle('D'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    // $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,$detail['batch_no'])->getStyle('E'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,number_format($detail['qty'],2))->getStyle('D'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,$detail['unit'])->getStyle('E'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,$detail['discount_amount'])->getStyle('F'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount,number_format($detail['rate']),2)->getStyle('G'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount,number_format($detail['amount']),2)->getStyle('H'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':H'.$rowcount)->applyFromArray(
                        array(
                            'borders' => array(
                                'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                            )
                        )
                    );
                    $total_qty += $detail['qty'];
                    $total_cubic_meter += $detail['total_cubic_meter'];
                    $total_cubic_feet += $detail['total_cubic_feet'];
                    $total_amount += $detail['amount'];
                }

                $rowcount++;
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount, number_format($total_qty,2))->getStyle('G'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount, number_format($total_cubic_meter,2))->getStyle('H'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount, number_format($total_cubic_feet,2))->getStyle('I'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount, number_format($total_amount),2)->getStyle('K'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':H'.$rowcount)->applyFromArray(
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
                $rowcount +=3;
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="PurchaseInvoice|Report.xlsx"');
            header('Cache-Control: max-age=0');
            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $objWriter->save('php://output');
            exit;
        }
        else{

            $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Purchase Invoice Report');
        $pdf->SetSubject('Purchase Invoice Report');

         $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->data['warehouses'] = $this->model['warehouse']->getRow(array('warehouse_id' => $post['warehouse_id']));

        $this->model['partner'] = $this->load->model('common/partner');
        $this->data['partner'] = $this->model['partner']->getRow(array('partner_id' => $post['partner_id']));
        // d($this->data['partner'],true);

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $company_logo,
            'date_from' => $post['date_from'],
            'date_to' => $post['date_to'],
            'warehouse' => $this->data['warehouses']['name'],
            'partner_id' => $this->data['partner']['name'],
            'print' => 'product'
        );
        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10,53,3);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            $pdf->AddPage();
        // set font
        $pdf->SetFont('freesans', '', 8);
        foreach($invoices as $row) {
            $pdf->ln(2);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(60, 6,'Product Name: ' . $row['product_name'], 0, false, 'L', 1, '', 0, false, 'M', 'M');
            $pdf->ln(5);
            $pdf->Cell(60, 6,'Product Code: ' . $row['product_code'], 0, false, 'L', 1, '', 0, false, 'M', 'M');
            $pdf->ln(5);
            $pdf->Cell(60, 6,'Warehouse: ' . $row['warehouse'], 0, false, 'L', 1, '', 0, false, 'M', 'M');
            // $pdf->ln(10);
            $sr =0;
            $total_qty = 0;
            $total_cubic_meter = 0;
            $total_cubic_feet = 0;
            $total_amount = 0;
            // $pdf->Ln(1);
            // d($row,true);
            foreach($row['data'] as $detail) {
                $sr++;
                $pdf->Ln(6);
                $pdf->Cell(10, 6, $sr, 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6, $detail['document_date'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(70, 6, $detail['document_identity'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                // $pdf->Cell(20, 6, $detail['container_no'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                // $pdf->Cell(20, 6, $detail['batch_no'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6, number_format($detail['qty'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6,$detail['unit'], 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6, $detail['discount_amount'], 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6, number_format($detail['rate'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, number_format($detail['amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $total_qty += $detail['qty'];
                $total_cubic_meter += $detail['total_cubic_meter'];
                $total_cubic_feet += $detail['total_cubic_feet'];
                $total_amount += $detail['amount'];
            }
            $pdf->Ln(6);
            $pdf->Cell(95, 6,'', 0, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(15, 6, number_format($total_qty,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(15, 6, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(15, 6, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(15, 6, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 6, number_format($total_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
        }
        //Close and output PDF document
        $pdf->Output('Purchase Invoice Report:'.date('YmdHis').'.pdf', 'I');
    }
    }

    private function pdfContainerWise($rows,$post) {
        $invoices = array();
        foreach($rows as $row) {
            if(!isset($invoices[$row['container_no']])) {
                $invoices[$row['container_no']] = array(
                    'warehouse' => $row['warehouse'],
                    'container_no' => $row['container_no'],
                    'data' => array()
                );
            }
            $invoices[$row['container_no']]['data'][] = $row;
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
        $company_logo = $setting['value'];

        $pdf = new PDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Purchase Invoice Report');
        $pdf->SetSubject('Purchase Invoice Report');

        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->data['warehouses'] = $this->model['warehouse']->getRow(array('warehouse_id' => $post['warehouse_id']));

        $this->model['partner'] = $this->load->model('common/partner');
        $this->data['partner'] = $this->model['partner']->getRow(array('partner_id' => $post['partner_id']));
        // d($this->data['partner'],true);

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
        $pdf->SetMargins(10,60,3);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // set font
        $pdf->SetFont('freesans', '', 8);
        foreach($invoices as $row) {
            $pdf->AddPage();
            $pdf->Cell(0,10,'Container No: ' . $row['container_no']);

            $pdf->ln(10);
            $pdf->Cell(  7, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell( 15, 7, 'Doc. Date', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell( 25, 7, 'Doc. No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell( 15, 7, 'Warehouse', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(120, 7, 'Product', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            // $pdf->Cell( 15, 7, 'Batch', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell( 15, 7, 'Quantity', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell( 14, 7, 'Unit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell( 14, 7, 'Feet', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell( 10, 7, 'Rate', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell( 20, 7, 'Total Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');

            $sr =0;
            $total_qty = 0;
            $total_cubic_meter = 0;
            $total_cubic_feet = 0;
            $total_amount = 0;
            $pdf->Ln(1);
            d($row,true);
            foreach($row['data'] as $detail) {
                $sr++;
                $pdf->Ln(6);
                $pdf->Cell(7, 6, $sr, 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6, $detail['document_date'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, $detail['document_identity'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6, $detail['warehouse'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(120, 6, $detail['product_name'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                // $pdf->Cell(15, 6, $detail['batch_no'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
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
        $pdf->Output('Purchase Invoice Report:'.date('YmdHis').'.pdf', 'I');
    }

    public function printExcelReport()
    {
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
        if(isset($post['product_id']) && $post['product_id'] != '') {
            $filter[] = "`product_id` = '".$post['product_id']."'";
        }
        if(isset($post['warehouse_id']) && $post['warehouse_id'] != '') {
            $filter[] = "`warehouse_id` = '".$post['warehouse_id']."'";
        }
        $where = implode(' AND ', $filter);
        $this->model['purchase_invoice_detail'] = $this->load->model('inventory/purchase_invoice_detail');
        $rows = $this->model['purchase_invoice_detail']->getRows($where, array('created_at'));
        if($post['group_by']=='document') {
            $this->excelDocumentWise($rows,$post);
        } elseif($post['group_by']=='partner') {
            $this->excelPartnerWise($rows,$post);
        } elseif($post['group_by']=='warehouse') {
            $this->excelWarehouseWise($rows,$post);
        } elseif($post['group_by']=='product') {
            $this->excelProductWise($rows,$post);
        }
    }

    private function excelDocumentWise($rows) {
        // d($rows, true);
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

        // d($invoices,true);

        $session = $this->session->data;
        $company_logo = $setting['value'];

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);

        // changing title from sale tax invoice to sale invoice for service associates
        $objPHPExcel->getProperties()
            ->setCreator("Hira")
            ->setLastModifiedBy("Hira")
            ->setTitle("Purchase Report");

        $objPHPExcel->data = array(
            'company_name' => $session['company_name'],
            'report_name' => 'Purchase Report',

        );

        $rowCount = 1;

        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":H".($rowCount));
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

        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":H".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Purchase Report');
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
        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":H".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, '');
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->getFill('ebebeb');
        $rowCount++;

        foreach ($invoices as $key => $value)
        {
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Doc. Date')->getStyle('A'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'Doc. Identity')->getStyle('B'.$rowCount)->getFont()->setBold( true );
            $rowCount++;
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $value['document_date'])->getStyle('A'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $value['document_identity'])->getStyle('B'.$rowCount)->getFont()->setBold( true );
            $rowCount++;

            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(17);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(17);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(17);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(17);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(17);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(17);

            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Product')->getStyle('A'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'Quantity')->getStyle('C'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Unit')->getStyle('D'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'Rate')->getStyle('E'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'Amount')->getStyle('F'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'Discount')->getStyle('G'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, 'Tax Amount')->getStyle('H'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, 'Total Amount')->getStyle('I'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':H'.$rowCount)->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    )
                )
            );
            $rowCount++;
            foreach($value['data'] as $key_1 => $value_1)
            {
                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $value_1['product_name']);
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $value_1['qty']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value_1['unit']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value_1['rate']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value_1['amount']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $value_1['discount']);
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $value_1['tax_amount']);
                $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $value_1['total_amount']);
                $rowCount++;
            }
            $rowCount++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Purchase Invoice Report.xlsx"');
        header('Cache-Control: max-age=0');
        //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        //$objWriter->save('some_excel_file.xlsx');
        $objWriter->save('php://output');
        exit;

    }

    private function excelPartnerWise($rows) {
        //d($rows, true);
        $invoices = array();
        foreach($rows as $row) {
            if(!isset($invoices[$row['partner_type'].'-'.$row['partner_id']])) {
                $invoices[$row['partner_type'].'-'.$row['partner_id']] = array(
                    'partner_type' => $row['partner_type'],
                    'partner_id' => $row['partner_id'],
                    'data' => array()
                );
            }
            $invoices[$row['partner_type'].'-'.$row['partner_id']]['data'][] = $row;
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

        // d($invoices,true);

        $session = $this->session->data;
        $company_logo = $setting['value'];

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);

        $objPHPExcel->getProperties()
            ->setCreator("Hira")
            ->setLastModifiedBy("Hira")
            ->setTitle("Sale Report");

        $objPHPExcel->data = array(
            'company_name' => $session['company_name'],
            'report_name' => 'Purchase Report',

        );

        $rowCount = 1;

        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":J".($rowCount));
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

        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":J".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Purchase Report');
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

        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":J".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, '');
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->getFill('ebebeb');
        $rowCount ++;

        foreach ($invoices as $key => $value)
        {
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Partner Name')->getStyle('A'.$rowCount)->getFont()->setBold( true );
            $rowCount++;
            $this->model['partner'] = $this->load->model('common/partner');
            $partner_name = $this->model['partner']->getRow(array('partner_id' => $value['partner_id']));
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $partner_name['name'])->getStyle('A'.$rowCount)->getFont()->setBold( true );
            $rowCount++;

            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(17);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(17);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(17);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(17);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(17);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(17);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(17);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(17);


            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Doc. Date')->getStyle('A'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'Doc. No.')->getStyle('B'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Product')->getStyle('C'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'Quantity')->getStyle('D'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'Unit')->getStyle('E'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'Rate')->getStyle('F'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, 'Amount')->getStyle('G'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, 'Discount')->getStyle('H'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, 'Tax Amount')->getStyle('I'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, 'Total Amount')->getStyle('J'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':J'.$rowCount)->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    )
                )
            );
            $rowCount++;
            foreach($value['data'] as $key_1 => $value_1)
            {
                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $value_1['document_date']);
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $value_1['document_identity']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value_1['product_name']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value_1['qty']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value_1['unit']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $value_1['rate']);
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $value_1['amount']);
                $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $value_1['discount_amount']);
                $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $value_1['tax_amount']);
                $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, $value_1['total_amount']);
                $rowCount++;
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Purchase Invoice Report.xlsx"');
        header('Cache-Control: max-age=0');
        //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        //$objWriter->save('some_excel_file.xlsx');
        $objWriter->save('php://output');
        exit;
    }

    private function excelWarehouseWise($rows) {
        // d($rows, true);
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

        // d($invoices,true);

        $session = $this->session->data;
        $company_logo = $setting['value'];

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);

        $objPHPExcel->getProperties()
            ->setCreator("Hira")
            ->setLastModifiedBy("Hira")
            ->setTitle("Sale Report");

        $objPHPExcel->data = array(
            'company_name' => $session['company_name'],
            'report_name' => 'Purchase Report',

        );

        $rowCount = 1;

        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":J".($rowCount));
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

        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":J".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Purchase Report');
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

        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":J".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, '');
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->getFill('ebebeb');
        $rowCount ++;

        foreach ($invoices as $key => $value)
        {
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Warehouse')->getStyle('A'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $value['warehouse'])->getStyle('B'.$rowCount)->getFont()->setBold( true );
            $rowCount++;

            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(17);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(17);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(17);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(17);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(17);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(17);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(17);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(17);


            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Doc. Date')->getStyle('A'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'Doc. No.')->getStyle('B'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Product')->getStyle('C'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'Quantity')->getStyle('D'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'Unit')->getStyle('E'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'Rate')->getStyle('F'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, 'Amount')->getStyle('G'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, 'Discount')->getStyle('H'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, 'Tax Amount')->getStyle('I'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, 'Total Amount')->getStyle('J'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':J'.$rowCount)->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    )
                )
            );
            $rowCount++;
            foreach($value['data'] as $key_1 => $value_1)
            {
                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $value_1['document_date']);
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $value_1['document_identity']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value_1['product_name']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value_1['qty']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value_1['unit']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $value_1['rate']);
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $value_1['amount']);
                $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $value_1['discount_amount']);
                $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $value_1['tax_amount']);
                $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, $value_1['total_amount']);
                $rowCount++;
            }
            $rowCount++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Purchase Invoice Report.xlsx"');
        header('Cache-Control: max-age=0');
        //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        //$objWriter->save('some_excel_file.xlsx');
        $objWriter->save('php://output');
        exit;

    }

    private function excelProductWise($rows) {
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

        $session = $this->session->data;
        $company_logo = $setting['value'];

        // d($invoices,true);

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);

        $objPHPExcel->getProperties()
            ->setCreator("Hira")
            ->setLastModifiedBy("Hira")
            ->setTitle("Purchase Report");

        $objPHPExcel->data = array(
            'company_name' => $session['company_name'],
            'report_name' => 'Purchase Report',

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
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Purchase Report');
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

        foreach ($invoices as $key => $value)
        {
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Product Name')->getStyle('A'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $value['product_name'])->getStyle('A'.$rowCount)->getFont()->setBold( true );
            $rowCount++;
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Product Code')->getStyle('A'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $value['product_code'])->getStyle('A'.$rowCount)->getFont()->setBold( true );
            $rowCount++;

            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(17);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(17);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(17);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(17);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(17);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(17);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(17);


            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Doc. Date')->getStyle('A'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'Doc. No.')->getStyle('B'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Partner')->getStyle('C'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'Quantity')->getStyle('D'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'Unit')->getStyle('E'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'Rate')->getStyle('F'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, 'Amount')->getStyle('G'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, 'Discount')->getStyle('H'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, 'Total Amount')->getStyle('J'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':I'.$rowCount)->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    )
                )
            );
            $rowCount++;
            foreach($value['data'] as $key_1 => $value_1)
            {
                $this->model['partner'] = $this->load->model('common/partner');
                $partner_name = $this->model['partner']->getRow(array('partner_id' => $value_1['partner_id']));
                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $value_1['document_date']);
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $value_1['document_identity']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $partner_name['name']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value_1['qty']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value_1['unit']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $value_1['rate']);
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $value_1['amount']);
                $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $value_1['discount_amount']);
                $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $value_1['total_amount']);
                $rowCount++;
            }
            $rowCount++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Purchase Invoice Report.xlsx"');
        header('Cache-Control: max-age=0');
        //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        //$objWriter->save('some_excel_file.xlsx');
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
        if(!empty($this->data['warehouse']))
        {
            $this->Cell(0, 10, 'Warehouse: '.$this->data['warehouse'], 0, false, 'C', 0, '', 0, false, 'M', 'M');    
        }
        if(!empty($this->data['partner_id']))
        {
            $this->Cell(0, 10,' Partner: '.$this->data['partner_id'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        }
        
        if($this->data['print'] == 'document')
        {
            $this->SetFont('freesans', '', 8);
            $this->ln(7);
            $this->Cell(7, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(100, 7, 'Product', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            // $pdf->Cell(20, 7, 'Container No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            // $pdf->Cell(20, 7, 'Batch No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(15, 7, 'Quantity', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(14, 7, 'Unit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(14, 7, 'Discount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(10, 7, 'Rate', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(20, 7, 'Total Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        }
        if($this->data['print'] == 'partner')
        {
            $this->SetFont('freesans', '', 8);
            $this->ln(7);
            $this->Cell(7, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(15, 7, 'Doc. Date', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, 'Doc. No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(150, 7, 'Product', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            // $pdf->Cell(15, 7, 'Container', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            // $pdf->Cell(15, 7, 'Batch', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(15, 7, 'Quantity', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(14, 7, 'Unit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(14, 7, 'Discount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(10, 7, 'Rate', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(20, 7, 'Total Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        }
        if($this->data['print'] == 'warehouse')
        {
            $this->SetFont('freesans', '', 8);
            $this->ln(7);
            $this->Cell(7, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(15, 7, 'Doc. Date', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, 'Doc. No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(150, 7, 'Product', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            // $pdf->Cell(15, 7, 'Container', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            // $pdf->Cell(15, 7, 'Batch', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(15, 7, 'Quantity', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(14, 7, 'Unit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(14, 7, 'Discount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(10, 7, 'Rate', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(20, 7, 'Total Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        }
        if($this->data['print'] == 'product')
        {
            $this->SetFont('freesans', '', 8);
            $this->ln(7);
            $this->Cell(10, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(15, 7, 'Doc. Date', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(70, 7, 'Doc. No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            // $pdf->Cell(20, 7, 'Container No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            // $pdf->Cell(20, 7, 'Batch No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(15, 7, 'Quantity', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(15, 7, 'Unit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(15, 7, 'Discount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(15, 7, 'Rate', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, 'Total Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        }
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