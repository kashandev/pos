<?php

class ControllerReportSaleTaxReport extends HController {

    protected function getAlias() {
        return 'report/sale_tax_report';
    }

    protected function getDefaultOrder() {
        return 'sale_tax_invoice_id';
    }

    protected function getDefaultSort() {
        return 'DESC';
    }

    protected function getList() {
        parent::getList();

        $this->data['partner_types'] = $this->session->data['partner_types'];

        $this->model['customer'] = $this->load->model('setup/customer');

        $where = "company_id=" . $this->session->data['company_id'];
        // $where .= " AND partner_category_id=" .'1';

        $this->data['partners'] = $this->model['customer']->getRows($where,array('name'));

        $this->model['product'] = $this->load->model('inventory/product');
        $this->data['products'] = $this->model['product']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->data['warehouses'] = $this->model['warehouse']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
        $this->data['href_get_product_json'] = $this->url->link($this->getAlias() . '/getProductJson', 'token=' . $this->session->data['token'], 'SSL');
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

    public function getDetailReport() {
        $post = $this->request->post;
        $session = $this->session->data;
        $this->model['sale_tax_invoice_detail'] = $this->load->model('inventory/sale_tax_invoice_detail');
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
            $arrWhere[] = "`partner_id` IN (". implode(",", $post['partner_id']) .")";
        }
        if($post['warehouse_id']){
            $arrWhere[] = "`warehouse_id` = '" . $post['warehouse_id'] . "'";
        }
        if($post['product_id']){
            $arrWhere[] = "`product_id` IN (" . implode(",", $post['product_id']) . ")";
        }
        if($post['container_no'])
            $arrWhere[] = "`container_no` LIKE '" . $post['container_no'] . "%'";

        $where = implode(' AND ', $arrWhere);


        $rows = $this->model['sale_tax_invoice_detail']->getRows($where);
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
            $filter[] = "`partner_id` IN (". implode(",", $post['partner_id']) .")";
        }
        if($post['warehouse_id']){
            $filter[] = "`warehouse_id` = '" . $post['warehouse_id'] . "'";
        }
        if($post['product_id']){
            $filter[] = "`product_id` IN (" . implode(",", $post['product_id']) . ")";
        }
        $where = implode(' AND ', $filter);
        //d($post, true);
        $this->model['sale_tax_invoice_detail'] = $this->load->model('inventory/sale_tax_invoice_detail');
        $rows = $this->model['sale_tax_invoice_detail']->getRows($where, array('created_at'));
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
        foreach($rows as $row) {
            if(!isset($invoices[$row['document_date']])) {
                $invoices[$row['document_date']] = array(
                    'document_date' => $row['document_date'],
                    'document_identity' => $row['document_identity'],
                    'data' => array()
                );
            }
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
            ->setCreator("Hira")
            ->setLastModifiedBy("Hira")
            ->setTitle("Sale Report");

        $objPHPExcel->data = array(
            'company_name' => $session['company_name'],
            'report_name' => 'Sale Report',

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
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Sale Report');
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
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Doc. Date')->getStyle('A'.$rowCount)->getFont()->setBold( true );
            $rowCount++;
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $value['document_date'])->getStyle('A'.$rowCount)->getFont()->setBold( true );
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
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Product')->getStyle('A'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'Customer')->getStyle('B'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Warehouse')->getStyle('B'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'Quantity')->getStyle('C'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'Unit')->getStyle('D'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'Rate')->getStyle('E'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, 'Amount')->getStyle('F'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, 'Discount')->getStyle('G'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, 'Tax Amount')->getStyle('H'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, 'Total Amount')->getStyle('I'.$rowCount)->getFont()->setBold( true );
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
                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $value_1['product_name']);
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $value_1['partner_name']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value_1['warehouse']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value_1['qty']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value_1['unit']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $value_1['rate']);
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $value_1['amount']);
                $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $value_1['discount']);
                $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $value_1['tax_amount']);
                $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, $value_1['total_amount']);
                $rowCount++;
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="sale_report.xlsx"');
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
            'report_name' => 'Sale Report',

        );

        $rowCount = 1;

        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":K".($rowCount));
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

        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":K".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Sale Report');
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

        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":K".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, '');
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->getFill('ebebeb');
        $rowCount ++;

        foreach ($invoices as $key => $value)
        {
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Partner Name')->getStyle('A'.$rowCount)->getFont()->setBold( true );
            $rowCount++;
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $value['partner_name'])->getStyle('A'.$rowCount)->getFont()->setBold( true );
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
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'Warehouse')->getStyle('C'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'Quantity')->getStyle('D'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'Unit')->getStyle('E'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, 'Rate')->getStyle('F'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, 'Amount')->getStyle('G'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, 'Discount')->getStyle('H'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, 'Tax Amount')->getStyle('I'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount, 'Total Amount')->getStyle('J'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':K'.$rowCount)->applyFromArray(
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
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value_1['warehouse']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value_1['qty']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $value_1['unit']);
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $value_1['rate']);
                $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $value_1['amount']);
                $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $value_1['discount_amount']);
                $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, $value_1['tax_amount']);
                $objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount, $value_1['total_amount']);
                $rowCount++;
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="sale_report.xlsx"');
        header('Cache-Control: max-age=0');
        //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        //$objWriter->save('some_excel_file.xlsx');
        $objWriter->save('php://output');
        exit;
    }

    private function excelWarehouseWise($rows) {
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
            'report_name' => 'Sale Report',

        );

        $rowCount = 1;

        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":K".($rowCount));
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

        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":K".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Sale Report');
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

        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":K".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, '');
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->getFill('ebebeb');
        $rowCount ++;

        foreach ($invoices as $key => $value)
        {
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Warehouse')->getStyle('A'.$rowCount)->getFont()->setBold( true );
            $rowCount++;
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $value['warehouse'])->getStyle('A'.$rowCount)->getFont()->setBold( true );
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
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'Customer')->getStyle('C'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'Quantity')->getStyle('D'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'Unit')->getStyle('E'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, 'Rate')->getStyle('F'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, 'Amount')->getStyle('G'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, 'Discount')->getStyle('H'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, 'Tax Amount')->getStyle('I'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount, 'Total Amount')->getStyle('J'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':K'.$rowCount)->applyFromArray(
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
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value_1['partner_name']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value_1['qty']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $value_1['unit']);
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $value_1['rate']);
                $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $value_1['amount']);
                $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $value_1['discount_amount']);
                $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, $value_1['tax_amount']);
                $objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount, $value_1['total_amount']);
                $rowCount++;
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="sale_report.xlsx"');
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
            ->setTitle("Sale Report");

        $objPHPExcel->data = array(
            'company_name' => $session['company_name'],
            'report_name' => 'Sale Report',

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
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Sale Report');
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
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'Warehouse')->getStyle('C'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'Quantity')->getStyle('D'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'Unit')->getStyle('E'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, 'Rate')->getStyle('F'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, 'Amount')->getStyle('G'.$rowCount)->getFont()->setBold( true );
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, 'Discount')->getStyle('H'.$rowCount)->getFont()->setBold( true );
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
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value_1['partner_name']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value_1['warehouse']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value_1['qty']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $value_1['unit']);
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $value_1['rate']);
                $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $value_1['amount']);
                $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $value_1['discount_amount']);
                $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, $value_1['total_amount']);
                $rowCount++;
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="sale_report.xlsx"');
        header('Cache-Control: max-age=0');
        //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        //$objWriter->save('some_excel_file.xlsx');
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
            $filter[] = "`partner_id` IN (". implode(",", $post['partner_id']) .")";
        }
        if($post['warehouse_id']){
            $filter[] = "`warehouse_id` = '" . $post['warehouse_id'] . "'";
        }
        if($post['product_id']){
            $filter[] = "`product_id` IN (" . implode(",", $post['product_id']) . ")";
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
        foreach($rows as $row) {
            if(!isset($invoices[$row['document_date']])) {
                $invoices[$row['document_date']] = array(
                    'document_date' => $row['document_date'],
                    'document_identity' => $row['document_identity'],
                    'data' => array()
                );
            }
            $invoices[$row['document_date']]['data'][] = $row;
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
        $pdf = new PDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Sale Invoice Report');
        $pdf->SetSubject('Sale Invoice Report');
        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'group_by' => 'document_date',
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10, 40, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // set font
        $pdf->SetFont('helvetica', '', 8);
        $grand_total = 0;
        $pdf->AddPage();
        foreach($invoices as $row) {
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(143, 7,'Document Date: ' . stdDate($row['document_date']), 0, false, 'L', 1, '', 0, false, 'M', 'M');
            $pdf->SetFont('helvetica', '', 8);
            $sr =0;
            $total_qty = 0;
            $amount = 0;
            $total_dis_amount = 0;
            $total_tax_amount = 0;
            $total_amount = 0;
            $pdf->Ln(1);
            foreach($row['data'] as $detail) {
                $sr++;
                $pdf->Ln(6);
                $pdf->Cell(7, 6, $sr, 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, $detail['document_identity'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(52, 6, $detail['product_name'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(30, 6, html_entity_decode($detail['partner_name']), 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(30, 6, html_entity_decode($detail['warehouse']), 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(17, 6, number_format($detail['qty'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6, $detail['unit'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(17, 6, number_format($detail['rate'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, number_format($detail['amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(17, 6, number_format($detail['discount_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, number_format($detail['tax_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, number_format($detail['total_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');

                $total_qty += $detail['qty'];
                $amount += $detail['amount'];
                $total_dis_amount += $detail['discount_amount'];
                $total_tax_amount += $detail['tax_amount'];
                $total_amount += $detail['total_amount'];
            }
            $pdf->Ln(6);
            $pdf->Cell(144, 6,'', 0, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(17, 6, number_format($total_qty,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(32, 6, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 6, number_format($amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(17, 6, number_format($total_dis_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 6, number_format($total_tax_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 6, number_format($total_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $grand_total += $total_amount;
            $pdf->Ln(12);
        }
        // $pdf->Ln(-6);
        // $pdf->SetFont('helvetica', 'B', 8);
        // $pdf->Cell(260, 6,'', 0, false, 'R', 0, '', 1, false, 'M', 'M');
        // $pdf->Cell(25, 6, number_format($grand_total,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');

        //Close and output PDF document
        $pdf->Output('Sale Invoice Report:'.date('YmdHis').'.pdf', 'I');
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

        $session = $this->session->data;
        $company_logo = $setting['value'];

        $pdf = new PDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Sale Invoice Report');
        $pdf->SetSubject('Sale Invoice Report');

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'group_by' => 'partner',
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10, 35, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // set font
        $pdf->SetFont('helvetica', '', 8);
        $grand_total = 0;
        $pdf->AddPage();
        foreach($invoices as $row) {
            $pdf->ln(5);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(282, 7,'Customer : ' . html_entity_decode($row['partner_name']), 0, false, 'L', 1, '', 0, false, 'M', 'M');
            $pdf->SetFont('helvetica', '', 8);
            // $pdf->ln(7);
            $sr =0;
            $total_qty = 0;
            $amount = 0;
            $total_dis_amount = 0;
            $total_tax_amount = 0;
            $total_amount = 0;

            $pdf->Ln(1);
            foreach($row['data'] as $detail) {
                $sr++;
                $pdf->Ln(6);
                $pdf->Cell(7, 6, $sr, 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6, stdDate($detail['document_date']), 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, $detail['document_identity'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(65, 6, $detail['product_name'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, $detail['warehouse'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, number_format($detail['qty'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6, $detail['unit'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, number_format($detail['rate'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, number_format($detail['amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, number_format($detail['discount_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, number_format($detail['tax_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, number_format($detail['total_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');

                $total_qty += $detail['qty'];
                $amount += $detail['amount'];
                $total_dis_amount += $detail['discount_amount'];
                $total_tax_amount += $detail['tax_amount'];
                $total_amount += $detail['total_amount'];
            }
            $pdf->Ln(6);
            $pdf->Cell(132, 6,'', 0, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 6, number_format($total_qty,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(35, 6, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 6, number_format($amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 6, number_format($total_dis_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 6, number_format($total_tax_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 6, number_format($total_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $grand_total += $total_amount;
            $pdf->ln(7);
        }

        // $pdf->Ln(6);
        // $pdf->SetFont('helvetica', 'B', 8);
        // $pdf->Cell(257, 6,'', 0, false, 'R', 0, '', 1, false, 'M', 'M');
        // $pdf->Cell(25, 6, number_format($grand_total,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');

        //Close and output PDF document
        $pdf->Output('Sale Invoice Report:'.date('YmdHis').'.pdf', 'I');
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
        // d($invoices, true);
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

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'group_by' => 'warehouse'
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10, 35, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // set font
        $pdf->SetFont('helvetica', '', 8);
        $grand_total = 0;
        $pdf->AddPage();
        foreach($invoices as $row) {
            $pdf->ln(5);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(282, 7,'Warehouse : ' . $row['warehouse'], 0, false, 'L', 1, '', 0, false, 'M', 'M');
            $pdf->SetFont('helvetica', '', 8);
            // $pdf->ln(7);
            $sr =0;
            $total_qty = 0;
            $amount = 0;
            $total_dis_amount = 0;
            $total_tax_amount = 0;
            $total_amount = 0;
            $pdf->Ln(1);
            foreach($row['data'] as $detail) {
                $sr++;
                $pdf->Ln(6);
                $pdf->Cell(7, 6, $sr, 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6, stdDate($detail['document_date']), 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, $detail['document_identity'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(55, 6, $detail['product_name'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(30, 6, $detail['partner_name'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, number_format($detail['qty'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6, $detail['unit'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, number_format($detail['rate'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, number_format($detail['amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, number_format($detail['discount_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, number_format($detail['tax_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, number_format($detail['total_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');

                $total_qty += $detail['qty'];
                $amount += $detail['amount'];
                $total_dis_amount += $detail['discount_amount'];
                $total_tax_amount += $detail['tax_amount'];
                $total_amount += $detail['total_amount'];

            }
            $pdf->Ln(6);
            $pdf->Cell(132, 6,'', 0, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 6, number_format($total_qty,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(35, 6, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 6, number_format($amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 6, number_format($total_dis_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 6, number_format($total_tax_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 6, number_format($total_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $grand_total += $total_amount;
            $pdf->Ln(10);  
        }

        // $pdf->Ln(6);
        // $pdf->SetFont('helvetica', 'B', 8);
        // $pdf->Cell(257, 6,'', 0, false, 'R', 0, '', 1, false, 'M', 'M');
        // $pdf->Cell(25, 6, number_format($grand_total,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');

        //Close and output PDF document
        $pdf->Output('Sale Invoice Report:'.date('YmdHis').'.pdf', 'I');
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

        $session = $this->session->data;
        $company_logo = $setting['value'];

        $pdf = new PDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Sale Invoice Report');
        $pdf->SetSubject('Sale Invoice Report');

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'group_by' => 'product'
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10, 35, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // set font
        $pdf->SetFont('helvetica', '', 8);
        $grand_total = 0;
        $pdf->AddPage();
        foreach($invoices as $row) {
            $pdf->ln(5);
            $pdf->SetFont('helvetica', 'B', 10);


            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(141, 7,'Product Name: ' . $row['product_name'], 0, false, 'L', 1, '', 0, false, 'M', 'M');
            $pdf->ln(6);
            $pdf->Cell(141, 7,'Product Code: ' . $row['product_code'], 0, false, 'L', 1, '', 0, false, 'M', 'M');


            // $pdf->Cell(0,9,'Product Name: ' . $row['product_name']);
            // $pdf->ln(5);
            // $pdf->Cell(50,9,'Product Code: ' . $row['product_code']);

            $pdf->SetFont('helvetica', '', 8);
            // $pdf->ln(7);
            $sr =0;
            $total_qty = 0;
            $amount = 0;
            $total_dis_amount = 0;
            $total_tax_amount = 0;
            $total_amount = 0;
            $pdf->Ln(1);
            foreach($row['data'] as $detail) {
                $sr++;
                $pdf->Ln(6);
                $pdf->Cell(7, 6, $sr, 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, stdDate($detail['document_date']), 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, $detail['document_identity'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(60, 6, html_entity_decode($detail['partner_name']), 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, html_entity_decode($detail['warehouse']), 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, number_format($detail['qty'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 6, $detail['unit'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, number_format($detail['rate'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, number_format($detail['amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 6, number_format($detail['discount_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, number_format($detail['tax_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 6, number_format($detail['total_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');

                $total_qty += $detail['qty'];
                $amount += $detail['amount'];
                $total_dis_amount += $detail['discount_amount'];
                $total_tax_amount += $detail['tax_amount'];
                $total_amount += $detail['total_amount'];
            }
            $pdf->Ln(6);
            $pdf->Cell(132, 6,'', 0, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 6, number_format($total_qty,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(35, 6, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 6, number_format($amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 6, number_format($total_dis_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 6, number_format($total_tax_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 6, number_format($total_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $grand_total += $total_amount;
            $pdf->Ln(6);
        }

        // $pdf->Ln(6);
        // $pdf->SetFont('helvetica', 'B', 8);
        // $pdf->Cell(257, 6,'', 0, false, 'R', 0, '', 1, false, 'M', 'M');
        // $pdf->Cell(25, 6, number_format($grand_total,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');

        //Close and output PDF document
        $pdf->Output('Sale Invoice Report:'.date('YmdHis').'.pdf', 'I');
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

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
//            'company_logo' => $company_logo
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(15, 35, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // set font
        $pdf->SetFont('helvetica', '', 8);
        $pdf->AddPage();
        foreach($invoices as $row) {
            $pdf->ln(5);
            $pdf->Cell(0,10,'Container No: ' . $row['container_no']);

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
            $this->Image($image_file, 10, 10, 30, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        // Set font
        $this->SetFont('times', 'B', 20);
        $this->Ln(2);
        // Title
        $this->Cell(0, 10, $this->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);
        $this->Cell(0, 10, $this->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->SetFont('helvetica', '', 8);
        $this->ln(14);
        if($this->data['group_by'] == 'document_date')
        {
            $this->Cell(7, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, 'Doc. No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(52, 7, 'Product', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(30, 7, 'Customer', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(30, 7, 'Warehouse', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(17, 7, 'Quantity', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(15, 7, 'Unit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(17, 7, 'Rate', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, 'Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(17, 7, 'Dis Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, 'Tax Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, 'Total Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        }
        else if($this->data['group_by'] == 'partner')
        {
            $this->Cell(7, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(15, 7, 'Doc. Date', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, 'Doc. No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(65, 7, 'Product', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(20, 7, 'Warehouse', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(20, 7, 'Quantity', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(15, 7, 'Unit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(20, 7, 'Rate', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, 'Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(20, 7, 'Dis Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, 'Tax Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, 'Total Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        }
        else if($this->data['group_by'] == 'warehouse')
        {
            $this->Cell(7, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(15, 7, 'Doc. Date', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, 'Doc. No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(55, 7, 'Product', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(30, 7, 'Customer', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(20, 7, 'Quantity', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(15, 7, 'Unit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(20, 7, 'Rate', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, 'Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(20, 7, 'Dis Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, 'Tax Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, 'Total Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        }
        else if($this->data['group_by'] == 'product')
        {
            $this->Cell(7, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(20, 7, 'Doc. Date', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, 'Doc. No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(60, 7, 'Partner', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(20, 7, 'Warehouse', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(20, 7, 'Quantity', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(15, 7, 'Unit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(20, 7, 'Rate', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, 'Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(20, 7, 'Dis Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, 'Tax Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, 'Total Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
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