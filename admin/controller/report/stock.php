<?php

class ControllerReportStock extends HController {

    protected function getAlias() {
        return 'report/stock';
    }

    protected function init() {
        $this->model['stock'] = $this->load->model('common/stock_ledger');
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

        $this->model['brand'] = $this->load->model('inventory/brand');
        $this->data['brands'] = $this->model['brand']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['product'] = $this->load->model('inventory/product');
        $this->data['products'] = $this->model['product']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
        $this->data['date_from'] = stdDate($this->session->data['fiscal_date_from']);
        $this->data['date_to'] = stdDate(($this->session->data['fiscal_date_to'] > date('Y-m-d') ? '' : $this->session->data['fiscal_date_to']));

        $this->data['strValidation'] = "{
                'rules': {
                    'date_from': {'required': true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},
                    'date_to': {'required': true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},
                },
            }";

        $this->data['href_get_product_json'] = $this->url->link($this->getAlias() . '/getProductJson', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_get_detail_report'] = $this->url->link($this->getAlias() .'/getDetailReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_get_summary_report'] = $this->url->link($this->getAlias() .'/getSummaryReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_print_warehouse_detail'] = $this->url->link($this->getAlias() .'/printWarehouseDetail', 'token=' . $this->session->data['token'], 'SSL');
       // $this->data['href_print_brand_wise'] = $this->url->link($this->getAlias() .'/printBrandWise', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_print_report_group_by'] = $this->url->link($this->getAlias() .'/printReportGroupBy', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_print_warehouse_summary'] = $this->url->link($this->getAlias() .'/printWarehouseSummary', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_print_container_detail'] = $this->url->link($this->getAlias() .'/printContainerDetail', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_print_container_summary'] = $this->url->link($this->getAlias() .'/printContainerSummary', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_print_excel'] = $this->url->link($this->getAlias() .'/printReportExcel', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_print_excel_summary'] = $this->url->link($this->getAlias() .'/printSummaryExcel', 'token=' . $this->session->data['token'], 'SSL');
        $this->template = $this->getAlias() . '.tpl';
        $this->response->setOutput($this->render());
    }

    public function getProductJson() {
        $search = $this->request->post['q'];
        $page = $this->request->post['page'];
        $product_category_id = $this->request->post['product_category_id'];

        $this->model['product'] = $this->load->model('inventory/product');
        $rows = $this->model['product']->getProductJson($search, $page, 25, array('product_category_id' => $product_category_id));

        echo json_encode($rows);
    }

    public function getDetailReport() {
        $post = $this->request->post;
        $session = $this->session->data;
        $this->model['stock'] = $this->load->model('common/stock_ledger');
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
        if($post['warehouse_id'])
            $arrWhere[] = "`warehouse_id` = '" . $post['warehouse_id'] . "'";
        if($post['product_id'])
            $arrWhere[] = "`product_id` = '" . $post['product_id'] . "'";
        if($post['brand_id'])
            $arrWhere[] = "`brand_id` = '" . $post['brand_id'] . "'";
        if($post['product_category_id'])
            $arrWhere[] = "`product_category_id` = '" . $post['product_category_id'] . "'";

        $where = implode(' AND ', $arrWhere);

        $rows = $this->model['stock']->getRows($where);
        // d($rows, true);
        $html = '';
        foreach($rows as $row) {
            $html .= '<tr>';
            $html .= '<td>'.stdDate($row['document_date']).'</td>';
            $html .= '<td>'.$row['document_identity'].'</td>';
            $html .= '<td>'.$row['warehouse'].'</td>';
            $html .= '<td>'.$row['product_category'].'</td>';
            $html .= '<td>'.$row['product_code'].'</td>';
            $html .= '<td>'.$row['product_name'].'</td>';
            $html .= '<td>'.$row['base_unit'].'</td>';
            $html .= '<td>'.$row['base_qty'].'</td>';
            $html .= '<td>'.$row['base_rate'].'</td>';
            $html .= '<td>'.$row['base_amount'].'</td>';
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

    public function printReportGroupBy() {
        $this->init();
        ini_set('memory_limit','1024M');
        $post = $this->request->post;

        $filter = array();
        $filter[] = "`company_id` = '".$this->session->data['company_id']."'";
        $filter[] = "`company_branch_id` = '".$this->session->data['company_branch_id']."'";
        $filter[] = "`fiscal_year_id` = '".$this->session->data['fiscal_year_id']."'";

        if($post['warehouse_id']){
            $filter[] = "`warehouse_id` = '" . $post['warehouse_id'] . "'";
        }
        if($post['product_id']){
            $filter[] = "`product_id` = '" . $post['product_id'] . "'";
        }
        if($post['product_category_id']){
            $filter[] = "`product_category_id` = '" . $post['product_category_id'] . "'";
        }
        if($post['brand_id']){
            $filter[] = "`brand_id` = '" . $post['brand_id'] . "'";
        }

        $where = implode(' AND ', $filter);
        //d($post, true);
        $this->model['stock'] = $this->load->model('common/stock_ledger');
        $rows = $this->model['stock']->getRows($where, array('created_at'));
        if($post['group_by']=='warehouse') {
            $this->pdfWarehouseWise($rows);
        } elseif($post['group_by']=='product') {
            $this->pdfProductWise($rows);
        }elseif($post['group_by']=='brand') {
            $this->pdfBrandWise($rows);
        }
    }




    public function printSummaryExcel()
    {
        ini_set('max_execution_time',400);
        $lang = $this->load->language($this->getAlias());

        $post = $this->request->post;
        $session = $this->session->data;

        $this->model['company'] = $this->load->model('setup/company');
        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->model['product_category'] = $this->load->model('inventory/product_category');
        $this->model['product'] = $this->load->model('inventory/product');
        $this->model['product'] = $this->load->model('inventory/product');
        $this->model['stock'] = $this->load->model('common/stock_ledger');

        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        $arrWarehouses = $this->model['warehouse']->getArrays('warehouse_id','name',array('company_id' => $this->session->data['company_id'], 'company_branch_id' => $this->session->data['company_branch_id']));
        $arrProductCategories = $this->model['product_category']->getArrays('product_category_id','name',array('company_id' => $this->session->data['company_id']));
        $arrProducts = $this->model['product']->getArrays('product_id','name',array('company_id' => $this->session->data['company_id']));

        $arrWhere = array();
        $arrWhere[] = "`company_id` = '".$session['company_id']."'";
        $arrWhere[] = "`company_branch_id` = '".$session['company_branch_id']."'";
        $arrWhere[] = "`fiscal_year_id` = '".$session['fiscal_year_id']."'";
        if($post['date_from'] != '') {
            $arrWhere[] = "`document_date` >= '".MySqlDate($post['date_from'])."'";
            $filter['date_from'] = $post['date_from'];
        }
        if($post['date_to'] != '') {
            $arrWhere[] = "`document_date` <= '".MySqlDate($post['date_to'])."'";
            $filter['date_to'] = $post['date_to'];
        }
        if($post['warehouse_id']) {
            $arrWhere[] = "`warehouse_id` = '" . $post['warehouse_id'] . "'";
            $filter['warehouse'] = $arrWarehouses[$post['warehouse_id']];
        }
        if($post['product_category_id']) {
            $arrWhere[] = "`product_category_id` = '" . $post['product_category_id'] . "'";
            $filter['product_category'] = $arrProductCategories[$post['product_category_id']];
        }
        if($post['product_id']) {
            $arrWhere[] = "`product_id` = '" . $post['product_id'] . "'";
            $filter['product'] = $arrProducts[$post['product_id']];
        }
        if($post['brand_id']) {
            $arrWhere[] = "`brand_id` = '" . $post['brand_id'] . "'";
            $filter['brand_id'] = $arrProducts[$post['brand_id']];
        }
        $where = implode(' AND ', $arrWhere);

        $rows = $this->model['stock']->getRows($where, array('`document_date`', 'sfd_sort_order','created_at','sort_order'));

        $stocks = array();
        foreach($rows as $row) {
            if(!isset($stocks[$row['warehouse']][$row['product_id']])) {
                $stocks[$row['warehouse']][$row['product_id']] = array(
                    'warehouse' => $row['warehouse'],
                    'product_code' => $row['product_code'],
                    'product_name' => $row['product_name'],
                    'total_qty' => 0,
                    'total_amount' => 0,
                    'unit' => $row['document_unit'],
                );
            }

            $stocks[$row['warehouse']][$row['product_id']]['total_qty'] += $row['base_qty'];
            $stocks[$row['warehouse']][$row['product_id']]['total_amount'] += $row['base_amount'];
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

        $company_logo = $setting['value'];

            $objPHPExcel = new PHPExcel();
            $objPHPExcel->setActiveSheetIndex(0);
            
            $objPHPExcel->getProperties()
                ->setCreator('Farrukh Afaq')
                ->setLastModifiedBy('Farrukh Afaq')
                ->setTitle('Stock Ledger');

                $objPHPExcel->data = array(
                    'company_name' => $session['company_name'],
                    'report_name' => $lang['heading_title'],
                'company_logo' =>$company_logo
            );

            
            $rowcount = 1;

            $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':F'.$rowcount);
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,$session['company_name']);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount)->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                    ),
                    'font' => array(
                        'size' => 20,
                        'bold' => true
                    ),
                    'fill' =>array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array( 'rgb' => 'ebebeb')
                    )
                )
            );
            $rowcount++;


            $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':F'.$rowcount);
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount, $lang['heading_title']);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount)->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                    ),
                    'font' => array(
                        'size' => 20,
                        'bold' => true
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array( 'rgb' => 'ebebeb' )
                    )
                )
            );
            $rowcount++;

            $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':F'.$rowcount);
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount, $filter['date_from'] . ' - ' . $filter['date_to']);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount)->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                    ),
                    'font' => array(
                        'size' => 14,
                        'bold' => true
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array( 'rgb' => 'ebebeb' )
                    )
                )
            );
            $rowcount++;
            
            $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':F'.$rowcount);
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount, 'Warehouse: '.$filter['warehouse'].' | Product Category: '.$filter['product_category']);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount)->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                    ),
                    'font' => array(
                        'size' => 14,
                        'bold' => true
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array( 'rgb' => 'ebebeb' )
                    )
                    )
            );
            $rowcount++;
            

            $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':F'.$rowcount);
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount, 'Brand: '.$filter['brand_id']);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount)->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                    ),
                    'font' => array(
                        'size' => 14,
                        'bold' => true
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array( 'rgb' => 'ebebeb' )
                    )
                    )
            );
            $rowcount++;
            
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(60);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':'.'F'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount, 'Sr.')->getStyle('A'.$rowcount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount, 'Product')->getStyle('B'.$rowcount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount, 'Unit')->getStyle('C'.$rowcount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount, 'Qty')->getStyle('D'.$rowcount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount, 'Amount')->getStyle('E'.$rowcount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount, 'Avg Cost')->getStyle('F'.$rowcount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':F'.$rowcount)->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array( 'rgb' => 'ebebeb' ),
                        'bold'=> true
                    ),
                    'borders' => array(
                        'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                    )
                )
            );
            $rowcount++;
            
            // d($stocks,true);
            foreach($stocks as $warehouse => $products) {
                $balance_qty = 0;
                $balance_amount = 0;
                $sr=0;
                $rowcount++;
                
                
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':F'.$rowcount);
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,"Warehouse: ". $warehouse)->getStyle('A'.$rowcount)->getFont()->setBold(true)->setSize(13);
                // set font
                foreach($products as $detail) {
                    if($detail['total_qty'] >= 0.1) {
                        $balance_qty += $detail['total_qty'];
                        $balance_amount += $detail['total_amount'];
                        $sr++;
                        $rowcount++;
                        $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,$sr)->getStyle('A'.$rowcount)->getAlignment(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount,$detail['product_code'] .' - '.$detail['product_name'])->getStyle('B'.$rowcount)->getAlignment(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,$detail['unit'])->getStyle('C'.$rowcount)->getAlignment(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,$detail['total_qty'])->getStyle('D'.$rowcount)->getAlignment(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,$detail['total_amount'])->getStyle('E'.$rowcount)->getAlignment(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,$detail['total_amount']/$detail['total_qty'])->getStyle('F'.$rowcount)->getAlignment(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':F'.$rowcount)->applyFromArray(
                            array(
                                'borders' => array(
                                    'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                                    )
                                    )
                                );
                            }
                        }
                        $rowcount++;
                        $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,$balance_qty)->getStyle('D'.$rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,$balance_amount)->getStyle('E'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':F'.$rowcount)->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array( 'rgb' => 'ebebeb')
                        ),
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                        ),
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        )
                    )
                );
                $rowcount += 2;

            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="Product Summary|Report.xlsx"');
            header('Cache-Control: max-age=0');
            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $objWriter->save('php://output');
            exit;

        
    }
/////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function printReportExcel()
    {
       
 ini_set('max_execution_time',400);
        $lang = $this->load->language($this->getAlias());

        $post = $this->request->post;
        $session = $this->session->data;

        $this->model['company'] = $this->load->model('setup/company');
        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->model['product_category'] = $this->load->model('inventory/product_category');
        $this->model['product'] = $this->load->model('inventory/product');
        $this->model['product'] = $this->load->model('inventory/product');
        $this->model['stock'] = $this->load->model('common/stock_ledger');

        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        $arrWarehouses = $this->model['warehouse']->getArrays('warehouse_id','name',array('company_id' => $this->session->data['company_id'], 'company_branch_id' => $this->session->data['company_branch_id']));
        $arrProductCategories = $this->model['product_category']->getArrays('product_category_id','name',array('company_id' => $this->session->data['company_id']));
        $arrProducts = $this->model['product']->getArrays('product_id','name',array('company_id' => $this->session->data['company_id']));

        $arrWhere = array();
        $arrWhere[] = "`company_id` = '".$session['company_id']."'";
        $arrWhere[] = "`company_branch_id` = '".$session['company_branch_id']."'";
        $arrWhere[] = "`fiscal_year_id` = '".$session['fiscal_year_id']."'";
        if($post['date_from'] != '') {
            $arrWhere[] = "`document_date` >= '".MySqlDate($post['date_from'])."'";
            $filter['date_from'] = $post['date_from'];
        }
        if($post['date_to'] != '') {
            $arrWhere[] = "`document_date` <= '".MySqlDate($post['date_to'])."'";
            $filter['date_to'] = $post['date_to'];
        }
        if($post['warehouse_id']) {
            $arrWhere[] = "`warehouse_id` = '" . $post['warehouse_id'] . "'";
            $filter['warehouse'] = $arrWarehouses[$post['warehouse_id']];
        }
        if($post['product_category_id']) {
            $arrWhere[] = "`product_category_id` = '" . $post['product_category_id'] . "'";
            $filter['product_category'] = $arrProductCategories[$post['product_category_id']];
        }
        if($post['product_id']) {
            $arrWhere[] = "`product_id` = '" . $post['product_id'] . "'";
            $filter['product'] = $arrProducts[$post['product_id']];
        }
        if($post['container_no']) {
            $arrWhere[] = "`container_no` LIKE '%" . $post['container_no'] . "%'";
            $filter['container_no'] = $post['container_no'];
        }
        if($post['brand_id']) {
            $arrWhere[] = "`brand_id` = '". $post['brand_id'] . "'";
            $filter['brand_id'] = $post['brand_id'];
        }


        $where = implode(' AND ', $arrWhere);
        $rows = $this->model['stock']->getRows($where, array('`document_date`', 'sfd_sort_order','created_at','sort_order'));
        // d($rows,true);
        $stocks = array();
        foreach($rows as $row) {
            $stocks[$row['warehouse'] . '     Brand : '. $row['brand']][$row['product_code'] . ' - ' . $row['product_name']][] = array(
                'container_no' => $row['container_no'],
                'batch_no' => $row['batch_no'],
                'product_code' => $row['product_code'],
                'document_date' => stdDate($row['document_date']),
                'document_identity' => $row['document_identity'],
                'qty' => $row['base_qty'],
                'unit' => $row['document_unit'],
                'rate' => $row['base_rate'],
                'amount' => $row['base_amount'],
                'created_at' => $row['created_at']
            );
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

        //$company_logo = $this->model['image']->resize($setting['value'],200,50);
        $company_logo = $setting['value'];


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
                        'size' => 20,
                        'bold' => true
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
                        'size' => 20,
                        'bold' => true
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array( 'rgb' => 'ebebeb' )
                    )
                )
            );
            $rowcount++;


            $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':G'.$rowcount);
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount, $filter['date_from'] . ' - ' . $filter['date_to']);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount)->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                    ),
                    'font' => array(
                        'size' => 14,
                        'bold' => true
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array( 'rgb' => 'ebebeb' )
                    )
                )
            );
            $rowcount++;


            $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':G'.$rowcount);
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount, 'Warehouse: '.$filter['warehouse'].' | Product Category: '.$filter['product_category']);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount)->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                    ),
                    'font' => array(
                        'size' => 14,
                        'bold' => true
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array( 'rgb' => 'ebebeb' )
                    )
                )
            );
            $rowcount++;


            $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':G'.$rowcount);
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount, 'Brand: '.$filter['brand_id']);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount)->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                    ),
                    'font' => array(
                        'size' => 14,
                        'bold' => true
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array( 'rgb' => 'ebebeb' )
                    )
                )
            );
            $rowcount++;

                    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(35);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
                    
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':'.'G'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,'Sr. No.')->getStyle('A'.$rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount,'Doc. Date')->getStyle('B'.$rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,'Doc. No.')->getStyle('C'.$rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,'Qty')->getStyle('D'.$rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,'Rate')->getStyle('E'.$rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,'Amount')->getStyle('F'.$rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount,'Balance Qty')->getStyle('G'.$rowcount)->getFont()->setBold(true);
                    // $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount,'Created At')->getStyle('H'.$rowcount)->getFont()->setBold(true);
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

            $rowcount++;
            $rowcount++;
        


            foreach($stocks as $warehouse => $products) {
                foreach($products as $product_name => $rows) {
                    $rowcount++;
                    
                    $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':G'.$rowcount);
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,'Product:    '.$product_name)->getStyle('A'.$rowcount)->getFont()->setBold(true)->setSize(13);
                    $rowcount++;
        

                    $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':G'.$rowcount);
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,'Warehouse: '.$warehouse)->getStyle('A'.$rowcount)->getFont()->setBold(true)->setSize(13);
                    $rowcount++;


                    $sr = 0;
                    $balance_qty = 0;
                    $balance_amount = 0;

                    foreach($rows as $detail) {
                        $rowcount++;
                        $balance_qty += $detail['qty'];
                        $balance_amount += $detail['amount'];
                        $avg_rate = ($balance_amount / $balance_qty);
                        $sr++;

                        $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,$sr);
                        $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount,$detail['document_date']);
                        $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,$detail['document_identity']);
                        $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,$detail['qty']);
                        $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,number_format($detail['rate'],2));
                        $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,number_format($detail['amount'],2));
                        $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount,number_format($balance_qty,2));
                        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':G'.$rowcount)->applyFromArray(
                            array(
                                'borders' => array(
                                    'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                                ),
                                'alignment' => array(
                                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                                )
                            )
                        );

                    }
                    $rowcount++;
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount, number_format($balance_qty),2)->getStyle('D'.$rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount, number_format($balance_amount),2)->getStyle('F'.$rowcount)->getFont()->setBold(true);
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
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                            )
                        )
                    );
                    $rowcount +=2;
                }
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="Stock|Report.xlsx"');
            header('Cache-Control: max-age=0');
            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $objWriter->save('php://output');
            exit;

    }





    private function pdfWarehouseWise($rows) {
        //d($rows, true);
        $post = $this->request->post;
        $invoices = array();
        foreach($rows as $row) {
            //d($row,true);
            if(!isset($invoices[$row['warehouse']])) {
                $invoices[$row['warehouse']] = array(
                    'container_no' => $row['container_no'],
                    'batch_no' => $row['batch_no'],
                    'product_code' => $row['product_code'],
                    'document_date' => stdDate($row['document_date']),
                    'document_identity' => $row['document_identity'],
                    'qty' => $row['base_qty'],
                    'warehouse' => $row['warehouse'],
                    'unit' => $row['unit'],
                    'rate' => $row['base_rate'],
                    'amount' => $row['base_amount'],
                    'created_at' => $row['created_at'],
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

        $session = $this->session->data;
        $company_logo = $setting['value'];

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

            foreach($invoices as $row) {
                $rowcount++;
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':I'.$rowcount);
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
                    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':'.'I'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);



                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,'Sr. No.')->getStyle('A'.$rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount,'Doc. Date')->getStyle('B'.$rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,'Doc. No.')->getStyle('C'.$rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,'Product')->getStyle('D'.$rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,'Qty')->getStyle('E'.$rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,'Rate')->getStyle('F'.$rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount,'Amount')->getStyle('G'.$rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount,'Balance Qty')->getStyle('H'.$rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount,'Created At')->getStyle('I'.$rowcount)->getFont()->setBold(true);
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
                    $balance_qty = 0;
                    $balance_amount = 0;

                    foreach($row['data'] as $detail) {
                        $rowcount++;
                        $balance_qty += $detail['base_qty'];
                        $balance_amount += $detail['base_amount'];
                        $avg_rate = ($balance_amount / $balance_qty);
                        $sr++;

                        $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,$sr)->getStyle('A'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount,stdDate($detail['document_date']))->getStyle('B'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,$detail['document_identity'])->getStyle('C'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,$detail['product_name'])->getStyle('D'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,number_format($detail['base_qty'],2))->getStyle('E'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,number_format($detail['base_rate'],2))->getStyle('F'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount,number_format($detail['base_amount'],2))->getStyle('G'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount,number_format($balance_qty,2))->getStyle('H'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount,$detail['created_at'])->getStyle('I'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':I'.$rowcount)->applyFromArray(
                            array(
                                'borders' => array(
                                    'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                                )
                            )
                        );
                    }

           $rowcount++;

                $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount, number_format($balance_qty,2))->getStyle('E'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount, number_format($balance_amount),2)->getStyle('G'.$rowcount)->getFont()->setBold(true);
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
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                        )
                    )
                );
                $rowcount +=3;

    }


            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="Stock|Report.xlsx"');
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
        $pdf->SetTitle('Stock Report');
        $pdf->SetSubject('Stock Report');

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $company_logo
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(3,60,3);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->AddPage();
        // set font
        $pdf->SetFont('helvetica', '', 8);
        foreach($invoices as $row) {
            //d($row,true);
            //$pdf->AddPage();

            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(202, 7,'Warehouse: ' . $row['warehouse'], 1, false, 'C', 1, '', 0, false, 'M', 'M');
            $pdf->Ln(9);

            // $pdf->SetFont('helvetica', 'B', 10);
            // $pdf->Cell(0,9,'Warehouse : ' . $row['warehouse']);
            // $pdf->SetFont('helvetica', '', 8);
//d($row,true);
            // $pdf->ln(15);
            $pdf->Cell(7, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, $lang['document_date'], 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, $lang['document_no'], 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(60, 7, $lang['product'], 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(15, 7, $lang['qty'], 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, $lang['rate'], 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, $lang['amount'], 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, 'Balance Qty', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, $lang['created_at'], 1, false, 'C', 0, '', 0, false, 'M', 'M');

            $sr =0;
            $balance_qty = 0;
            $balance_amount = 0;
            foreach($row['data'] as $detail) {
                //d($detail,true);
                $balance_qty += $detail['base_qty'];
                $balance_amount += $detail['base_amount'];
                $avg_rate = ($balance_amount / $balance_qty);
                $pdf->SetFont('helvetica', '', 8);
                $sr++;
                $pdf->Ln(7);

                $pdf->Cell(7, 7, $sr, 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 7, $detail['document_date'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 7, $detail['document_identity'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(60, 7, $detail['product_name'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 7, number_format($detail['base_qty'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 7, number_format($detail['base_rate'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 7, number_format($detail['base_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 7, number_format($balance_qty,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 7, stdDateTime($detail['created_at']), 1, false, 'R', 0, '', 1, false, 'M', 'M');

            }
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->Ln(7);
            $pdf->Cell(107, 7, '', 0, false, 'L', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(15, 7, number_format($balance_qty,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 7, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 7, number_format($balance_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->ln(20);
        }

        //Close and output PDF document
        $pdf->Output('Stock Report:'.date('YmdHis').'.pdf', 'I');
    }
}

    private function pdfBrandWise($rows) {
        //d($rows, true);
        $post = $this->request->post;
        $invoices = array();
        foreach($rows as $row) {
            if(!isset($invoices[$row['brand_id']])) {
                $invoices[$row['brand_id']] = array(
                    'container_no' => $row['container_no'],
                    'batch_no' => $row['batch_no'],
                    'product_code' => $row['product_code'],
                    'document_date' => stdDate($row['document_date']),
                    'document_identity' => $row['document_identity'],
                    'qty' => $row['base_qty'],
                    'brand' => $row['brand'],
                    'unit' => $row['unit'],
                    'rate' => $row['base_rate'],
                    'amount' => $row['base_amount'],
                    'created_at' => $row['created_at'],
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

        $session = $this->session->data;
        $company_logo = $setting['value'];

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

            foreach($invoices as $row) {
                $rowcount++;
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':I'.$rowcount);
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,'Warehouse:  '. $row['brand']);
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
                $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':'.'I'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);



                $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,'Sr. No.')->getStyle('A'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount,'Doc. Date')->getStyle('B'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,'Doc. No.')->getStyle('C'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,'Product')->getStyle('D'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,'Qty')->getStyle('E'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,'Rate')->getStyle('F'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount,'Amount')->getStyle('G'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount,'Balance Qty')->getStyle('H'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount,'Created At')->getStyle('I'.$rowcount)->getFont()->setBold(true);
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
                $balance_qty = 0;
                $balance_amount = 0;

                foreach($row['data'] as $detail) {
                    $rowcount++;
                    $balance_qty += $detail['base_qty'];
                    $balance_amount += $detail['base_amount'];
                    $avg_rate = ($balance_amount / $balance_qty);
                    $sr++;

                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,$sr)->getStyle('A'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount,stdDate($detail['document_date']))->getStyle('B'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,$detail['document_identity'])->getStyle('C'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,$detail['product_name'])->getStyle('D'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,number_format($detail['base_qty'],2))->getStyle('E'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,number_format($detail['base_rate'],2))->getStyle('F'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount,number_format($detail['base_amount'],2))->getStyle('G'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount,number_format($balance_qty,2))->getStyle('H'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount,$detail['created_at'])->getStyle('I'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':I'.$rowcount)->applyFromArray(
                        array(
                            'borders' => array(
                                'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                            )
                        )
                    );
                }

                $rowcount++;

                $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount, number_format($balance_qty,2))->getStyle('E'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount, number_format($balance_amount),2)->getStyle('G'.$rowcount)->getFont()->setBold(true);
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
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                        )
                    )
                );
                $rowcount +=3;

            }


            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="Stock|Report.xlsx"');
            header('Cache-Control: max-age=0');
            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $objWriter->save('php://output');
            exit;

        }
        else{

        $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Farrukh Afaq');
        $pdf->SetTitle('Stock Report');
        $pdf->SetSubject('Stock Report');

        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->data['warehouses'] = $this->model['warehouse']->getRow(array('warehouse_id' => $post['warehouse_id']));
        $this->model['product_category'] = $this->load->model('inventory/product_category');
        $this->data['product_category'] = $this->model['product_category']->getRow(array('product_category_id' => $post['product_category_id']));
        $this->model['brand'] = $this->load->model('inventory/brand');
        $this->data['brand'] = $this->model['brand']->getRow(array('brand_id' => $post['brand_id']));

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $company_logo,
            'date_from' => $post['date_from'],
            'date_to' => $post['date_to'],
            'warehouse_id' => $this->data['warehouses']['name'],
            'product_category_id' => $this->data['product_category']['name'],
            'brand_id' => $this->data['brand']['name']
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(5,60,5);
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

            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(200, 7,'Brand: ' . $row['brand'], 1, false, 'C', 1, '', 0, false, 'M', 'M');
            $pdf->Ln(9);

            // $pdf->SetFont('helvetica', 'B', 10);
            // $pdf->Cell(0,9,'Brand: ' . $row['brand']);

            $pdf->SetFont('helvetica', '', 8);
            // $pdf->ln(15);
            $pdf->Cell(7, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(17, 7, $lang['document_date'], 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, $lang['document_no'], 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(60, 7, $lang['product'], 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(15, 7, $lang['qty'], 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, $lang['rate'], 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, $lang['amount'], 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, 'Balance Qty', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, $lang['created_at'], 1, false, 'C', 0, '', 0, false, 'M', 'M');

            $sr =0;
            $balance_qty = 0;
            $balance_amount = 0;
            foreach($row['data'] as $detail) {
                $balance_qty += $detail['base_qty'];
                $balance_amount += $detail['base_amount'];
                $avg_rate = ($balance_amount / $balance_qty);
                $pdf->SetFont('helvetica', '', 8);
                $sr++;
                $pdf->Ln(7);

                $pdf->Cell(7, 7, $sr, 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(17, 7, $detail['document_date'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 7, $detail['document_identity'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(60, 7, $detail['product_name'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 7, number_format($detail['base_qty'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 7, number_format($detail['base_rate'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 7, number_format($detail['base_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 7, number_format($balance_qty,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 7, stdDateTime($detail['created_at']), 1, false, 'R', 0, '', 1, false, 'M', 'M');

            }
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->Ln(7);
            $pdf->Cell(104, 7, '', 0, false, 'L', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(15, 7, number_format($balance_qty,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 7, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 7, number_format($balance_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');

            $pdf->ln(20);
        }
        //Close and output PDF document
        $pdf->Output('Stock Report:'.date('YmdHis').'.pdf', 'I');
    }
}

    private function pdfProductWise($rows) {
        //d($rows, true);
        $post =$this->request->post;
        $invoices = array();
        foreach($rows as $row) {
            //d($row,true);
            if(!isset($invoices[$row['product_id']])) {
                $invoices[$row['product_id']] = array(

                    'container_no' => $row['container_no'],
                    'batch_no' => $row['batch_no'],
                    'product_code' => $row['product_code'],
                    'product_name' => $row['product_name'],
                    'document_date' => stdDate($row['document_date']),
                    'document_identity' => $row['document_identity'],
                    'qty' => $row['base_qty'],
                    'unit' => $row['unit'],
                    'rate' => $row['base_rate'],
                    'amount' => $row['base_amount'],
                    'created_at' => $row['created_at'],
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

            foreach($invoices as $row) {
                $rowcount++;
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':I'.$rowcount);
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

                $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':I'.$rowcount);
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
                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(45);
                $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':'.'I'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);



                $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,'Sr. No.')->getStyle('A'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount,'Doc. Date')->getStyle('B'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,'Doc. No.')->getStyle('C'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,'Product')->getStyle('D'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,'Qty')->getStyle('E'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,'Rate')->getStyle('F'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount,'Amount')->getStyle('G'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount,'Balance Qty')->getStyle('H'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount,'Created At')->getStyle('I'.$rowcount)->getFont()->setBold(true);
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
                $balance_qty = 0;
                $balance_amount = 0;

                foreach($row['data'] as $detail) {
                    $rowcount++;
                    $balance_qty += $detail['base_qty'];
                    $balance_amount += $detail['base_amount'];
                    $avg_rate = ($balance_amount / $balance_qty);
                    $sr++;

                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,$sr)->getStyle('A'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount,stdDate($detail['document_date']))->getStyle('B'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,$detail['document_identity'])->getStyle('C'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,$detail['product_name'])->getStyle('D'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,number_format($detail['base_qty'],2))->getStyle('E'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,number_format($detail['base_rate'],2))->getStyle('F'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount,number_format($detail['base_amount'],2))->getStyle('G'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount,number_format($balance_qty,2))->getStyle('H'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowcount,$detail['created_at'])->getStyle('I'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':I'.$rowcount)->applyFromArray(
                        array(
                            'borders' => array(
                                'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                            )
                        )
                    );
                }

                $rowcount++;

                $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount, number_format($balance_qty,2))->getStyle('E'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount, number_format($balance_amount),2)->getStyle('G'.$rowcount)->getFont()->setBold(true);
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
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                        )
                    )
                );
                $rowcount +=3;

            }


            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="Stock|Report.xlsx"');
            header('Cache-Control: max-age=0');
            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $objWriter->save('php://output');
            exit;

        }
        else{


            $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Farrukh Afaq');
        $pdf->SetTitle('Stock Report');
        $pdf->SetSubject('Stock Report');

        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->data['warehouses'] = $this->model['warehouse']->getRow(array('warehouse_id' => $post['warehouse_id']));
        $this->model['product_category'] = $this->load->model('inventory/product_category');
        $this->data['product_category'] = $this->model['product_category']->getRow(array('product_category_id' => $post['product_category_id']));
        $this->model['brand'] = $this->load->model('inventory/brand');
        $this->data['brand'] = $this->model['brand']->getRow(array('brand_id' => $post['brand_id']));

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $company_logo,
            'date_from' => $post['date_from'],
            'date_to' => $post['date_to'],
            'warehouse_id' => $this->data['warehouses']['name'],
            'product_category_id' => $this->data['product_category']['name'],
            'brand_id' => $this->data['brand']['name']
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10, 60, 5);
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

            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(94, 7,'Product Name: ' . $row['product_name'], 1, false, 'C', 1, '', 0, false, 'M', 'M');
            $pdf->Cell(93, 7,'Product Code: ' . $row['product_code'], 1, false, 'C', 1, '', 0, false, 'M', 'M');
            $pdf->Ln(9);

            // $pdf->SetFont('helvetica', 'B', 10);
            // $pdf->Cell(0,9,'Product Name: ' . $row['product_name']);
            // $pdf->ln(5);
            // $pdf->Cell(50,9,'Product Code: ' . $row['product_code']);
            // $pdf->ln(5);
            // $pdf->SetFont('helvetica', '', 8);
            // $pdf->ln(7);
            $pdf->Cell(7, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, $lang['document_date'], 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, $lang['document_no'], 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, $lang['warehouse'], 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, $lang['brand'], 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(15, 7, $lang['qty'], 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, $lang['rate'], 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, $lang['amount'], 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, 'Balance Qty', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(25, 7, $lang['created_at'], 1, false, 'C', 0, '', 0, false, 'M', 'M');

            $sr =0;
            $balance_qty = 0;
            $balance_amount = 0;
            foreach($row['data'] as $detail) {
                $balance_qty += $detail['base_qty'];
                $balance_amount += $detail['base_amount'];
                $avg_rate = ($balance_amount / $balance_qty);
                $pdf->SetFont('helvetica', '', 8);
                $sr++;
                $pdf->Ln(7);

                $pdf->Cell(7, 7, $sr, 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 7, $detail['document_date'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 7, $detail['document_identity'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 7, $detail['warehouse'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 7, $detail['brand'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(15, 7, number_format($detail['base_qty'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 7, number_format($detail['base_rate'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 7, number_format($detail['base_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 7, number_format($balance_qty,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 7, stdDateTime($detail['created_at']), 1, false, 'R', 0, '', 1, false, 'M', 'M');

            }
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->Ln(7);
            $pdf->Cell(87, 7, '', 0, false, 'L', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(15, 7, number_format($balance_qty,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 7, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 7, number_format($balance_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->ln(20);
        }

        //Close and output PDF document
        $pdf->Output('Stock Report:'.date('YmdHis').'.pdf', 'I');
    }
}

    public function printWarehouseDetail() {
        ini_set('max_execution_time',400);
        $lang = $this->load->language($this->getAlias());

        $post = $this->request->post;
        $session = $this->session->data;
        // d($post,true);

        $this->model['company'] = $this->load->model('setup/company');
        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->model['product_category'] = $this->load->model('inventory/product_category');
        $this->model['product'] = $this->load->model('inventory/product');
        $this->model['product'] = $this->load->model('inventory/product');
        $this->model['stock'] = $this->load->model('common/stock_ledger');

        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        $arrWarehouses = $this->model['warehouse']->getArrays('warehouse_id','name',array('company_id' => $this->session->data['company_id'], 'company_branch_id' => $this->session->data['company_branch_id']));
        $arrProductCategories = $this->model['product_category']->getArrays('product_category_id','name',array('company_id' => $this->session->data['company_id']));
        $arrProducts = $this->model['product']->getArrays('product_id','name',array('company_id' => $this->session->data['company_id']));

        // d($arrWarehouses,true);

        $arrWhere = array();
        $arrWhere[] = "`company_id` = '".$session['company_id']."'";
        $arrWhere[] = "`company_branch_id` = '".$session['company_branch_id']."'";
        $arrWhere[] = "`fiscal_year_id` = '".$session['fiscal_year_id']."'";
        if($post['date_from'] != '') {
            $arrWhere[] = "`document_date` >= '".MySqlDate($post['date_from'])."'";
            $filter['date_from'] = $post['date_from'];
        }
        if($post['date_to'] != '') {
            $arrWhere[] = "`document_date` <= '".MySqlDate($post['date_to'])."'";
            $filter['date_to'] = $post['date_to'];
        }
        if($post['warehouse_id']) {
            $arrWhere[] = "`warehouse_id` = '" . $post['warehouse_id'] . "'";
            $filter['warehouse'] = $arrWarehouses[$post['warehouse_id']];
        }
        if($post['product_category_id']) {
            $arrWhere[] = "`product_category_id` = '" . $post['product_category_id'] . "'";
            $filter['product_category'] = $arrProductCategories[$post['product_category_id']];
        }
        if($post['product_id']) {
            $arrWhere[] = "`product_id` = '" . $post['product_id'] . "'";
            $filter['product'] = $arrProducts[$post['product_id']];
        }
        if($post['container_no']) {
            $arrWhere[] = "`container_no` LIKE '%" . $post['container_no'] . "%'";
            $filter['container_no'] = $post['container_no'];
        }
        if($post['brand_id']) {
            $arrWhere[] = "`brand_id` = '". $post['brand_id'] . "'";
            $filter['brand_id'] = $post['brand_id'];
        }

        //d(array($post, $filter), true);

        $where = implode(' AND ', $arrWhere);
        $rows = $this->model['stock']->getRows($where, array('`document_date`', 'sfd_sort_order','created_at','sort_order'));
        // d($rows,true);
        $stocks = array();
        foreach($rows as $row) {
            $stocks[$row['warehouse'] . '     Brand : '. $row['brand']][$row['product_code'] . ' - ' . $row['product_name']][] = array(
                'container_no' => $row['container_no'],
                'batch_no' => $row['batch_no'],
                'product_code' => $row['product_code'],
                'document_date' => stdDate($row['document_date']),
                'document_identity' => $row['document_identity'],
                'qty' => $row['base_qty'],
                'unit' => $row['document_unit'],
                'rate' => $row['base_rate'],
                'amount' => $row['base_amount'],
                'created_at' => $row['created_at']
            );
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

        //$company_logo = $this->model['image']->resize($setting['value'],200,50);
        $company_logo = $setting['value'];

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

            foreach($stocks as $warehouse => $products) {
                foreach($products as $product_name => $rows) {
                    $rowcount++;
                    $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':C'.$rowcount);
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,'Warehouse: '.$warehouse)->getStyle('A'.$rowcount)->getFont()->setBold(true)->setSize(13);
                    $rowcount++;

                    $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':C'.$rowcount);
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,'Product:    '.$product_name)->getStyle('A'.$rowcount)->getFont()->setBold(true)->setSize(13);
                    $rowcount++;

                    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(35);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
                    // $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':'.'G'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);



                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,'Sr. No.')->getStyle('A'.$rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount,'Doc. Date')->getStyle('B'.$rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,'Doc. No.')->getStyle('C'.$rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,'Qty')->getStyle('D'.$rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,'Rate')->getStyle('E'.$rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,'Amount')->getStyle('F'.$rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount,'Balance Qty')->getStyle('G'.$rowcount)->getFont()->setBold(true);
                    // $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount,'Created At')->getStyle('H'.$rowcount)->getFont()->setBold(true);
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
                    $balance_qty = 0;
                    $balance_amount = 0;

                    foreach($rows as $detail) {
                        $rowcount++;
                        $balance_qty += $detail['qty'];
                        $balance_amount += $detail['amount'];
                        $avg_rate = ($balance_amount / $balance_qty);
                        $sr++;

                        $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,$sr);
                        $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount,stdDate($detail['document_date']));
                        $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,$detail['document_identity']);
                        $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,$detail['qty']);
                        $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,number_format($detail['rate'],2));
                        $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount,number_format($detail['amount'],2));
                        $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowcount,number_format($balance_qty,2));
                        // $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowcount,$detail['created_at']);
                        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':G'.$rowcount)->applyFromArray(
                            array(
                                'borders' => array(
                                    'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                                ),
                                'alignment' => array(
                                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                                )
                            )
                        );

                    }
                    $rowcount++;
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount, number_format($balance_qty),2)->getStyle('D'.$rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowcount, number_format($balance_amount),2)->getStyle('F'.$rowcount)->getFont()->setBold(true);
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
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                            )
                        )
                    );
                    $rowcount +=3;
                }
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="Stock|Report.xlsx"');
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
        $pdf->SetTitle('Stock Ledger');
        $pdf->SetSubject('Stock Ledger');

        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->data['warehouses'] = $this->model['warehouse']->getRow(array('warehouse_id' => $post['warehouse_id']));
        $this->model['product_category'] = $this->load->model('inventory/product_category');
        $this->data['product_category'] = $this->model['product_category']->getRow(array('product_category_id' => $post['product_category_id']));
        $this->model['brand'] = $this->load->model('inventory/brand');
        $this->data['brand'] = $this->model['brand']->getRow(array('brand_id' => $post['brand_id']));

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $company_logo,
            'date_from' => $post['date_from'],
            'date_to' => $post['date_to'],
            'warehouse_id' => $this->data['warehouses']['name'],
            'product_category_id' => $this->data['product_category']['name'],
            'brand_id' => $this->data['brand']['name'],
            'print' => 'warehouseDetail'
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10, 58,5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->AddPage();
        //d($stocks, true);
        foreach($stocks as $warehouse => $products) {
            foreach($products as $product_name => $rows) {
                // set font
                $pdf->SetFont('helvetica', 'B', 9);
                // add a page
                $pdf->ln(5);
                $pdf->SetFillColor(255,255,255);

                $arrProduct = splitString('Product: '.$product_name, 120);
                foreach($arrProduct as $product){
                    $pdf->Cell(190, 5,$product, 0, false, 'L', false, '', 0, false, 'M', 'M');
                    $pdf->ln(5);
                }

                $pdf->Cell(77, 7,'Warehouse: ' . $warehouse, 0, false, 'L', false, '', 0, false, 'M', 'M');
                // $pdf->Ln(7);
                $sr =0;
                $balance_qty = 0;
                $balance_amount = 0;
                foreach($rows as $detail) {
                    $balance_qty += $detail['qty'];
                    $balance_amount += $detail['amount'];
                    $avg_rate = ($balance_amount / $balance_qty);
                    $pdf->SetFont('helvetica', '', 8);
                    $sr++;
                    $pdf->Ln(7);
                    $pdf->Cell(7, 7, $sr, 1, false, 'C', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(25, 7, $detail['document_date'], 1, false, 'C', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(35, 7, $detail['document_identity'], 1, false, 'C', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(20, 7, $detail['unit'], 1, false, 'C', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(20, 7, number_format($detail['qty'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(25, 7, number_format($detail['rate'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(30, 7, number_format($detail['amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(30, 7, number_format($balance_qty,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                    // $pdf->Cell(25, 7, stdDateTime($detail['created_at']), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                }
                // set font
                $pdf->SetFont('helvetica', 'B', 8);
                $pdf->Ln(7);
                $pdf->Cell(87, 7, '', 0, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 7, number_format($balance_qty,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 7, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(30, 7, number_format($balance_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->ln(3);
            }
        }

        //Close and output PDF document
        $pdf->Output('Stock Ledger:'.date('YmdHis').'.pdf', 'I');
    }
}


    public function printWarehouseSummary() {
        ini_set('max_execution_time',400);
        $lang = $this->load->language($this->getAlias());

        $post = $this->request->post;
        $session = $this->session->data;

        $this->model['company'] = $this->load->model('setup/company');
        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->model['product_category'] = $this->load->model('inventory/product_category');
        $this->model['product'] = $this->load->model('inventory/product');
        $this->model['product'] = $this->load->model('inventory/product');
        $this->model['stock'] = $this->load->model('common/stock_ledger');

        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        $arrWarehouses = $this->model['warehouse']->getArrays('warehouse_id','name',array('company_id' => $this->session->data['company_id'], 'company_branch_id' => $this->session->data['company_branch_id']));
        $arrProductCategories = $this->model['product_category']->getArrays('product_category_id','name',array('company_id' => $this->session->data['company_id']));
        $arrProducts = $this->model['product']->getArrays('product_id','name',array('company_id' => $this->session->data['company_id']));

        $arrWhere = array();
        $arrWhere[] = "`company_id` = '".$session['company_id']."'";
        $arrWhere[] = "`company_branch_id` = '".$session['company_branch_id']."'";
        $arrWhere[] = "`fiscal_year_id` = '".$session['fiscal_year_id']."'";
        if($post['date_from'] != '') {
            $arrWhere[] = "`document_date` >= '".MySqlDate($post['date_from'])."'";
            $filter['date_from'] = $post['date_from'];
        }
        if($post['date_to'] != '') {
            $arrWhere[] = "`document_date` <= '".MySqlDate($post['date_to'])."'";
            $filter['date_to'] = $post['date_to'];
        }
        if($post['warehouse_id']) {
            $arrWhere[] = "`warehouse_id` = '" . $post['warehouse_id'] . "'";
            $filter['warehouse'] = $arrWarehouses[$post['warehouse_id']];
        }
        if($post['product_category_id']) {
            $arrWhere[] = "`product_category_id` = '" . $post['product_category_id'] . "'";
            $filter['product_category'] = $arrProductCategories[$post['product_category_id']];
        }
        if($post['product_id']) {
            $arrWhere[] = "`product_id` = '" . $post['product_id'] . "'";
            $filter['product'] = $arrProducts[$post['product_id']];
        }
        if($post['brand_id']) {
            $arrWhere[] = "`brand_id` = '" . $post['brand_id'] . "'";
            $filter['brand_id'] = $arrProducts[$post['brand_id']];
        }
        $where = implode(' AND ', $arrWhere);

        $rows = $this->model['stock']->getRows($where, array('`document_date`', 'sfd_sort_order','created_at','sort_order'));
        $stocks = array();
        foreach($rows as $row) {
            if(!isset($stocks[$row['warehouse']][$row['product_id']])) {
                $stocks[$row['warehouse']][$row['product_id']] = array(
                    'warehouse' => $row['warehouse'],
                    'product_code' => $row['product_code'],
                    'product_name' => $row['product_name'],
                    'total_qty' => 0,
                    'total_amount' => 0,
                    'unit' => $row['document_unit'],
                );
            }
            
            $stocks[$row['warehouse']][$row['product_id']]['total_qty'] += $row['base_qty'];
            $stocks[$row['warehouse']][$row['product_id']]['total_amount'] += $row['base_amount'];
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

        $company_logo = $setting['value'];

        if($post['output'] == 'Excel'){
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->setActiveSheetIndex(0);

            $objPHPExcel->getProperties()
                ->setCreator('Farrukh Afaq')
                ->setLastModifiedBy('Farrukh Afaq')
                ->setTitle('Stock Ledger');

            $objPHPExcel->data = array(
                'company_name' => $session['company_name'],
                'report_name' => $lang['heading_title'],
                'company_logo' =>$company_logo
            );


            $rowcount = 1;

            $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':E'.$rowcount);
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


            $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':E'.$rowcount);
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

            foreach($stocks as $warehouse => $products) {
                $balance_qty = 0;
                $balance_amount = 0;
                $sr=0;
                $rowcount++;

                $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowcount.':C'.$rowcount);
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount, $warehouse)->getStyle('A'.$rowcount)->getFont()->setBold(true)->setSize(13);
                $rowcount++;

                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(60);
                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':'.'E'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount, 'Sr.')->getStyle('A'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount, 'Product')->getStyle('B'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount, 'Quantity')->getStyle('C'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount, 'Amount')->getStyle('D'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount, 'Avg Cost')->getStyle('E'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':E'.$rowcount)->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array( 'rgb' => 'ebebeb' )
                        ),
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        )
                    )
                );
                // set font
                foreach($products as $detail) {
                    if($detail['total_qty'] != 0) {
                        $balance_qty += $detail['total_qty'];
                        $balance_amount += $detail['total_amount'];
                        $sr++;
                        $rowcount++;
                        $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowcount,$sr)->getStyle('A'.$rowcount)->getAlignment(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowcount,$detail['product_name'])->getStyle('B'.$rowcount)->getAlignment(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,$detail['total_qty'])->getStyle('C'.$rowcount)->getAlignment(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,$detail['total_amount'])->getStyle('D'.$rowcount)->getAlignment(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowcount,$detail['total_amount']/$detail['total_qty'])->getStyle('E'.$rowcount)->getAlignment(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':E'.$rowcount)->applyFromArray(
                            array(
                                'borders' => array(
                                    'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                                )
                            )
                        );
                    }
                }
                $rowcount++;
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowcount,$balance_qty)->getStyle('C'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowcount,$balance_amount)->getStyle('D'.$rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowcount.':E'.$rowcount)->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array( 'rgb' => 'ebebeb')
                        ),
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                        ),
                        'borders' => array(
                            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        )
                    )
                );
                $rowcount += 3;

            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="Product Summary|Report.xlsx"');
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
        $pdf->SetTitle('Stock Ledger');
        $pdf->SetSubject('Stock Ledger');

        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->data['warehouses'] = $this->model['warehouse']->getRow(array('warehouse_id' => $post['warehouse_id']));
        $this->model['product_category'] = $this->load->model('inventory/product_category');
        $this->data['product_category'] = $this->model['product_category']->getRow(array('product_category_id' => $post['product_category_id']));
        $this->model['brand'] = $this->load->model('inventory/brand');
        $this->data['brand'] = $this->model['brand']->getRow(array('brand_id' => $post['brand_id']));

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $company_logo,
            'date_from' => $post['date_from'],
            'date_to' => $post['date_to'],
            'warehouse_id' => $this->data['warehouses']['name'],
            'product_category_id' => $this->data['product_category']['name'],
            'brand_id' => $this->data['brand']['name'],
            'print' => 'warehouseSummary'
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(5, 58, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // add a page
        $pdf->AddPage();

        // d($stocks,true);

        foreach($stocks as $warehouse => $products) {
            // set font
            $pdf->SetFont('helvetica', 'B', 9);
            $sr =0;
            $balance_qty = 0;
            $balance_amount = 0;
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(192, 7,'Warehouse: ' . $warehouse, 0, false, 'L', 1, '', 0, false, 'M', 'M');
            // set font
            $pdf->SetFont('helvetica', '', 8);
            foreach($products as $detail) {
                if($detail['total_qty'] >= 0.1) {
                    $balance_qty = round($balance_qty,2) + round($detail['total_qty'],2);
                    $balance_amount += $detail['total_amount'];
                    $sr++;
                    $pdf->Ln(7);
                    $pdf->Cell(7, 7, $sr, 1, false, 'R', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(115, 7, $detail['product_code'] . ' - ' . $detail['product_name'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(10, 7, $detail['unit'], 1, false, 'R', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(15, 7, number_format($detail['total_qty'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                    $pdf->Cell(25, 7, number_format($detail['total_amount'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                    if(round($detail['total_qty'],2)=='0.00') {
                    $pdf->Cell(25, 7, 0, 1, false, 'R', 0, '', 1, false, 'M', 'M');
                    } else {
                    $pdf->Cell(25, 7, number_format(round($detail['total_amount'],2)/round($detail['total_qty'],2),2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                    }
                }
            }
            // set font
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->Ln(7);
            $pdf->Cell(132, 7, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(15, 7, number_format($balance_qty,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 7, number_format($balance_amount,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 7, '', 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Ln(10);
        }

        //Close and output PDF document
        $pdf->Output('Stock Summary:'.date('YmdHis').'.pdf', 'I');
    }
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
        //     // $this->Image($image_file, 10, 10, 23, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // }
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
        $this->Cell(0, 10, 'Warehouse: '.$this->data['warehouse_id'].' | Product Category: '.$this->data['product_category_id'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(6);
        $this->Cell(0, 10, 'Brand: '.$this->data['brand_id'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);
        if($this->data['print'] == 'warehouseDetail')
        {
            $this->SetFont('helvetica', 'B', 8);
            $this->Cell(7, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, 'Doc. Date', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(35, 7, 'Doc. No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(20, 7, 'Unit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(20, 7, 'Qty', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, 'Rate', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(30, 7, 'Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(30, 7, 'Balance Qty', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            // $this->Cell(25, 7, 'Created at', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        }
        if($this->data['print'] == 'warehouseSummary')
        {
            $this->SetFont('helvetica', 'B', 8);
            $this->Cell(7, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(115, 7, 'Product', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(10, 7, 'Unit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(15, 7, 'Qty', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, 'Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(25, 7, 'Avg Cost', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        }
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
