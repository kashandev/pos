<?php

class ControllerReportSaleAnalysisReport extends HController {

    protected function getAlias() {
        return 'report/sale_analysis_report';
    }

    protected function getList() {
        parent::getList();

        $this->data['partner_types'] = $this->session->data['partner_types'];

        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
        $this->data['href_get_product_json'] = $this->url->link($this->getAlias() . '/getProductJson', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['href_get_partner_json'] = $this->url->link($this->getAlias() . '/getPartnerJson', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['href_get_detail_report'] = $this->url->link($this->getAlias() .'/getDetailReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_print_report'] = $this->url->link($this->getAlias() .'/printReport', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['href_print_excel_report'] = $this->url->link($this->getAlias() .'/printExcelReport', 'token=' . $this->session->data['token'], 'SSL');
        
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

    public function getPartnerJson() {
        $search = $this->request->post['q'];
        $page = $this->request->post['page'];

        $this->model['partner'] = $this->load->model('common/partner');
        $rows = $this->model['partner']->getPartnerJson($search, $page, 25, ['partner_type_id' => 2]);

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
        if($post['partner_id']) {
            $arrWhere[] = "`partner_id` IN ('". implode("','", $post['partner_id']) ."')";
        }
        if($post['warehouse_id']){
            $arrWhere[] = "`warehouse_id` = '" . $post['warehouse_id'] . "'";
        }
        if($post['product_id']){
            $arrWhere[] = "`product_id` IN ('" . implode("','", $post['product_id']) . "')";
        }
        if($post['container_no'])
            $arrWhere[] = "`container_no` LIKE '" . $post['container_no'] . "%'";

        $where = implode(' AND ', $arrWhere);


        $rows = $this->model['sale_invoice_detail']->getRows($where);
        $html = '';
        foreach($rows as $row) {
            $href = $this->url->link($row['route'].'/update',$row['primary_key_field'].'='.$row['primary_key_value'].'&token='.$this->session->data['token']);
            $html .= '<tr>';
            $html .= '<td data-sort="'.$row['document_date'].'" >'.stdDate($row['document_date']).'</td>';
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
            $filter[] = "`partner_id` IN ('". implode("','", $post['partner_id']) ."')";
        }
        if($post['warehouse_id']){
            $filter[] = "`warehouse_id` = '" . $post['warehouse_id'] . "'";
        }
        if($post['product_id']){
            $filter[] = "`product_id` IN ('" . implode("','", $post['product_id']) . "')";
        }
        $where = implode(' AND ', $filter);
        $this->model['sale_tax_invoice_detail'] = $this->load->model('inventory/sale_tax_invoice_detail');
        $rows = $this->model['sale_tax_invoice_detail']->getRows($where, array('created_at'));
        // d($rows, true);
        if($post['group_by']=='document') {
            $this->excelDocumentWise($rows);
        } elseif($post['group_by']=='partner') {
            $this->excelPartnerWise($rows);
        } elseif($post['group_by']=='warehouse') {
            $this->excelWarehouseWise($rows);
        } elseif($post['group_by']=='product') {
            $this->excelProductWise($rows);
        }
    }


    private function excelDocumentWise($rows) {
        //d($rows, true);
        $invoices = array();
        $this->model['stock'] = $this->load->model('common/stock_ledger');

        foreach($rows as $row) {
            if(!isset($invoices[$row['document_date']])) {
                $invoices[$row['document_date']] = array(
                    'document_date' => $row['document_date'],
                    'document_identity' => $row['document_identity'],
                    'data' => array()
                );
            }
            $where = [];
            $where[] = "`company_id`   = '{$this->session->data['company_id']}'";
            $where[] = "`company_branch_id`   = '{$this->session->data['company_branch_id']}'";
            $where[] = "`fiscal_year_id`   = '{$this->session->data['fiscal_year_id']}'";
            $where[] = "`product_id`   = '{$row['product_id']}'";
            $where[] = "`warehouse_id` = '{$row['warehouse_id']}'";
            $where[] = "`document_date` = '{$row['document_date']}'";
            $where[] = "`document_identity` = '{$row['ref_document_identity']}'";
            $where = implode(' AND ', $where);
            $stock = $this->model['stock']->getStock($where);
            $row['stock_qty'] = $stock['stock_qty'];
            // $row['avg_stock_rate'] = $stock['avg_stock_rate'];
            // Change Avg stock rate value to detail cog rate (T)
            $row['avg_stock_rate'] = $row['cog_rate'];
            $row['cog_amount'] = $row['cog_amount'];
            $invoices[$row['document_date']]['data'][] = $row;
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
            ->setCreator("Muhammad Salman")
            ->setLastModifiedBy("Muhammad Salman")
            ->setTitle("Sales - Cost Analysis Report");

        $objPHPExcel->data = array(
            'company_name' => $session['company_name'],
            'report_name' => 'Sales - Cost Analysis Report',

        );

        $rowCount = 1;
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(8);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(10);


        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":B".($rowCount+1));
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $session['company_name']);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount . ':B'.$rowCount)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                ),
                'font' => array(
                    'size' => 12,
                    'bold' => true
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'FFFFFF')
                )
            )
        );

        $objPHPExcel->getActiveSheet()->mergeCells('C'.($rowCount).":G".($rowCount+1));
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Sales - Cost Analysis Report');
        $objPHPExcel->getActiveSheet()->getStyle('C'.$rowCount . ':G'.$rowCount)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                ),
                'font' => array(
                    'size' => 15,
                    'bold' => true
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'FFFFFF')
                )
            )
        );

        $objPHPExcel->getActiveSheet()->mergeCells('H'.($rowCount).":I".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, 'From: ' . $this->request->post['date_from']);
        $objPHPExcel->getActiveSheet()->mergeCells('J'.($rowCount).":L".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, 'Print Date: ' . date(STD_DATETIME));
        $objPHPExcel->getActiveSheet()->getStyle('H'.$rowCount . ':L'.$rowCount)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
                ),
                'font' => array(
                    'size' => 12
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'ffffff')
                )
            )
        );

        
        $rowCount ++;
        $objPHPExcel->getActiveSheet()->mergeCells('H'.($rowCount).":I".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, 'To: ' . $this->request->post['date_to']);
        $objPHPExcel->getActiveSheet()->mergeCells('J'.($rowCount).":L".($rowCount));
        $objPHPExcel->getActiveSheet()->getStyle('H'.$rowCount . ':L'.$rowCount)->applyFromArray(
            array(
                'alignment' => array(   
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
                ),
                'font' => array(
                    'size' => 12
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'FFFFFF')
                )
            )
        );

        $rowCount++; 
        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":E".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'TRANSACTIONS');
        $objPHPExcel->getActiveSheet()->mergeCells('F'.($rowCount).":G".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'SALES');
        $objPHPExcel->getActiveSheet()->mergeCells('H'.($rowCount).":I".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, 'COGS');
        $objPHPExcel->getActiveSheet()->mergeCells('J'.($rowCount).":L".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, 'GROSS MARGIN ANALYSIS');
        $objPHPExcel->getActiveSheet()->getStyle('A'.($rowCount).":L".($rowCount))->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                ),
                'font' => array(
                    'size' => 12,
                    'bold' => true
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'EEEEEE')
                ),
                'borders' => array(
                    'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                )
            )
        );


        $rowCount++;
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Date');
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'Document No');
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Product');
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'Customer');
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'Qty');
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'Sale Price');
        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, 'Total Sale');
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, 'Avg. U/Cost');
        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, 'Total Cost');
        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, 'Gross Profit');
        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount, 'GM ROI');
        $objPHPExcel->getActiveSheet()->SetCellValue('L'.$rowCount, 'GM SALES');
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount . ':L'.$rowCount)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                ),
                'font' => array(
                    'size' => 10,
                    'bold' => true
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'EEEEEE')
                ),
                'borders' => array(
                    'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                )
            )
        );

        $grand_total_sale_qty       = 0;
        $grand_total_sale           = 0;
        $grand_total_cogs           = 0;
        $grand_total_gross_profit   = 0;

        foreach ($invoices as $key => $value)
        {
            $rowCount++;
            $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":L".($rowCount));
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Document Date: ' . stdDate($value['document_date']))->getStyle('A'.$rowCount)->getFont()->setBold( true );
            $rowCount++;

            $total_sale_qty     = 0;
            $total_sale         = 0;
            $total_cogs         = 0;
            $total_gross_profit = 0;
            
            foreach($value['data'] as $key_1 => $value_1)
            {
                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, stdDate($value_1['document_date']));
                $objPHPExcel->getActiveSheet()->getStyle('A'.($rowCount))->applyFromArray(
                    array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                        ),
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        )
                    )
                );

                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $value_1['document_identity']);
                $objPHPExcel->getActiveSheet()->getStyle('B'.($rowCount))->applyFromArray(
                    array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                        ),
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        )
                    )
                );
                
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value_1['product_name']);
                $objPHPExcel->getActiveSheet()->getStyle('C'.($rowCount))->applyFromArray(
                    array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                        ),
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        )
                    )
                );

                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value_1['partner_name']);
                $objPHPExcel->getActiveSheet()->getStyle('D'.($rowCount))->applyFromArray(
                    array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                        ),
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        )
                    )
                );

                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, number_format($value_1['qty'], 0));
                $objPHPExcel->getActiveSheet()->getStyle('E'.($rowCount))->applyFromArray(
                    array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                        ),
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        )
                    )
                );
                // $cog_amount = ($value_1['avg_stock_rate']*$value_1['qty']);
                $cog_amount = ($value_1['cog_amount']);
                $gross_profit = ($value_1['amount']-$cog_amount);
                $gm_roi = (($gross_profit/$cog_amount)*100);
                $sale_gm = (($gross_profit/$value_1['amount'])*100);
                $gm_roi = (strtolower($gm_roi) != 'inf') ? $gm_roi : 0;
                $sale_gm = (strtolower($sale_gm) != 'inf') ? $sale_gm : 0;

                if( $cog_amount <= 0 )
                {
                    $gm_roi = 100;
                    $sale_gm = 100;
                }


                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, number_format($value_1['rate'],2));
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, number_format($value_1['amount'],2));                
                $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, number_format($value_1['avg_stock_rate'],2));
                $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, number_format($cog_amount,2));
                $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, number_format($gross_profit,2));
                $objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount, number_format($gm_roi,2) . '%');
                $objPHPExcel->getActiveSheet()->SetCellValue('L'.$rowCount, number_format($sale_gm,2) . '%');
                $objPHPExcel->getActiveSheet()->getStyle('F'.($rowCount) . ':L'.($rowCount))->applyFromArray(
                    array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                        ),
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        )
                    )
                );


                $total_sale_qty     += $value_1['qty'];
                $total_sale         += $value_1['amount'];
                $total_cogs         += $cog_amount;
                $total_gross_profit += $gross_profit;
                
                $rowCount++;
            }

            $total_gm_roi = (($total_gross_profit/$total_cogs)*100);
            $total_gm_sale = (($total_gross_profit/$total_sale)*100);
            $total_gm_roi = (strtolower($total_gm_roi) != 'inf')?$total_gm_roi:0;
            $total_gm_sale = (strtolower($total_gm_sale) != 'inf')?$total_gm_sale:0;

            if( $total_cogs <= 0 )
            {
                $total_gm_roi = 100;
                $total_gm_sale = 100;
            }

            $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":D".($rowCount));
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Total');
            $objPHPExcel->getActiveSheet()->getStyle('A'.($rowCount) . ':D'.($rowCount))->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    ),
                    'font' => array(
                        'bold' => true
                    )
                )
            );

            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, number_format($total_sale_qty, 2));
            $objPHPExcel->getActiveSheet()->getStyle('E'.($rowCount))->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    ),
                    'font' => array(
                        'bold' => true
                    )
                )
            );

            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, number_format($total_sale, 2));
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, number_format($total_cogs, 2));
            $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, number_format($total_gross_profit, 2));
            $objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount, number_format($total_gm_roi,2) . '%');
            $objPHPExcel->getActiveSheet()->SetCellValue('L'.$rowCount, number_format($total_gm_sale,2) . '%');
            $objPHPExcel->getActiveSheet()->getStyle('F'.($rowCount) . ':L'.($rowCount))->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    ),
                    'font' => array(
                        'bold' => true
                    )
                )
            );

            $rowCount++;


            /*Grand Total*/
            $grand_total_sale_qty       += $total_sale_qty; 
            $grand_total_sale           += $total_sale;
            $grand_total_cogs           += $total_cogs;
            $grand_total_gross_profit   += $total_gross_profit;

        }

        $grand_total_gm_roi = (($grand_total_gross_profit/$grand_total_cogs)*100);
        $grand_total_gm_sale = (($grand_total_gross_profit/$grand_total_sale)*100);
        $grand_total_gm_roi = (strtolower($grand_total_gm_roi) != 'inf')?$grand_total_gm_roi:0;
        $grand_total_gm_sale = (strtolower($grand_total_gm_sale) != 'inf')?$grand_total_gm_sale:0;

        if( $grand_total_cogs <= 0 )
        {
            $grand_total_gm_roi = 100;
            $grand_total_gm_sale = 100;
        }



        $rowCount++;
        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":D".($rowCount));
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Grand Total');
            $objPHPExcel->getActiveSheet()->getStyle('A'.($rowCount) . ':D'.($rowCount))->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    ),
                    'font' => array(
                        'bold' => true
                    )
                )
            );

            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, number_format($grand_total_sale_qty, 2));
            $objPHPExcel->getActiveSheet()->getStyle('E'.($rowCount))->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    ),
                    'font' => array(
                        'bold' => true
                    )
                )
            );

            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, number_format($grand_total_sale, 2));
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, number_format($grand_total_cogs, 2));
            $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, number_format($grand_total_gross_profit, 2));
            $objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount, number_format($grand_total_gm_roi,2) . '%');
            $objPHPExcel->getActiveSheet()->SetCellValue('L'.$rowCount, number_format($grand_total_gm_sale,2) . '%');
            $objPHPExcel->getActiveSheet()->getStyle('F'.($rowCount) . ':L'.($rowCount))->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    ),
                    'font' => array(
                        'bold' => true
                    )
                )
            );



        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="sales_cost_analysis_report.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save('php://output');
        exit;

    }

    private function excelPartnerWise($rows) {
        //d($rows, true);
        $invoices = array();
        $this->model['stock'] = $this->load->model('common/stock_ledger');

        foreach($rows as $row) {
            if(!isset($invoices[$row['partner_type'].'-'.$row['partner_name']])) {
                $invoices[$row['partner_type'].'-'.$row['partner_name']] = array(
                    'partner_type' => $row['partner_type'],
                    'partner_name' => $row['partner_name'],
                    'data' => array()
                );
            }

            $where = [];
            $where[] = "`company_id`   = '{$this->session->data['company_id']}'";
            $where[] = "`company_branch_id`   = '{$this->session->data['company_branch_id']}'";
            $where[] = "`fiscal_year_id`   = '{$this->session->data['fiscal_year_id']}'";
            $where[] = "`product_id`   = '{$row['product_id']}'";
            $where[] = "`warehouse_id` = '{$row['warehouse_id']}'";
            $where[] = "`document_date` = '{$row['document_date']}'";
            $where[] = "`document_identity` = '{$row['ref_document_identity']}'";
            $where = implode(' AND ', $where);
            $stock = $this->model['stock']->getStock($where);
            $row['stock_qty'] = $stock['stock_qty'];
            // $row['avg_stock_rate'] = $stock['avg_stock_rate'];
            // Change Avg stock rate value to detail cog rate (T)
            $row['avg_stock_rate'] = $row['cog_rate'];
            $row['cog_amount'] = $row['cog_amount'];
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

        // d($invoices,true);

        $session = $this->session->data;
        $company_logo = $setting['value'];

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);

        // changing title from sale tax invoice to sale invoice for service associates
        $objPHPExcel->getProperties()
            ->setCreator("Muhammad Salman")
            ->setLastModifiedBy("Muhammad Salman")
            ->setTitle("Sales - Cost Analysis Report");

        $objPHPExcel->data = array(
            'company_name' => $session['company_name'],
            'report_name' => 'Sales - Cost Analysis Report',

        );

        $rowCount = 1;

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(8);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(10);


        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":B".($rowCount+1));
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $session['company_name']);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount . ':B'.$rowCount)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                ),
                'font' => array(
                    'size' => 12,
                    'bold' => true
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'FFFFFF')
                )
            )
        );

        $objPHPExcel->getActiveSheet()->mergeCells('C'.($rowCount).":G".($rowCount+1));
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Sales - Cost Analysis Report');
        $objPHPExcel->getActiveSheet()->getStyle('C'.$rowCount . ':G'.$rowCount)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                ),
                'font' => array(
                    'size' => 15,
                    'bold' => true
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'FFFFFF')
                )
            )
        );

        $objPHPExcel->getActiveSheet()->mergeCells('H'.($rowCount).":I".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, 'From: ' . $this->request->post['date_from']);
        $objPHPExcel->getActiveSheet()->mergeCells('J'.($rowCount).":L".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, 'Print Date: ' . date(STD_DATETIME));
        $objPHPExcel->getActiveSheet()->getStyle('H'.$rowCount . ':L'.$rowCount)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
                ),
                'font' => array(
                    'size' => 12
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'ffffff')
                )
            )
        );

        
        $rowCount ++;
        $objPHPExcel->getActiveSheet()->mergeCells('H'.($rowCount).":I".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, 'To: ' . $this->request->post['date_to']);
        $objPHPExcel->getActiveSheet()->mergeCells('I'.($rowCount).":L".($rowCount));
        $objPHPExcel->getActiveSheet()->getStyle('H'.$rowCount . ':L'.$rowCount)->applyFromArray(
            array(
                'alignment' => array(   
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
                ),
                'font' => array(
                    'size' => 12
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'FFFFFF')
                )
            )
        );

        $rowCount++; 
        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":E".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'TRANSACTIONS');
        $objPHPExcel->getActiveSheet()->mergeCells('F'.($rowCount).":G".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'SALES');
        $objPHPExcel->getActiveSheet()->mergeCells('H'.($rowCount).":I".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, 'COGS');
        $objPHPExcel->getActiveSheet()->mergeCells('J'.($rowCount).":L".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, 'GROSS MARGIN ANALYSIS');
        $objPHPExcel->getActiveSheet()->getStyle('A'.($rowCount).":L".($rowCount))->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                ),
                'font' => array(
                    'size' => 12,
                    'bold' => true
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'EEEEEE')
                ),
                'borders' => array(
                    'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                )
            )
        );


        $rowCount++;
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Date');
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'Document No');
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Product');
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'Customer');
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'Qty');
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'Sale Price');
        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, 'Total Sale');
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, 'Avg. U/Cost');
        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, 'Total Cost');
        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, 'Gross Profit');
        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount, 'GM ROI');
        $objPHPExcel->getActiveSheet()->SetCellValue('L'.$rowCount, 'GM SALES');
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount . ':L'.$rowCount)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                ),
                'font' => array(
                    'size' => 10,
                    'bold' => true
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'EEEEEE')
                ),
                'borders' => array(
                    'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                )
            )
        );

        $grand_total_sale_qty       = 0;
        $grand_total_sale           = 0;
        $grand_total_cogs           = 0;
        $grand_total_gross_profit   = 0;

        foreach ($invoices as $key => $value)
        {
            $rowCount++;
            $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":L".($rowCount));
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Customer: ' . $value['partner_name'])->getStyle('A'.$rowCount)->getFont()->setBold( true );
            $rowCount++;

            $total_sale_qty     = 0;
            $total_sale         = 0;
            $total_cogs         = 0;
            $total_gross_profit = 0;
            
            foreach($value['data'] as $key_1 => $value_1)
            {
                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, stdDate($value_1['document_date']));
                $objPHPExcel->getActiveSheet()->getStyle('A'.($rowCount))->applyFromArray(
                    array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                        ),
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        )
                    )
                );

                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $value_1['document_identity']);
                $objPHPExcel->getActiveSheet()->getStyle('B'.($rowCount))->applyFromArray(
                    array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                        ),
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        )
                    )
                );
                
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value_1['product_name']);
                $objPHPExcel->getActiveSheet()->getStyle('C'.($rowCount))->applyFromArray(
                    array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                        ),
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        )
                    )
                );

                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value_1['partner_name']);
                $objPHPExcel->getActiveSheet()->getStyle('D'.($rowCount))->applyFromArray(
                    array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                        ),
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        )
                    )
                );

                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, number_format($value_1['qty'], 0));
                $objPHPExcel->getActiveSheet()->getStyle('E'.($rowCount))->applyFromArray(
                    array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                        ),
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        )
                    )
                );
                // $cog_amount = ($value_1['avg_stock_rate']*$value_1['qty']);
                $cog_amount = ($value_1['cog_amount']);
                $gross_profit = ($value_1['amount']-$cog_amount);
                $gm_roi = (($gross_profit/$cog_amount)*100);
                $sale_gm = (($gross_profit/$value_1['amount'])*100);
                $gm_roi = (strtolower($gm_roi) != 'inf') ? $gm_roi : 0;
                $sale_gm = (strtolower($sale_gm) != 'inf') ? $sale_gm : 0;

                if( $cog_amount <= 0 )
                {
                    $gm_roi = 100;
                    $sale_gm = 100;
                }


                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, number_format($value_1['rate'],2));
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, number_format($value_1['amount'],2));                
                $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, number_format($value_1['avg_stock_rate'],2));
                $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, number_format($cog_amount,2));
                $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, number_format($gross_profit,2));
                $objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount, number_format($gm_roi,2) . '%');
                $objPHPExcel->getActiveSheet()->SetCellValue('L'.$rowCount, number_format($sale_gm,2) . '%');
                $objPHPExcel->getActiveSheet()->getStyle('F'.($rowCount) . ':L'.($rowCount))->applyFromArray(
                    array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                        ),
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        )
                    )
                );


                $total_sale_qty     += $value_1['qty'];
                $total_sale         += $value_1['amount'];
                $total_cogs         += $cog_amount;
                $total_gross_profit += $gross_profit;
                
                $rowCount++;
            }

            $total_gm_roi = (($total_gross_profit/$total_cogs)*100);
            $total_gm_sale = (($total_gross_profit/$total_sale)*100);
            $total_gm_roi = (strtolower($total_gm_roi) != 'inf')?$total_gm_roi:0;
            $total_gm_sale = (strtolower($total_gm_sale) != 'inf')?$total_gm_sale:0;

            if( $total_cogs <= 0 )
            {
                $total_gm_roi = 100;
                $total_gm_sale = 100;
            }


            $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":D".($rowCount));
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Total');
            $objPHPExcel->getActiveSheet()->getStyle('A'.($rowCount) . ':D'.($rowCount))->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    ),
                    'font' => array(
                        'bold' => true
                    )
                )
            );

            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, number_format($total_sale_qty, 2));
            $objPHPExcel->getActiveSheet()->getStyle('E'.($rowCount))->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    ),
                    'font' => array(
                        'bold' => true
                    )
                )
            );

            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, number_format($total_sale, 2));
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, number_format($total_cogs, 2));
            $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, number_format($total_gross_profit, 2));
            $objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount, number_format($total_gm_roi,2) . '%');
            $objPHPExcel->getActiveSheet()->SetCellValue('L'.$rowCount, number_format($total_gm_sale,2) . '%');
            $objPHPExcel->getActiveSheet()->getStyle('F'.($rowCount) . ':L'.($rowCount))->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    ),
                    'font' => array(
                        'bold' => true
                    )
                )
            );

            $rowCount++;


            /*Grand Total*/
            $grand_total_sale_qty       += $total_sale_qty; 
            $grand_total_sale           += $total_sale;
            $grand_total_cogs           += $total_cogs;
            $grand_total_gross_profit   += $total_gross_profit;

        }

        $grand_total_gm_roi = (($grand_total_gross_profit/$grand_total_cogs)*100);
        $grand_total_gm_sale = (($grand_total_gross_profit/$grand_total_sale)*100);
        $grand_total_gm_roi = (strtolower($grand_total_gm_roi) != 'inf')?$grand_total_gm_roi:0;
        $grand_total_gm_sale = (strtolower($grand_total_gm_sale) != 'inf')?$grand_total_gm_sale:0;

        if( $grand_total_cogs <= 0 )
        {
            $grand_total_gm_roi = 100;
            $grand_total_gm_sale = 100;
        }

        $rowCount++;
        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":D".($rowCount));
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Grand Total');
            $objPHPExcel->getActiveSheet()->getStyle('A'.($rowCount) . ':D'.($rowCount))->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    ),
                    'font' => array(
                        'bold' => true
                    )
                )
            );

            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, number_format($grand_total_sale_qty, 2));
            $objPHPExcel->getActiveSheet()->getStyle('E'.($rowCount))->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    ),
                    'font' => array(
                        'bold' => true
                    )
                )
            );

            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, number_format($grand_total_sale, 2));
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, number_format($grand_total_cogs, 2));
            $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, number_format($grand_total_gross_profit, 2));
            $objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount, number_format($grand_total_gm_roi,2) . '%');
            $objPHPExcel->getActiveSheet()->SetCellValue('L'.$rowCount, number_format($grand_total_gm_sale,2) . '%');
            $objPHPExcel->getActiveSheet()->getStyle('F'.($rowCount) . ':L'.($rowCount))->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    ),
                    'font' => array(
                        'bold' => true
                    )
                )
            );



        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="sales_cost_analysis_report.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save('php://output');
        exit;
    }


    private function excelProductWise($rows) {
        // d($rows, true);
        $invoices = array();
        $this->model['stock'] = $this->load->model('common/stock_ledger');

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
            $where = [];
            $where[] = "`company_id`   = '{$this->session->data['company_id']}'";
            $where[] = "`company_branch_id`   = '{$this->session->data['company_branch_id']}'";
            $where[] = "`fiscal_year_id`   = '{$this->session->data['fiscal_year_id']}'";
            $where[] = "`product_id`   = '{$row['product_id']}'";
            $where[] = "`warehouse_id` = '{$row['warehouse_id']}'";
            $where[] = "`document_date` = '{$row['document_date']}'";
            $where[] = "`document_identity` = '{$row['ref_document_identity']}'";
            $where = implode(' AND ', $where);
            $stock = $this->model['stock']->getStock($where);
            $row['stock_qty'] = $stock['stock_qty'];
            // $row['avg_stock_rate'] = $stock['avg_stock_rate'];
            // Change Avg stock rate value to detail cog rate (T)
            $row['avg_stock_rate'] = $row['cog_rate'];
            $row['cog_amount'] = $row['cog_amount'];
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

        // changing title from sale tax invoice to sale invoice for service associates
        $objPHPExcel->getProperties()
            ->setCreator("Muhammad Salman")
            ->setLastModifiedBy("Muhammad Salman")
            ->setTitle("Sales - Cost Analysis Report");

        $objPHPExcel->data = array(
            'company_name' => $session['company_name'],
            'report_name' => 'Sales - Cost Analysis Report',

        );

        $rowCount = 1;

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(8);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(10);


        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":B".($rowCount+1));
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $session['company_name']);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount . ':B'.$rowCount)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                ),
                'font' => array(
                    'size' => 12,
                    'bold' => true
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'FFFFFF')
                )
            )
        );

        $objPHPExcel->getActiveSheet()->mergeCells('C'.($rowCount).":G".($rowCount+1));
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Sales - Cost Analysis Report');
        $objPHPExcel->getActiveSheet()->getStyle('C'.$rowCount . ':G'.$rowCount)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                ),
                'font' => array(
                    'size' => 15,
                    'bold' => true
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'FFFFFF')
                )
            )
        );

        $objPHPExcel->getActiveSheet()->mergeCells('H'.($rowCount).":I".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, 'From: ' . $this->request->post['date_from']);
        $objPHPExcel->getActiveSheet()->mergeCells('J'.($rowCount).":L".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, 'Print Date: ' . date(STD_DATETIME));
        $objPHPExcel->getActiveSheet()->getStyle('H'.$rowCount . ':L'.$rowCount)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
                ),
                'font' => array(
                    'size' => 12
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'ffffff')
                )
            )
        );

        
        $rowCount ++;
        $objPHPExcel->getActiveSheet()->mergeCells('H'.($rowCount).":I".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, 'To: ' . $this->request->post['date_to']);
        $objPHPExcel->getActiveSheet()->mergeCells('I'.($rowCount).":L".($rowCount));
        $objPHPExcel->getActiveSheet()->getStyle('H'.$rowCount . ':L'.$rowCount)->applyFromArray(
            array(
                'alignment' => array(   
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
                ),
                'font' => array(
                    'size' => 12
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'FFFFFF')
                )
            )
        );

        $rowCount++; 
        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":E".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'TRANSACTIONS');
        $objPHPExcel->getActiveSheet()->mergeCells('F'.($rowCount).":G".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'SALES');
        $objPHPExcel->getActiveSheet()->mergeCells('H'.($rowCount).":I".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, 'COGS');
        $objPHPExcel->getActiveSheet()->mergeCells('J'.($rowCount).":L".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, 'GROSS MARGIN ANALYSIS');
        $objPHPExcel->getActiveSheet()->getStyle('A'.($rowCount).":L".($rowCount))->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                ),
                'font' => array(
                    'size' => 12,
                    'bold' => true
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'EEEEEE')
                ),
                'borders' => array(
                    'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                )
            )
        );


        $rowCount++;
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Date');
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'Document No');
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Product');
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'Customer');
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'Qty');
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'Sale Price');
        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, 'Total Sale');
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, 'Avg. U/Cost');
        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, 'Total Cost');
        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, 'Gross Profit');
        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount, 'GM ROI');
        $objPHPExcel->getActiveSheet()->SetCellValue('L'.$rowCount, 'GM SALES');
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount . ':L'.$rowCount)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                ),
                'font' => array(
                    'size' => 10,
                    'bold' => true
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'EEEEEE')
                ),
                'borders' => array(
                    'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                )
            )
        );

        $grand_total_sale_qty       = 0;
        $grand_total_sale           = 0;
        $grand_total_cogs           = 0;
        $grand_total_gross_profit   = 0;

        foreach ($invoices as $key => $value)
        {
            $rowCount++;
            $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":L".($rowCount));
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Product: ' . $value['product_code'] . ' - ' . $value['product_name'])->getStyle('A'.$rowCount)->getFont()->setBold( true );
            $rowCount++;

            $total_sale_qty     = 0;
            $total_sale         = 0;
            $total_cogs         = 0;
            $total_gross_profit = 0;
            
            foreach($value['data'] as $key_1 => $value_1)
            {
                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, stdDate($value_1['document_date']));
                $objPHPExcel->getActiveSheet()->getStyle('A'.($rowCount))->applyFromArray(
                    array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                        ),
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        )
                    )
                );

                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $value_1['document_identity']);
                $objPHPExcel->getActiveSheet()->getStyle('B'.($rowCount))->applyFromArray(
                    array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                        ),
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        )
                    )
                );
                
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value_1['product_name']);
                $objPHPExcel->getActiveSheet()->getStyle('C'.($rowCount))->applyFromArray(
                    array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                        ),
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        )
                    )
                );

                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value_1['partner_name']);
                $objPHPExcel->getActiveSheet()->getStyle('D'.($rowCount))->applyFromArray(
                    array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                        ),
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        )
                    )
                );

                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, number_format($value_1['qty'], 0));
                $objPHPExcel->getActiveSheet()->getStyle('E'.($rowCount))->applyFromArray(
                    array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                        ),
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        )
                    )
                );
                // $cog_amount = ($value_1['avg_stock_rate']*$value_1['qty']);
                $cog_amount = ($value_1['cog_amount']);
                $gross_profit = ($value_1['amount']-$cog_amount);
                $gm_roi = (($gross_profit/$cog_amount)*100);
                $sale_gm = (($gross_profit/$value_1['amount'])*100);
                $gm_roi = (strtolower($gm_roi) != 'inf') ? $gm_roi : 0;
                $sale_gm = (strtolower($sale_gm) != 'inf') ? $sale_gm : 0;

                if( $cog_amount <= 0 )
                {
                    $gm_roi = 100;
                    $sale_gm = 100;
                }


                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, number_format($value_1['rate'],2));
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, number_format($value_1['amount'],2));                
                $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, number_format($value_1['avg_stock_rate'],2));
                $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, number_format($cog_amount,2));
                $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, number_format($gross_profit,2));
                $objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount, number_format($gm_roi,2) . '%');
                $objPHPExcel->getActiveSheet()->SetCellValue('L'.$rowCount, number_format($sale_gm,2) . '%');
                $objPHPExcel->getActiveSheet()->getStyle('F'.($rowCount) . ':L'.($rowCount))->applyFromArray(
                    array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                        ),
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        )
                    )
                );


                $total_sale_qty     += $value_1['qty'];
                $total_sale         += $value_1['amount'];
                $total_cogs         += $cog_amount;
                $total_gross_profit += $gross_profit;
                
                $rowCount++;
            }

            $total_gm_roi = (($total_gross_profit/$total_cogs)*100);
            $total_gm_sale = (($total_gross_profit/$total_sale)*100);
            $total_gm_roi = (strtolower($total_gm_roi) != 'inf')?$total_gm_roi:0;
            $total_gm_sale = (strtolower($total_gm_sale) != 'inf')?$total_gm_sale:0;

            if( $total_cogs <= 0 )
            {
                $total_gm_roi = 100;
                $total_gm_sale = 100;
            }


            $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":D".($rowCount));
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Total');
            $objPHPExcel->getActiveSheet()->getStyle('A'.($rowCount) . ':D'.($rowCount))->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    ),
                    'font' => array(
                        'bold' => true
                    )
                )
            );

            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, number_format($total_sale_qty, 2));
            $objPHPExcel->getActiveSheet()->getStyle('E'.($rowCount))->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    ),
                    'font' => array(
                        'bold' => true
                    )
                )
            );

            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, number_format($total_sale, 2));
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, number_format($total_cogs, 2));
            $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, number_format($total_gross_profit, 2));
            $objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount, number_format($total_gm_roi,2) . '%');
            $objPHPExcel->getActiveSheet()->SetCellValue('L'.$rowCount, number_format($total_gm_sale,2) . '%');
            $objPHPExcel->getActiveSheet()->getStyle('F'.($rowCount) . ':L'.($rowCount))->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    ),
                    'font' => array(
                        'bold' => true
                    )
                )
            );

            $rowCount++;


            /*Grand Total*/
            $grand_total_sale_qty       += $total_sale_qty; 
            $grand_total_sale           += $total_sale;
            $grand_total_cogs           += $total_cogs;
            $grand_total_gross_profit   += $total_gross_profit;

        }

        $grand_total_gm_roi = (($grand_total_gross_profit/$grand_total_cogs)*100);
        $grand_total_gm_sale = (($grand_total_gross_profit/$grand_total_sale)*100);
        $grand_total_gm_roi = (strtolower($grand_total_gm_roi) != 'inf')?$grand_total_gm_roi:0;
        $grand_total_gm_sale = (strtolower($grand_total_gm_sale) != 'inf')?$grand_total_gm_sale:0;

        if( $grand_total_cogs <= 0 )
        {
            $grand_total_gm_roi = 100;
            $grand_total_gm_sale = 100;
        }


        $rowCount++;
        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":D".($rowCount));
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Grand Total');
            $objPHPExcel->getActiveSheet()->getStyle('A'.($rowCount) . ':D'.($rowCount))->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    ),
                    'font' => array(
                        'bold' => true
                    )
                )
            );

            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, number_format($grand_total_sale_qty, 2));
            $objPHPExcel->getActiveSheet()->getStyle('E'.($rowCount))->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    ),
                    'font' => array(
                        'bold' => true
                    )
                )
            );

            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, number_format($grand_total_sale, 2));
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, number_format($grand_total_cogs, 2));
            $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, number_format($grand_total_gross_profit, 2));
            $objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount, number_format($grand_total_gm_roi,2) . '%');
            $objPHPExcel->getActiveSheet()->SetCellValue('L'.$rowCount, number_format($grand_total_gm_sale,2) . '%');
            $objPHPExcel->getActiveSheet()->getStyle('F'.($rowCount) . ':L'.($rowCount))->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    ),
                    'font' => array(
                        'bold' => true
                    )
                )
            );



        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="sales_cost_analysis_report.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save('php://output');
        exit;

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
            $filter[] = "`partner_id` IN ('". implode("','", $post['partner_id']) ."')";
        }
        if($post['warehouse_id']){
            $filter[] = "`warehouse_id` = '" . $post['warehouse_id'] . "'";
        }
        if($post['product_id']){
            $filter[] = "`product_id` IN ('" . implode("','", $post['product_id']) . "')";
        }
        $where = implode(' AND ', $filter);
        // d($where, true);
        $this->model['sale_tax_invoice_detail'] = $this->load->model('inventory/sale_tax_invoice_detail');
        $rows = $this->model['sale_tax_invoice_detail']->getRows($where, array('created_at'));
        // d($rows,true);
        if($post['group_by']=='document') {
            $this->pdfDocumentWise($rows);
        } elseif($post['group_by']=='partner') {
            $this->pdfPartnerWise($rows);
        } elseif($post['group_by']=='warehouse') {
            $this->pdfWarehouseWise($rows);
        } elseif($post['group_by']=='product') {
            $this->pdfProductWise($rows);
        }     
    }

    private function pdfDocumentWise($rows) {
        // d($rows, true);
        $invoices = array();
        $this->model['stock'] = $this->load->model('common/stock_ledger');
        
        foreach($rows as $row) {
            if(!isset($invoices[$row['document_date']])) {

                $invoices[$row['document_date']] = array(
                    'document_date' => $row['document_date'],
                    'document_identity' => $row['document_identity'],
                    'data' => array()
                );
            }

            $where = [];
            $where[] = "`company_id`   = '{$this->session->data['company_id']}'";
            $where[] = "`company_branch_id`   = '{$this->session->data['company_branch_id']}'";
            $where[] = "`fiscal_year_id`   = '{$this->session->data['fiscal_year_id']}'";
            $where[] = "`product_id`   = '{$row['product_id']}'";
            $where[] = "`warehouse_id` = '{$row['warehouse_id']}'";
            $where[] = "`document_date` = '{$row['document_date']}'";
            $where[] = "`document_identity` = '{$row['ref_document_identity']}'";
            $where = implode(' AND ', $where);
            // d($where, true);
            $stock = $this->model['stock']->getStock($where);
            $row['stock_qty'] = $stock['stock_qty'];
            // $row['avg_stock_rate'] = $stock['avg_stock_rate'];
            // Change Avg stock rate value to detail cog rate (T)
            $row['avg_stock_rate'] = $row['cog_rate'];
            $row['cog_amount'] = $row['cog_amount'];
            $invoices[$row['document_date']]['data'][] = $row;
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
        
        $pdf = new PDF('L', PDF_UNIT, 'LEGAL', true, 'UTF-8', false);
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Muhammad Salman');
        $pdf->SetTitle('Sales - Cost Analysis Report');
        $pdf->SetSubject('Sales - Cost Analysis Report');
        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'group_by' => 'document_date',
            'from_date' => $this->request->post['date_from'],
            'to_date' => $this->request->post['date_to']
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10, 35, 10);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // set font
        $pdf->SetFont('helvetica', '', 7);

        $pdf->AddPage();


        $grand_total_sale_qty       = 0;
        $grand_total_sale           = 0;
        $grand_total_cogs           = 0;
        $grand_total_gross_profit   = 0;


        foreach($invoices as $row) {
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(0, 7,'Document Date: ' . stdDate($row['document_date']), 0, false, 'L', 1, '', 0, false, 'M', 'M');
            $pdf->SetFont('helvetica', '', 7);
            
            $total_sale_qty     = 0;
            $total_sale         = 0;
            $total_cogs         = 0;
            $total_gross_profit = 0;
            $total_gm_roi       = 0;
            $total_gm_sale      = 0;

            $pdf->Ln(1);
            foreach($row['data'] as $detail) {

                // $cog_amount = ($detail['avg_stock_rate']*$detail['qty']);
                $cog_amount = ($detail['cog_amount']);
                $gross_profit = ($detail['amount']-$cog_amount);
                $gm_roi = (($gross_profit/$cog_amount)*100);
                $sale_gm = (($gross_profit/$detail['amount'])*100);
                $gm_roi = (strtolower($gm_roi) != 'inf') ? $gm_roi : 0;
                $sale_gm = (strtolower($sale_gm) != 'inf') ? $sale_gm : 0;

                if( $cog_amount <= 0 )
                {
                    $gm_roi = 100;
                    $sale_gm = 100;
                }

                if( strlen($detail['product_name']) <= 57 )
                {
                    $pdf->Ln(6);
                    $pdf->Cell(20, 6, stdDate($detail['document_date']), 1, false, 'C', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(25, 6, $detail['document_identity'], 1, false, 'C', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(73, 6, $detail['product_name'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(73, 6, html_entity_decode($detail['partner_name']), 1, false, 'L', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(15, 6, number_format($detail['qty'],0), 1, false, 'C', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(20, 6, number_format($detail['rate'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(20, 6, number_format($detail['amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(18, 6, number_format($detail['avg_stock_rate'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(20, 6, number_format($cog_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(20, 6, number_format($gross_profit,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(15, 6, number_format($gm_roi,2).'%', 1, false, 'C', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(15, 6, number_format($sale_gm,2).'%', 1, false, 'C', 0, '', 1, false, 'M', 'M');
                }
                else
                {
                    $productArr = splitString($detail['product_name'], 57);
                    $customerArr = splitString($detail['partner_name'], 57);
                    foreach ($productArr as $index => $product) {
                        if( $index == 0 )
                        {
                            $pdf->Ln(6);
                            $pdf->Cell(20, 6, stdDate($detail['document_date']), 'TLR', false, 'C', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(25, 6, $detail['document_identity'], 'TLR', false, 'C', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(73, 6, $product, 'TLR', false, 'L', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(73, 6, html_entity_decode($customerArr[$index]), 'TLR', false, 'L', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(15, 6, number_format($detail['qty'],0), 'TLR', false, 'C', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, number_format($detail['rate'],2), 'TLR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, number_format($detail['amount'],2), 'TLR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(18, 6, number_format($detail['avg_stock_rate'],2), 'TLR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, number_format($cog_amount,2), 'TLR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, number_format($gross_profit,2), 'TLR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(15, 6, number_format($gm_roi,2).'%', 'TLR', false, 'C', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(15, 6, number_format($sale_gm,2).'%', 'TLR', false, 'C', 0, '', 1, false, 'M', 'M');                 
                        }
                        else if( $index == count($productArr)-1 )
                        {
                            $pdf->Ln(6);
                            $pdf->Cell(20, 6, '', 'LR', false, 'C', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(25, 6, '', 'LR', false, 'L', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(73, 6, $product, 'LR', false, 'L', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(73, 6, html_entity_decode($customerArr[$index]), 'LR', false, 'L', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(15, 6, '', 'LR', false, 'L', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(18, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(15, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(15, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');  
                        }
                        else
                        {
                            $pdf->Ln(6);
                            $pdf->Cell(20, 6, '', 'LR', false, 'C', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(25, 6, '', 'LR', false, 'L', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(73, 6, $product, 'LR', false, 'L', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(73, 6, html_entity_decode($customerArr[$index]), 'LR', false, 'L', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(15, 6, '', 'LR', false, 'C', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(18, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(15, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(15, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');  
                        }
                    }
                }

                $total_sale_qty += $detail['qty'];
                $total_sale += $detail['amount'];
                $total_cogs += $cog_amount;
                $total_gross_profit += $gross_profit;               
                
            }
            
            $pdf->Ln(6);
            $total_gm_roi = (($total_gross_profit/$total_cogs)*100);
            $total_gm_sale = (($total_gross_profit/$total_sale)*100);
            $total_gm_roi = (strtolower($total_gm_roi) != 'inf')?$total_gm_roi:0;
            $total_gm_sale = (strtolower($total_gm_sale) != 'inf')?$total_gm_sale:0;

            if( $total_cogs <= 0 )
            {
                $total_gm_roi = 100;
                $total_gm_sale = 100;
            }

            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->Cell(191, 6, 'Total', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(15, 6, number_format($total_sale_qty), 1, false, 'C', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 6, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 6, number_format($total_sale,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(18, 6, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 6, number_format($total_cogs, 2), 1, false, 'R', 0, '', 1, false, 'M', 'M');            
            $pdf->Cell(20, 6, number_format($total_gross_profit, 2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(15, 6, number_format($total_gm_roi, 2) . '%', 1, false, 'C', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(15, 6, number_format($total_gm_sale, 2) . '%', 1, false, 'C', 0, '', 1, false, 'M', 'M');
            $pdf->Ln(7);


            /*Grand Total*/
            $grand_total_sale_qty       += $total_sale_qty; 
            $grand_total_sale           += $total_sale;
            $grand_total_cogs           += $total_cogs;
            $grand_total_gross_profit   += $total_gross_profit;
        }

        $pdf->Ln(6);
        $grand_total_gm_roi = (($grand_total_gross_profit/$grand_total_cogs)*100);
        $grand_total_gm_sale = (($grand_total_gross_profit/$grand_total_sale)*100);
        $grand_total_gm_roi = (strtolower($grand_total_gm_roi) != 'inf')?$grand_total_gm_roi:0;
        $grand_total_gm_sale = (strtolower($grand_total_gm_sale) != 'inf')?$grand_total_gm_sale:0;

        if( $grand_total_cogs <= 0 )
        {
            $grand_total_gm_roi = 100;
            $grand_total_gm_sale = 100;
        }

        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(191, 6, 'Grand Total', 1, false, 'R', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(15, 6, number_format($grand_total_sale_qty), 1, false, 'C', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(20, 6, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(20, 6, number_format($grand_total_sale,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(18, 6, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(20, 6, number_format($grand_total_cogs, 2), 1, false, 'R', 0, '', 1, false, 'M', 'M');            
        $pdf->Cell(20, 6, number_format($grand_total_gross_profit, 2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(15, 6, number_format($grand_total_gm_roi, 2) . '%', 1, false, 'C', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(15, 6, number_format($grand_total_gm_sale, 2) . '%', 1, false, 'C', 0, '', 1, false, 'M', 'M');

        //Close and output PDF document
        $pdf->Output('Sales - Cost Analysis Report:'.date('YmdHis').'.pdf', 'I');
    }

    private function pdfPartnerWise($rows) {
        //d($rows, true);
        $invoices = array();
        $this->model['stock'] = $this->load->model('common/stock_ledger');

        foreach($rows as $row) {
            if(!isset($invoices[$row['partner_type'].'-'.$row['partner_name']])) {
                $invoices[$row['partner_type'].'-'.$row['partner_name']] = array(
                    'partner_type' => $row['partner_type'],
                    'partner_name' => $row['partner_name'],
                    'data' => array()
                );
            }

            $where = [];
            $where[] = "`company_id`   = '{$this->session->data['company_id']}'";
            $where[] = "`company_branch_id`   = '{$this->session->data['company_branch_id']}'";
            $where[] = "`fiscal_year_id`   = '{$this->session->data['fiscal_year_id']}'";
            $where[] = "`product_id`   = '{$row['product_id']}'";
            $where[] = "`warehouse_id` = '{$row['warehouse_id']}'";
            $where[] = "`document_date` = '{$row['document_date']}'";
            $where[] = "`document_identity` = '{$row['ref_document_identity']}'";
            $where = implode(' AND ', $where);
            $stock = $this->model['stock']->getStock($where);
            $row['stock_qty'] = $stock['stock_qty'];
            // $row['avg_stock_rate'] = $stock['avg_stock_rate'];
            // Change Avg stock rate value to detail cog rate (T)
            $row['avg_stock_rate'] = $row['cog_rate'];
            $row['cog_amount'] = $row['cog_amount'];
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

        $session = $this->session->data;
        $company_logo = $setting['value'];

        $pdf = new PDF('L', PDF_UNIT, 'LEGAL', true, 'UTF-8', false);
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Muhammad Salman');
        $pdf->SetTitle('Sales - Cost Analysis Report');
        $pdf->SetSubject('Sales - Cost Analysis Report');
        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'group_by' => 'document_date',
            'from_date' => $this->request->post['date_from'],
            'to_date' => $this->request->post['date_to']
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10, 35, 10);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // set font
        $pdf->SetFont('helvetica', '', 7);

        $pdf->AddPage();


        $grand_total_sale_qty       = 0;
        $grand_total_sale           = 0;
        $grand_total_cogs           = 0;
        $grand_total_gross_profit   = 0;


        foreach($invoices as $row) {
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(0, 7,'Customer: ' . $row['partner_name'], 0, false, 'L', 1, '', 0, false, 'M', 'M');
            $pdf->SetFont('helvetica', '', 7);
            
            $total_sale_qty     = 0;
            $total_sale         = 0;
            $total_cogs         = 0;
            $total_gross_profit = 0;
            $total_gm_roi       = 0;
            $total_gm_sale      = 0;

            $pdf->Ln(1);
            foreach($row['data'] as $detail) {

                // $cog_amount = ($detail['avg_stock_rate']*$detail['qty']);
                $cog_amount = ($detail['cog_amount']);
                $gross_profit = ($detail['amount']-$cog_amount);
                $gm_roi = (($gross_profit/$cog_amount)*100);
                $sale_gm = (($gross_profit/$detail['amount'])*100);
                $gm_roi = (strtolower($gm_roi) != 'inf') ? $gm_roi : 0;
                $sale_gm = (strtolower($sale_gm) != 'inf') ? $sale_gm : 0;

                if( $cog_amount <= 0 )
                {
                    $gm_roi = 100;
                    $sale_gm = 100;
                }


                if( strlen($detail['product_name']) <= 47 )
                {
                    $pdf->Ln(6);
                    $pdf->Cell(20, 6, stdDate($detail['document_date']), 1, false, 'C', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(25, 6, $detail['document_identity'], 1, false, 'C', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(73, 6, $detail['product_name'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(73, 6, html_entity_decode($detail['partner_name']), 1, false, 'L', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(15, 6, number_format($detail['qty'],0), 1, false, 'C', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(20, 6, number_format($detail['rate'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(20, 6, number_format($detail['amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(18, 6, number_format($detail['avg_stock_rate'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(20, 6, number_format($cog_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(20, 6, number_format($gross_profit,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(15, 6, number_format($gm_roi,2).'%', 1, false, 'C', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(15, 6, number_format($sale_gm,2).'%', 1, false, 'C', 0, '', 1, false, 'M', 'M');
                }
                else
                {
                    $productArr = str_split($detail['product_name'], 47);
                    $customerArr = str_split($detail['partner_name'], 47);
                    foreach ($productArr as $index => $product) {
                        if( $index == 0 )
                        {
                            $pdf->Ln(6);
                            $pdf->Cell(20, 6, stdDate($detail['document_date']), 'TLR', false, 'C', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(25, 6, $detail['document_identity'], 'TLR', false, 'C', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(73, 6, $product, 'TLR', false, 'L', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(73, 6, html_entity_decode($customerArr[$index]), 'TLR', false, 'L', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(15, 6, number_format($detail['qty'],0), 'TLR', false, 'C', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, number_format($detail['rate'],2), 'TLR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, number_format($detail['amount'],2), 'TLR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(18, 6, number_format($detail['avg_stock_rate'],2), 'TLR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, number_format($cog_amount,2), 'TLR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, number_format($gross_profit,2), 'TLR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(15, 6, number_format($gm_roi,2).'%', 'TLR', false, 'C', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(15, 6, number_format($sale_gm,2).'%', 'TLR', false, 'C', 0, '', 1, false, 'M', 'M');                 
                        }
                        else if( $index == count($productArr)-1 )
                        {
                            $pdf->Ln(6);
                            $pdf->Cell(20, 6, '', 'LR', false, 'C', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(25, 6, '', 'LR', false, 'L', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(73, 6, $product, 'LR', false, 'L', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(73, 6, html_entity_decode($customerArr[$index]), 'LR', false, 'L', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(15, 6, '', 'LR', false, 'C', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(18, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(15, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(15, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');  
                        }
                        else
                        {
                            $pdf->Ln(6);
                            $pdf->Cell(20, 6, '', 'LR', false, 'C', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(25, 6, '', 'LR', false, 'L', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(73, 6, $product, 'LR', false, 'L', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(73, 6, html_entity_decode($customerArr[$index]), 'LR', false, 'L', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(15, 6, '', 'LR', false, 'C', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(18, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(15, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(15, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');  
                        }
                    }
                }

                $total_sale_qty += $detail['qty'];
                $total_sale += $detail['amount'];
                $total_cogs += $cog_amount;
                $total_gross_profit += $gross_profit;               
                
            }
            
            $pdf->Ln(6);
            $total_gm_roi = (($total_gross_profit/$total_cogs)*100);
            $total_gm_sale = (($total_gross_profit/$total_sale)*100);
            $total_gm_roi = (strtolower($total_gm_roi) != 'inf')?$total_gm_roi:0;
            $total_gm_sale = (strtolower($total_gm_sale) != 'inf')?$total_gm_sale:0;

            if( $total_cogs <= 0 )
            {
                $total_gm_roi = 100;
                $total_gm_sale = 100;
            }


            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->Cell(191, 6, 'Total', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(15, 6, number_format($total_sale_qty), 1, false, 'C', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 6, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 6, number_format($total_sale,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(18, 6, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 6, number_format($total_cogs, 2), 1, false, 'R', 0, '', 1, false, 'M', 'M');            
            $pdf->Cell(20, 6, number_format($total_gross_profit, 2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(15, 6, number_format($total_gm_roi, 2) . '%', 1, false, 'C', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(15, 6, number_format($total_gm_sale, 2) . '%', 1, false, 'C', 0, '', 1, false, 'M', 'M');
            $pdf->Ln(7);


            /*Grand Total*/
            $grand_total_sale_qty       += $total_sale_qty; 
            $grand_total_sale           += $total_sale;
            $grand_total_cogs           += $total_cogs;
            $grand_total_gross_profit   += $total_gross_profit;
        }

        $pdf->Ln(6);
        $grand_total_gm_roi = (($grand_total_gross_profit/$grand_total_cogs)*100);
        $grand_total_gm_sale = (($grand_total_gross_profit/$grand_total_sale)*100);
        $grand_total_gm_roi = (strtolower($grand_total_gm_roi) != 'inf')?$grand_total_gm_roi:0;
        $grand_total_gm_sale = (strtolower($grand_total_gm_sale) != 'inf')?$grand_total_gm_sale:0;

        if( $grand_total_cogs <= 0 )
        {
            $grand_total_gm_roi = 100;
            $grand_total_gm_sale = 100;
        }

        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(191, 6, 'Grand Total', 1, false, 'R', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(15, 6, number_format($grand_total_sale_qty), 1, false, 'C', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(20, 6, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(20, 6, number_format($grand_total_sale,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(18, 6, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(20, 6, number_format($grand_total_cogs, 2), 1, false, 'R', 0, '', 1, false, 'M', 'M');            
        $pdf->Cell(20, 6, number_format($grand_total_gross_profit, 2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(15, 6, number_format($grand_total_gm_roi, 2) . '%', 1, false, 'C', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(15, 6, number_format($grand_total_gm_sale, 2) . '%', 1, false, 'C', 0, '', 1, false, 'M', 'M');

        //Close and output PDF document
        $pdf->Output('Sales - Cost Analysis Report:'.date('YmdHis').'.pdf', 'I');
    }

    private function pdfProductWise($rows) {
        //d($rows, true);
        $invoices = array();
        $this->model['stock'] = $this->load->model('common/stock_ledger');

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
            $where = [];
            $where[] = "`company_id`   = '{$this->session->data['company_id']}'";
            $where[] = "`company_branch_id`   = '{$this->session->data['company_branch_id']}'";
            $where[] = "`fiscal_year_id`   = '{$this->session->data['fiscal_year_id']}'";
            $where[] = "`product_id`   = '{$row['product_id']}'";
            $where[] = "`warehouse_id` = '{$row['warehouse_id']}'";
            $where[] = "`document_date` = '{$row['document_date']}'";
            $where[] = "`document_identity` = '{$row['ref_document_identity']}'";
            $where = implode(' AND ', $where);
            $stock = $this->model['stock']->getStock($where);
            $row['stock_qty'] = $stock['stock_qty'];
            // $row['avg_stock_rate'] = $stock['avg_stock_rate'];
            // Change Avg stock rate value to detail cog rate (T)
            $row['avg_stock_rate'] = $row['cog_rate'];
            $row['cog_amount'] = $row['cog_amount'];
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

        $pdf = new PDF('L', PDF_UNIT, 'LEGAL', true, 'UTF-8', false);
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Muhammad Salman');
        $pdf->SetTitle('Sales - Cost Analysis Report');
        $pdf->SetSubject('Sales - Cost Analysis Report');
        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'group_by' => 'document_date',
            'from_date' => $this->request->post['date_from'],
            'to_date' => $this->request->post['date_to']
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10, 35, 10);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // set font
        $pdf->SetFont('helvetica', '', 7);

        $pdf->AddPage();


        $grand_total_sale_qty       = 0;
        $grand_total_sale           = 0;
        $grand_total_cogs           = 0;
        $grand_total_gross_profit   = 0;


        foreach($invoices as $row) {
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(0, 7,'Product: ' . $row['product_code'] . ' - ' . $row['product_name'], 0, false, 'L', 1, '', 0, false, 'M', 'M');
            $pdf->SetFont('helvetica', '', 7);
            
            $total_sale_qty     = 0;
            $total_sale         = 0;
            $total_cogs         = 0;
            $total_gross_profit = 0;
            $total_gm_roi       = 0;
            $total_gm_sale      = 0;

            $pdf->Ln(1);
            foreach($row['data'] as $detail) {

                // $cog_amount = ($detail['avg_stock_rate']*$detail['qty']);
                $cog_amount = ($detail['cog_amount']);
                $gross_profit = ($detail['amount']-$cog_amount);
                $gm_roi = (($gross_profit/$cog_amount)*100);
                $sale_gm = (($gross_profit/$detail['amount'])*100);
                $gm_roi = (strtolower($gm_roi) != 'inf') ? $gm_roi : 0;
                $sale_gm = (strtolower($sale_gm) != 'inf') ? $sale_gm : 0;

                if( $cog_amount <= 0 )
                {
                    $gm_roi = 100;
                    $sale_gm = 100;
                }


                if( strlen($detail['product_name']) <= 47 )
                {
                    $pdf->Ln(6);
                    $pdf->Cell(20, 6, stdDate($detail['document_date']), 1, false, 'C', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(25, 6, $detail['document_identity'], 1, false, 'C', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(73, 6, $detail['product_name'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(73, 6, html_entity_decode($detail['partner_name']), 1, false, 'L', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(15, 6, number_format($detail['qty'],0), 1, false, 'C', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(20, 6, number_format($detail['rate'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(20, 6, number_format($detail['amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(18, 6, number_format($detail['avg_stock_rate'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(20, 6, number_format($cog_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(20, 6, number_format($gross_profit,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(15, 6, number_format($gm_roi,2).'%', 1, false, 'C', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(15, 6, number_format($sale_gm,2).'%', 1, false, 'C', 0, '', 1, false, 'M', 'M');
                }
                else
                {
                    $productArr = str_split($detail['product_name'], 47);
                    $customerArr = str_split($detail['partner_name'], 47);
                    foreach ($productArr as $index => $product) {
                        if( $index == 0 )
                        {
                            $pdf->Ln(6);
                            $pdf->Cell(20, 6, stdDate($detail['document_date']), 'TLR', false, 'C', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(25, 6, $detail['document_identity'], 'TLR', false, 'C', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(73, 6, $product, 'TLR', false, 'L', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(73, 6, html_entity_decode($customerArr[$index]), 'TLR', false, 'L', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(15, 6, number_format($detail['qty'],0), 'TLR', false, 'C', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, number_format($detail['rate'],2), 'TLR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, number_format($detail['amount'],2), 'TLR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(18, 6, number_format($detail['avg_stock_rate'],2), 'TLR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, number_format($cog_amount,2), 'TLR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, number_format($gross_profit,2), 'TLR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(15, 6, number_format($gm_roi,2).'%', 'TLR', false, 'C', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(15, 6, number_format($sale_gm,2).'%', 'TLR', false, 'C', 0, '', 1, false, 'M', 'M');                 
                        }
                        else if( $index == count($productArr)-1 )
                        {
                            $pdf->Ln(6);
                            $pdf->Cell(20, 6, '', 'LR', false, 'C', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(25, 6, '', 'LR', false, 'L', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(73, 6, $product, 'LR', false, 'L', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(73, 6, html_entity_decode($customerArr[$index]), 'LR', false, 'L', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(15, 6, '', 'LR', false, 'C', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(18, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(15, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(15, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');  
                        }
                        else
                        {
                            $pdf->Ln(6);
                            $pdf->Cell(20, 6, '', 'LR', false, 'C', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(25, 6, '', 'LR', false, 'L', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(73, 6, $product, 'LR', false, 'L', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(73, 6, html_entity_decode($customerArr[$index]), 'LR', false, 'L', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(15, 6, '', 'LR', false, 'C', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(18, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(20, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(15, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');
                            $pdf->Cell(15, 6, '', 'LR', false, 'R', 0, '', 1, false, 'M', 'M');  
                        }
                    }
                }

                $total_sale_qty += $detail['qty'];
                $total_sale += $detail['amount'];
                $total_cogs += $cog_amount;
                $total_gross_profit += $gross_profit;               
                
            }
            
            $pdf->Ln(6);
            $total_gm_roi = (($total_gross_profit/$total_cogs)*100);
            $total_gm_sale = (($total_gross_profit/$total_sale)*100);
            $total_gm_roi = (strtolower($total_gm_roi) != 'inf')?$total_gm_roi:0;
            $total_gm_sale = (strtolower($total_gm_sale) != 'inf')?$total_gm_sale:0;

            if( $total_cogs <= 0 )
            {
                $total_gm_roi = 100;
                $total_gm_sale = 100;
            }


            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->Cell(191, 6, 'Total', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(15, 6, number_format($total_sale_qty), 1, false, 'C', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 6, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 6, number_format($total_sale,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(18, 6, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 6, number_format($total_cogs, 2), 1, false, 'R', 0, '', 1, false, 'M', 'M');            
            $pdf->Cell(20, 6, number_format($total_gross_profit, 2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(15, 6, number_format($total_gm_roi, 2) . '%', 1, false, 'C', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(15, 6, number_format($total_gm_sale, 2) . '%', 1, false, 'C', 0, '', 1, false, 'M', 'M');
            $pdf->Ln(7);


            /*Grand Total*/
            $grand_total_sale_qty       += $total_sale_qty; 
            $grand_total_sale           += $total_sale;
            $grand_total_cogs           += $total_cogs;
            $grand_total_gross_profit   += $total_gross_profit;
        }

        $pdf->Ln(6);
        $grand_total_gm_roi = (($grand_total_gross_profit/$grand_total_cogs)*100);
        $grand_total_gm_sale = (($grand_total_gross_profit/$grand_total_sale)*100);
        $grand_total_gm_roi = (strtolower($grand_total_gm_roi) != 'inf')?$grand_total_gm_roi:0;
        $grand_total_gm_sale = (strtolower($grand_total_gm_sale) != 'inf')?$grand_total_gm_sale:0;

        if( $grand_total_cogs <= 0 )
        {
            $grand_total_gm_roi = 100;
            $grand_total_gm_sale = 100;
        }

        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(191, 6, 'Grand Total', 1, false, 'R', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(15, 6, number_format($grand_total_sale_qty), 1, false, 'C', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(20, 6, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(20, 6, number_format($grand_total_sale,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(18, 6, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(20, 6, number_format($grand_total_cogs, 2), 1, false, 'R', 0, '', 1, false, 'M', 'M');            
        $pdf->Cell(20, 6, number_format($grand_total_gross_profit, 2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(15, 6, number_format($grand_total_gm_roi, 2) . '%', 1, false, 'C', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(15, 6, number_format($grand_total_gm_sale, 2) . '%', 1, false, 'C', 0, '', 1, false, 'M', 'M');

        //Close and output PDF document
        $pdf->Output('Sales - Cost Analysis Report:'.date('YmdHis').'.pdf', 'I');
    }

}

class PDF extends TCPDF {
    public $data = array();

    //Page header
    public function Header() {
        // Logo
        // if($this->data['company_logo'] != '') {
        //     $image_file = DIR_IMAGE.$this->data['company_logo'];
        //     //$this->Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);
        //     $this->Image($image_file, 10, 10, 30, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // }
        // Set font
        $this->SetFont('helvetica', 'B', 10);
        $this->Ln(2);
        // Title
        $this->Cell(50, 5, $this->data['company_name'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        
        $this->Cell(206, 5, $this->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(35, 5, 'From Date: ' . $this->data['from_date'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(45, 5, 'Print Date: ' . date(STD_DATETIME), 0, false, 'L', 0, '', 0, false, 'M', 'M');
        
        $this->Ln(5);
        $this->Cell(256, 5, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $this->Cell(35, 5, 'To Date: ' . $this->data['to_date'], 0, false, 'L', 0, '', 0, false, 'M', 'M');


        $this->ln(7);
        $this->SetFont('helvetica', 'B', 8);
        $this->SetFillColor(245,245,245);
        $this->Cell(206, 7, 'TRANSACTIONS', 1, false, 'C', 1, '', 0, false, 'M', 'M');
        $this->Cell(40, 7, 'SALES', 1, false, 'C', 1, '', 0, false, 'M', 'M');
        $this->Cell(38, 7, 'COGS', 1, false, 'C', 1, '', 0, false, 'M', 'M');
        $this->Cell(50, 7, 'GROSS MARGIN ANALYSIS', 1, false, 'C', 1, '', 0, false, 'M', 'M');
        $this->SetFillColor(0,0,0);
        
        $this->ln(7);
        $this->SetFont('helvetica', 'B', 7);
        $this->SetFillColor(189,189,189);
        $this->Cell(20, 7, 'Date', 1, false, 'C', 1, '', 0, false, 'M', 'M');
        $this->Cell(25, 7, 'Document No', 1, false, 'C', 1, '', 0, false, 'M', 'M');
        $this->Cell(73, 7, 'Product', 1, false, 'C', 1, '', 0, false, 'M', 'M');
        $this->Cell(73, 7, 'Customer', 1, false, 'C', 1, '', 0, false, 'M', 'M');
        $this->Cell(15, 7, 'Qty', 1, false, 'C', 1, '', 0, false, 'M', 'M');
        $this->Cell(20, 7, 'Sale Price', 1, false, 'C', 1, '', 0, false, 'M', 'M');
        $this->Cell(20, 7, 'Total Sale', 1, false, 'C', 1, '', 0, false, 'M', 'M');
        $this->Cell(18, 7, 'Avg. U/Cost', 1, false, 'C', 1, '', 0, false, 'M', 'M');
        $this->Cell(20, 7, 'Total Cost', 1, false, 'C', 1, '', 0, false, 'M', 'M');
        $this->Cell(20, 7, 'Gross Profit', 1, false, 'C', 1, '', 0, false, 'M', 'M');
        $this->Cell(15, 7, 'GM ROI', 1, false, 'C', 1, '', 0, false, 'M', 'M');
        $this->Cell(15, 7, 'GM SALES', 1, false, 'C', 1, '', 0, false, 'M', 'M');
        $this->SetFillColor(0,0,0);

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