<?php

class ControllerInventoryProduct extends HController {

    protected function validateDocument() {
        return false;
    }

    protected function getAlias() {
        return 'inventory/product';
    }

    protected function getPrimaryKey() {
        return 'product_id';
    }

    protected function getList() {
        parent::getList();

        $this->model['product_category'] = $this->load->model('inventory/product_category');
        $this->data['product_categorys'] = $this->model['product_category']->getRows(array('company_id' => $this->session->data['company_id']),array('name'));

        $this->model['product_sub_category'] = $this->load->model('inventory/product_sub_category');
        $this->data['product_sub_categorys'] = $this->model['product_sub_category']->getRows(array('company_id' => $this->session->data['company_id']),array('name'));

        $this->model['brand'] = $this->load->model('inventory/brand');
        $this->data['brands'] = $this->model['brand']->getRows(array('company_id' => $this->session->data['company_id']));

//        d($this->data['product_categorys'],true);
        $url='';
        if(isset($this->request->get['product_category_id'])) {
            $url .= "&product_category_id=".$this->request->get['product_category_id'];
            $this->data['product_category_id'] = $this->request->get['product_category_id'];
        }

        if(isset($this->request->get['product_sub_category_id'])) {
            $url .= "&product_sub_category_id=".$this->request->get['product_sub_category_id'];
            $this->data['product_sub_category_id'] = $this->request->get['product_sub_category_id'];
        }

        if(isset($this->request->get['brand_id'])) {
            $url .= "&brand_id=".$this->request->get['brand_id'];
            $this->data['brand_id'] = $this->request->get['brand_id'];
        }
//d($this->request->get['product_category_id']);
        $this->data['action_list'] = $this->url->link($this->getAlias(), 'token=' . $this->session->data['token'], 'SSL');
        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'].$url, 'SSL');


//        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_export_excel'] = $this->url->link($this->getAlias() . '/exportDataExcel', 'token=' . $this->session->data['token'].$url, 'SSL');
        $this->response->setOutput($this->render());
    }

    public function exportDataExcel()
    {
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        $results = $this->model[$this->getAlias()]->getRows();
        // d($results,true);

        // d('i am here');
        // exit;

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);

        $rowCount = 1;


        $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowCount.':G'.$rowCount);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->getAlignment()->applyFromArray(
            array('font' => array(
                'bold' => true,
                'size' => 14,
            ),'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
        );
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount,'Product List')->getStyle('A'.$rowCount)->getFont()->setBold( true )->setSize(14);
        $rowCount++;
        
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Product Category')->getStyle('A'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'Brand')->getStyle('B'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Code')->getStyle('C'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'Name')->getStyle('D'.$rowCount)->getFont()->setBold( true );
        // $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'Unit')->getStyle('E'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'Cost Price')->getStyle('F'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'Sale Price')->getStyle('G'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, 'Created At')->getStyle('H'.$rowCount)->getFont()->setBold( true );

        $rowCount++;

        // d($results,true);
        foreach($results as $key => $detail) {
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $detail['product_category']);
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $detail['brand']);
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $detail['product_code']);
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $detail['name']);
            // $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $detail['unit']);
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $detail['cost_price']);
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $detail['sale_price']);
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $detail['created_at']);
            $rowCount++;

            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="Product List.xlsx"');
            header('Cache-Control: max-age=0');
            //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
            //$objWriter->save('some_excel_file.xlsx');
            $objWriter->save('php://output');
            exit;
    }

    public function getAjaxLists() {
        $lang = $this->load->language($this->getAlias());
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());

        $data = array();
        $aColumns = array('action','product_category', 'product_code', 'name','cost_price','sale_price', 'created_at','check_box');

        /*
         * Paging
         */
        $sLimit = "";
        if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
            $data['criteria']['start'] = $_GET['iDisplayStart'];
            $data['criteria']['limit'] = $_GET['iDisplayLength'];
        }

        /*
         * Ordering
         */
        $sOrder = "";
        if (isset($_GET['iSortCol_0'])) {
            $sOrder = " ORDER BY  ";
            for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
                if ($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true") {
                    $sOrder .= "`" . $aColumns[intval($_GET['iSortCol_' . $i])] . "` " .
                        ($_GET['sSortDir_' . $i] === 'asc' ? 'asc' : 'desc') . ", ";
                }
            }

            $sOrder = substr_replace($sOrder, "", -2);
            if ($sOrder == " ORDER BY") {
                $sOrder = "";
            }
            $data['criteria']['orderby'] = $sOrder;
        }


        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        $arrWhere = array();
        if(isset($this->request->get['product_category_id'])) {
            $arrWhere[] = "`product_category_id` ='".$this->request->get['product_category_id']."'";
        }

        if(isset($this->request->get['product_sub_category_id'])) {
            $arrWhere[] = "`product_sub_category_id` ='".$this->request->get['product_sub_category_id']."'";
        }

        if(isset($this->request->get['brand_id'])) {
            $arrWhere[] = "`brand_id` ='".$this->request->get['brand_id']."'";
        }
        $arrWhere[] = "`company_id` = '".$this->session->data['company_id']."'";
        if (isset($_GET['sSearch']) && $_GET['sSearch'] != "") {
            $arrSSearch = array();
            for ($i = 0; $i < count($aColumns); $i++) {
                if (isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" && $_GET['sSearch'] != '') {
                    $arrSSearch[] = "LOWER(`" . $aColumns[$i] . "`) LIKE '%" . $this->db->escape(strtolower($_GET['sSearch'])) . "%'";
                }
            }
            if(!empty($arrSSearch)) {
                $arrWhere[] = '(' . implode(' OR ', $arrSSearch) . ')';
            }
        }

        /* Individual column filtering */
        for ($i = 0; $i < count($aColumns); $i++) {
            if (isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
                $arrWhere[] = "LOWER(`" . $aColumns[$i] . "`) LIKE '%" . $this->db->escape(strtolower($_GET['sSearch_' . $i])) . "%' ";
            }
        }

        if (!empty($arrWhere)) {
            //$data['filter']['RAW'] = substr($sWhere, 5, strlen($sWhere) - 5);
            $data['filter']['RAW'] = implode(' AND ', $arrWhere);
        }

        //d($data, true);
        $results = $this->model[$this->getAlias()]->getLists($data);
        $iFilteredTotal = $results['total'];
        $iTotal = $results['table_total'];


        /*
         * Output
         */
        $output = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );

        foreach ($results['lists'] as $aRow) {
            $row = array();
            $actions = array();

            $actions[] = array(
                'text' => $lang['edit'],
                'href' => $this->url->link($this->getAlias() . '/update', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $aRow[$this->getPrimaryKey()], 'SSL'),
                'btn_class' => 'btn btn-primary btn-xs',
                'class' => 'fa fa-pencil'
            );

            $actions[] = array(
                'text' => $lang['duplicate'],
                'href' => $this->url->link($this->getAlias() . '/insert', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $aRow[$this->getPrimaryKey()].'&duplicate=1', 'SSL'),
                'btn_class' => 'btn btn-primary btn-xs',
                'class' => 'fa fa-clone'
            );

            $actions[] = array(
                'text' => $lang['delete'],
                'href' => 'javascript:void(0);',
                'click' => "ConfirmDelete('" . $this->url->link($this->getAlias() . '/delete', 'token=' . $this->session->data['token'] . '&id=' . $aRow[$this->getPrimaryKey()], 'SSL') . "')",
                'btn_class' => 'btn btn-danger btn-xs',
                'class' => 'fa fa-times'
            );

            $actions[] = array(
                'text' => $lang['print_barcode'],
                'target' => '_blank',
                'href' => $this->url->link($this->getAlias() . '/printLabel', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $aRow[$this->getPrimaryKey()], 'SSL'),
                'btn_class' => 'btn btn-primary btn-xs',
                'class' => 'fa fa-print'
            );

            $strAction = '';
            foreach ($actions as $action) {
                $strAction .= '<a '.(isset($action['btn_class'])?'class="'.$action['btn_class'].'"':'').' href="' . $action['href'] .'" '. (isset($action['target']) ? 'target="' . $action['target'] . '"' : '') . ' data-toggle="tooltip" title="' . $action['text'] . '" ' . (isset($action['click']) ? 'onClick="' . $action['click'] . '"' : '') . '>';
                if (isset($action['class'])) {
                    $strAction .= '<span class="' . $action['class'] . '"></span>';
                } else {
                    $strAction .= $action['text'];
                }
                $strAction .= '</a>&nbsp;';
            }


            for ($i = 0; $i < count($aColumns); $i++) {
                if ($aColumns[$i] == 'action') {
                    $row[] = $strAction;
                } elseif ($aColumns[$i] == 'created_at') {
                    $row[] = stdDateTime($aRow['created_at']);
                } elseif ($aColumns[$i] == 'cost_price') {
                    $row[] = (int)($aRow['cost_price']);
                } elseif ($aColumns[$i] == 'sale_price') {
                    $row[] = (int)($aRow['sale_price']);
                }
                 elseif ($aColumns[$i] == 'check_box') {
                    $row[] = '<input type="checkbox" name="selected[]" value="' . $aRow[$this->getPrimaryKey()] . '" />';
                } else {
                    $row[] = $aRow[$aColumns[$i]];
                }

            }
            $output['aaData'][] = $row;
        }
        echo json_encode($output);
    }

    protected function getForm() {
        parent::getForm();
        $this->data['sale_profit'] = 0;
        $this->data['sale_price'] = 0;
        $this->data['wholesale_price'] = 0;
        $this->data['minimum_price'] = 0;
        $this->data['cost_price'] = 0;
        $this->data['reorder_quantity'] = 0;

        $this->model['product_category'] = $this->load->model('inventory/product_category');
        $this->data['product_categories'] = $this->model['product_category']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['brand'] = $this->load->model('inventory/brand');
        $this->data['brands'] = $this->model['brand']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['make'] = $this->load->model('inventory/make');
        $this->data['makes'] = $this->model['make']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['model'] = $this->load->model('inventory/model');
        $this->data['models'] = $this->model['model']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['unit'] = $this->load->model('inventory/unit');
        $this->data['units'] = $this->model['unit']->getRows(array('company_id' => $this->session->data['company_id']));
        $this->data['href_product_category_form'] = $this->url->link('inventory/product_category/insert', 'token=' . $this->session->data['token'].'&popup=popup', 'SSL');
        $this->data['href_product_sub_category_form'] = $this->url->link('inventory/product_sub_category/insert', 'token=' . $this->session->data['token'].'&popup=popup', 'SSL');
        $this->data['href_brand_form'] = $this->url->link('inventory/brand/insert', 'token=' . $this->session->data['token'].'&popup=popup', 'SSL');
        $this->data['href_model_form'] = $this->url->link('inventory/model/insert', 'token=' . $this->session->data['token'].'&popup=popup', 'SSL');

        $this->data['url_add_brand'] = $this->url->link('inventory/brand/saveBrand', 'token=' . $this->session->data['token'] );
        $this->data['url_add_product_category'] = $this->url->link('inventory/product_category/saveProductCategory', 'token=' . $this->session->data['token'] );
        $this->data['url_add_product_sub_category'] = $this->url->link('inventory/product_sub_category/saveProductSubCategory', 'token=' . $this->session->data['token'] );
        $this->data['url_add_model'] = $this->url->link('inventory/model/saveModel', 'token=' . $this->session->data['token'] );

       $this->data['href_print_label'] = $this->url->link($this->getAlias() . '/printLabel', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');


        $this->model['module_setting'] = $this->load->model('common/setting');
        $this->model['coa'] = $this->load->model('gl/coa_level3');

        //d( $this->session->data['fiscal_year_id'],true);
        $filter = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'inventory',
        );
        $accounts = $this->model['module_setting']->getRows($filter, array('field'));
        foreach($accounts as $account) {
            if($account['field']=='inventory_account_id') {
                $this->data['inventory_accounts'][] = $this->model['coa']->getRow(array('coa_level3_id' => $account['value']));
            } elseif($account['field']=='revenue_account_id') {
                $this->data['revenue_accounts'][] = $this->model['coa']->getRow(array('coa_level3_id' => $account['value']));
            } elseif($account['field']=='cogs_account_id') {
                $this->data['cogs_accounts'][] = $this->model['coa']->getRow(array('coa_level3_id' => $account['value']));
            } elseif($account['field']=='adjustment_account_id') {
                $this->data['adjustment_accounts'][] = $this->model['coa']->getRow(array('coa_level3_id' => $account['value']));
            } elseif($account['field']=='auto_generate_product_code') {
                $this->data['auto_generate_product_code'] = $account['value'];
            }
        }

        if($this->data['auto_generate_product_code'] != 1) {
            $this->data['product_code'] = '';
        } else {
            $this->data['product_code'] = 'AUTO';
        }

        if (isset($this->request->get['product_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $result = $this->model[$this->getAlias()]->getRow(array('product_id' => $this->request->get['product_id']));
            foreach ($result as $field => $value) {
                $this->data[$field] = $value;
            }
            if(isset($this->request->get['duplicate'])) {
                if($this->data['auto_generate_product_code'] != 1) {
                    $this->data['product_code'] = '';
                } else {
                    $this->data['product_code'] = 'AUTO';
                }
            }
        }

        $this->data['name'] = htmlentities($result['name']);

        $this->model['image'] = $this->load->model('tool/image');
        $this->data['no_image'] = $this->model['image']->resize('no_image.jpg', 100, 100);

        if ($this->data['product_image'] && file_exists(DIR_IMAGE . $this->data['product_image']) && is_file(DIR_IMAGE . $this->data['product_image'])) {
            $this->data['src_product_image'] = $this->model['image']->resize($this->data['product_image'], 100, 100);
        } else {
            $this->data['src_product_image'] = $this->model['image']->resize('no_image.jpg', 100, 100);
        }


//            $this->model['product_sub_category'] = $this->load->model('inventory/product_sub_category');
//            $rows = $this->model['product_sub_category']->getRows(array('product_category_id' => $result['product_category_id']));
//            $html = '';
//            $html .= '<option value="">&nbsp;</option>';
//            foreach($rows as $row) {
//
//                if($row['product_sub_category_id']!= "") {
//                    $html .= '<option value="'.$row['product_sub_category_id'].'" selected="true">'.$row['name'].'</option>';
//                } else {
//                    $html .= '<option value="'.$row['product_sub_category_id'].'">'.$row['name'].'</option>';
//                }
//            }
//            $this->data['$product_sub_categories'] = $html;

//        d(array($result,$this->data['$product_sub_categories']),true);


        $this->data['href_product_sub_category'] = $this->url->link($this->getAlias() . '/getProductSubCategory', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        
        $dublicate_url = ( $this->request->get['duplicate'] == 1 ) ? '&duplicate=1' : '';
        $this->data['action_validate_product_code'] = $this->url->link($this->getAlias() . '/validateProductCode', 'token=' . $this->session->data['token'] . '&product_id=' . $this->request->get['product_id'] . $dublicate_url);
        $this->data['action_validate_product_name'] = $this->url->link($this->getAlias() . '/validateProductName', 'token=' . $this->session->data['token'] . '&product_id=' . $this->request->get['product_id'] . $dublicate_url);
        $this->data['strValidation']= "{
            'rules':{
                'product_category_id': {'required':true},
                'thickness_id': {'required':true},
                'width_id': {'required':true},
                'length_id': {'required':true},
                'grade_id': {'required':true},
                'sawmill_id': {'required':true},
                'name': {'required':true, 'minlength': 3, 'remote':  {url: '" . $this->data['action_validate_product_name'] . "', type: 'post'}},
                'product_code': {'required':true, 'minlength': 3, 'remote':  {url: '" . $this->data['action_validate_product_code'] . "', type: 'post'}},
                'unit_id': {'required':true},
                'inventory_account_id': {'required': true},
                'revenue_account_id': {'required': true},
                'cogs_account_id': {'required': true},
                'adjustment_account_id': {'required': true},
                'thickness_unit_id': {'required':true},
                'thickness_value': {'required':true},
                'length_unit_id': {'required':true},
                'length_value': {'required':true},
                'width_unit_id': {'required':true},
                'width_value': {'required':true},
             },
            'ignore':[]
          }";

        $this->response->setOutput($this->render());
    }


    public function printLabelOld() {
        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        // d(array('session' => $this->session->data, 'post' => $this->request->post, 'get' => $this->request->get), true);
        $lang = $this->load->language($this->getAlias());
        $post = $this->request->post;
        $session = $this->session->data;
        $print_type = $this->request->get['print_type'];
        $this->model['product'] = $this->load->model('inventory/product');
        $product = $this->model['product']->getRow(array('product_id' => $this->request->get['product_id']));


        //d(array('post'=>$post, 'session'=>$session, 'product'=>$product), true);
        $pdf = new PDF('P', 'mm', array(50.06,27.94), true, 'UTF-8', false);
        $pdf->setPageOrientation('L',false,'');
        $pdf->SetAutoPageBreak(TRUE, 0);

        

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Muhammad Salman');
        $pdf->SetTitle('Barcode Print');
        $pdf->SetSubject('Barcode Print');

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $session['company_image']
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(1, 1, 1);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 0);

        // add a page
        $pdf->AddPage();

        $column = 1;
        $this_code =  (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $product['product_code'])) ? 'C128' : 'C39';
        $pdf->SetFont('helvetica', '', 8);

        // define barcode style
        $style = array(
            'position' => 'S',
            'align' => 'C',
            'stretch' => true,
            'fitwidth' => false,
            'cellfitalign' => 'C',
            'border' => false,
            'hpadding' => 'auto',
            'vpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false, //array(255,255,255),
            'text' => false,
            'font' => 'helvetica',
            'fontsize' => 5,
            //'module_width' => 13,
        );

                $pdf->ln(3);
                $pdf->SetFont('helvetica', 'B', 12);
                if($print_type == 'with_company'){
                $pdf->Cell(45, 0, $session['company_name'], 0, 0,'C');
                $pdf->Cell(45, 0, '', 0, 1,'C');
                $pdf->SetFont('helvetica', '', 8);
                $pdf->ln(9);
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                $pdf->setXY($x,$y);
                $pdf->write1DBarcode($product['product_code'], $this_code,$x+2,$y-9,40,8,0.4,$style,'M');
                $pdf->ln(3);
                $pdf->SetFont('helvetica', 'B', 8);
                $pdf->ln(1.5);
                $pdf->setX(11);
                $pdf->Cell(22, 0, 'Price: '.number_format($product['sale_price'],2,".",""), 0, 0,'R');
                }

                else if($print_type == 'without_company'){
                $pdf->Cell(45, 0, '', 0, 1,'C');
                $pdf->SetFont('helvetica', '', 8);
                $pdf->ln(9);
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                $pdf->setXY($x,$y);
                $pdf->write1DBarcode($product['product_code'], $this_code,$x+2,$y-9,40,8,0.4,$style,'M');
                $pdf->ln(3);
                $pdf->SetFont('helvetica', 'B', 8);
                $pdf->Cell(45, 0, $product['name'], 0, 1,'C');
                $pdf->ln(1.5);
                }

                else if($print_type == 'company'){
                $pdf->Cell(45, 0, $session['company_name'], 0, 0,'C');
                $pdf->Cell(45, 0, '', 0, 1,'C');
                $pdf->SetFont('helvetica', '', 8);
                $pdf->ln(9);
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                $pdf->setXY($x,$y);
                $pdf->write1DBarcode($product['product_code'], $this_code,$x+2,$y-9,40,8,0.4,$style,'M');
                $pdf->ln(3);
                $pdf->SetFont('helvetica', 'B', 8);
                $pdf->ln(1.5);
                }

               else if($print_type == 'price'){
                $pdf->Cell(45, 0, '', 0, 1,'C');
                $pdf->SetFont('helvetica', '', 8);
                $pdf->ln(9);
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                $pdf->setXY($x,$y);
                $pdf->write1DBarcode($product['product_code'], $this_code,$x+2,$y-9,40,8,0.4,$style,'M');
                $pdf->ln(3);
                $pdf->SetFont('helvetica', 'B', 8);
                $pdf->ln(1.5);
                $pdf->setX(11);
                $pdf->Cell(22, 0, 'Price: '.number_format($product['sale_price'],2,".",""), 0, 0,'R');
                }
                else {
                $pdf->Cell(45, 0, $session['company_name'], 0, 0,'C');
                $pdf->Cell(45, 0, '', 0, 1,'C');
                $pdf->SetFont('helvetica', '', 8);
                $pdf->ln(9);
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                $pdf->setXY($x,$y);
                $pdf->write1DBarcode($product['product_code'], $this_code,$x+2,$y-9,40,8,0.4,$style,'M');
                $pdf->ln(3);
                $pdf->SetFont('helvetica', 'B', 8);
                $pdf->Cell(45, 0, $product['name'], 0, 1,'C');
                $pdf->ln(1.5);
                $pdf->Cell(22, 0, $product['product_code'], 0, 0,'L');
                $pdf->Cell(22, 0, 'Price: '.number_format($product['sale_price'],2,".",""), 0, 0,'R');
                }

               //Close and output PDF document
               $pdf->Output('Barcode:'.date('YmdHis').'.pdf', 'I');

    }


    public function printLabel() {
        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        //d(array('session' => $this->session->data, 'post' => $this->request->post, 'get' => $this->request->get), true);
        $lang = $this->load->language($this->getAlias());
        $post = $this->request->post;
        $session = $this->session->data;
        $print_type = $this->request->get['print_type'];
        $this->model['product'] = $this->load->model('inventory/product');
        $product = $this->model['product']->getRow(array('product_id' => $this->request->get['product_id']));

        //d(array('post'=>$post, 'session'=>$session, 'product'=>$product), true);
        $pdf = new PDF('P', 'mm', array(50.06,27.94), true, 'UTF-8', false);
        $pdf->setPageOrientation('L',false,'');
        $pdf->SetAutoPageBreak(TRUE, 0);

        

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Muhammad Salman');
        $pdf->SetTitle('Barcode Print');
        $pdf->SetSubject('Barcode Print');


        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $session['company_image']
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(1, 1, 1);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 0);

        // set font
        // $pdf->SetFont('times', '', 8);

        // add a page
        $pdf->AddPage();

        $column = 1;
        $this_code =  (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $product['product_code'])) ? 'C128' : 'C39';
        $pdf->SetFont('helvetica', '', 8);

        // define barcode style
        $style = array(
            'position' => 'S',
            'align' => 'C',
            'stretch' => true,
            'fitwidth' => false,
            'cellfitalign' => 'C',
            'border' => false,
            'hpadding' => 'auto',
            'vpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false, //array(255,255,255),
            'text' => false,
            'font' => 'helvetica',
            'fontsize' => 5,
            //'module_width' => 13,
        );
        for($i=1;$i<=$product['reorder_quantity'];$i+=$column) {
            if($column == 1) {
    
               if($print_type == 'with_company'){
                $pdf->SetFont('helvetica', 'B', 12);
                $pdf->Cell(45, 0, $session['company_name'], 0, 0,'C');
                $pdf->Cell(45, 0, '', 0, 1,'C');
                $pdf->SetFont('helvetica', '', 8);
                $pdf->ln(10);
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                $pdf->setXY($x,$y);
                $pdf->write1DBarcode($product['product_code'], $this_code,$x+2,$y-9,40,8,0.4,$style,'M');
                $pdf->ln(4);
                $pdf->SetFont('helvetica', 'B', 7);
                $pdf->Cell(45, 0, $product['name'], 0, 1,'C');
                $pdf->ln(1.5);
                $pdf->setX(5);
                $pdf->SetFont('helvetica', 'B', 6);
                $pdf->Cell(25, 0, 'Price: '.number_format($product['sale_price'],0,".",""), 0, 0,'R');
                $pdf->ln(5);
                }

                else if($print_type == 'without_company'){
                $pdf->Cell(45, 0, '', 0, 1,'C');
                $pdf->SetFont('helvetica', '', 8);
                $pdf->ln(10);
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                $pdf->setXY($x,$y);
                $pdf->write1DBarcode($product['product_code'], $this_code,$x+2,$y-9,40,8,0.4,$style,'M');
                $pdf->ln(3);
                $pdf->ln(1.5);
                $pdf->SetFont('helvetica', 'B', 7);
                $pdf->Cell(45, 0, $product['name'], 0, 1,'C');
                $pdf->ln(10);
                }

                else if($print_type == 'company'){
                $pdf->SetFont('helvetica', 'B', 12);     
                $pdf->Cell(45, 0, $session['company_name'], 0, 0,'C');
                $pdf->Cell(45, 0, '', 0, 1,'C');
                $pdf->SetFont('helvetica', '', 8);
                $pdf->ln(10);
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                $pdf->setXY($x,$y);
                $pdf->write1DBarcode($product['product_code'], $this_code,$x+2,$y-9,40,8,0.4,$style,'M');
                $pdf->ln(3);
                $pdf->ln(1.5);
                $pdf->SetFont('helvetica', 'B', 7);
                $pdf->Cell(45, 0, $product['name'], 0, 1,'C');
                $pdf->ln(10);
                }

               else if($print_type == 'price'){
                $pdf->Cell(45, 0, '', 0, 1,'C');
                $pdf->SetFont('helvetica', '', 8);
                $pdf->ln(10);
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                $pdf->setXY($x,$y);
                $pdf->write1DBarcode($product['product_code'], $this_code,$x+2,$y-9,40,8,0.4,$style,'M');
                $pdf->ln(4);
                $pdf->SetFont('helvetica', 'B', 7);
                $pdf->Cell(45, 0, $product['name'], 0, 1,'C');
                $pdf->ln(1.5);
                $pdf->setX(5);
                $pdf->SetFont('helvetica', 'B', 6);
                $pdf->Cell(25, 0, 'Price: '.number_format($product['sale_price'],0,".",""), 0, 0,'R');
                $pdf->ln(10);
                }
                else {  
                $pdf->SetFont('helvetica', 'B', 12);    
                $pdf->Cell(45, 0, $session['company_name'], 0, 0,'C');
                $pdf->Cell(45, 0, '', 0, 1,'C');
                $pdf->SetFont('helvetica', '', 8);
                $pdf->ln(9);
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                $pdf->setXY($x,$y);
                $pdf->write1DBarcode($product['product_code'], $this_code,$x+2,$y-9,40,8,0.4,$style,'M');
                $pdf->ln(4);
                $pdf->SetFont('helvetica', 'B', 7);
                $pdf->Cell(45, 0, $product['name'], 0, 1,'C');
                $pdf->ln(1.5);
                $pdf->SetFont('helvetica', 'B', 6);
                $pdf->Cell(22, 0, $product['product_code'], 0, 0,'L');
                $pdf->SetFont('helvetica', 'B', 6);
                $pdf->Cell(25, 0, 'Price: '.number_format($product['sale_price'],0,".",""), 0, 0,'R');
                $pdf->ln(10);
                }

            } 

            if(($i<$product['reorder_quantity'] - 1)) {
                $pdf->AddPage();
            }
        }

        //Close and output PDF document
        $pdf->Output('Barcode:'.date('YmdHis').'.pdf', 'I');
    }






    public function getProductSubCategory() {
        $post = $this->request->post;
        $product_category_id = $post['product_category_id'];
        $product_sub_category_id = $post['product_sub_category_id'];

        $this->model['product_sub_category'] = $this->load->model('inventory/product_sub_category');
        $rows = $this->model['product_sub_category']->getRows(array('product_category_id' => $product_category_id));
        $html = '';
        $html .= '<option value="">&nbsp;</option>';
        foreach($rows as $row) {
            if($row['product_sub_category_id'] == $product_sub_category_id) {
                $html .= '<option value="'.$row['product_sub_category_id'].'" selected="true">'.$row['name'].'</option>';
            } else {
                $html .= '<option value="'.$row['product_sub_category_id'].'">'.$row['name'].'</option>';
            }
        }
        $json = array(
            'success' => true,
            'html' => $html
        );
//d($json,true);
        echo json_encode($json);
    }

    public function validateProductCode()
    {
        $code = $this->request->post['product_code'];
//        d($code,true);
        $company_id = $this->session->data['company_id'];
        $product_id = $this->request->get['product_id'];
        $duplicate = $this->request->get['duplicate'];

        if( $duplicate == 1 ){
            $product_id = '';
        }


        $this->load->language('inventory/product');
        if ($code) {
            $this->model['product'] = $this->load->model('inventory/product');
            $where = "company_id='" . $company_id . "' AND LOWER(product_code) = '".strtolower($code)."' AND product_id != '".$product_id."'";
            $row = $this->model['product']->getRow($where);
//            d($coa,true);
            if ($row) {
                echo json_encode($this->language->get('error_duplicate_product_code'));
            } else {
                echo json_encode("true");
            }
        } else {
            echo json_encode($this->language->get('error_product_code'));
        }
        exit;
    }

    public function validateProductName()
    {
        $name = $this->request->post['name'];
        $company_id = $this->session->data['company_id'];
        $product_id = $this->request->get['product_id'];
        $duplicate = $this->request->get['duplicate'];

        if( $duplicate == 1 ){
            $product_id = '';
        }

        $this->load->language('inventory/product');
        if ($name) {
            $this->model['product'] = $this->load->model('inventory/product');
            $where = "company_id='" . $company_id . "' AND LOWER(name) = '".strtolower($name)."' AND product_id != '".$product_id."'";
            $coa = $this->model['product']->getRow($where);            
            if ($coa) {
                echo json_encode($this->language->get('error_duplicate_product_name'));
            } else {
                echo json_encode("true");
            }
        } else {
            echo json_encode($this->language->get('error_name'));
        }
        exit;
    }

    protected function insertData($data) {
        if($data['product_code']=='AUTO') {
            $product_code = $this->model[$this->getAlias()]->getMaxProductCode();
            $data['product_code'] = str_pad($product_code,4,'0',STR_PAD_LEFT);
        }

//        $data['name'] = html_entity_decode($data['name']);
        $data['company_id'] = $this->session->data['company_id'];

        $this->model[$this->getAlias()]->add($this->getAlias(), $data);

    }

    protected function updateData($primary_key, $data) {
        $data['company_id'] = $this->session->data['company_id'];
//        $data['name'] = html_entity_decode($data['name']);
     //   d($data,true);
        $this->model[$this->getAlias()]->edit($this->getAlias(), $primary_key, $data);
        //d($data,true);
    }

    public function getProducts() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->request->post['product_category_id']) {
            $this->model['product'] = $this->load->model('inventory/product');
            $products = $this->model['product']->getArrays('product_id','name',array('company_id' => $this->session->data['company_id'], 'product_category_id' => $this->request->post['product_category_id']));
            $html = '<option value="">&nbsp;</option>';
            foreach($products as $id => $value) {
                $html .= '<option value="'.$id.'">'.$value.'</option>';
            }
            $json = array(
                'success' => true,
                'html' => $html
            );
        } else {
            $this->load->language('inventory/product');
            $json = array(
                'success' => false,
                'error' => $this->language->get('error_select_product_category')
            );
        }

        $this->response->setOutput(json_encode($json));
    }

    public function getProductCharges() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->request->post['product_id']) {
            $this->model['product'] = $this->load->model('inventory/product');
            $charges = $this->model['product']->getProductCharges($this->request->post['product_id']);
            $json = array(
                'success' => true,
                'charges' => $charges
            );
        } else {
            $this->load->language('inventory/product');
            $json = array(
                'success' => false,
                'error' => $this->language->get('error_select_product')
            );
        }

        $this->response->setOutput(json_encode($json));
    }

    public function getProductUnits() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->request->post['product_id']) {
            $this->model['product'] = $this->load->model('inventory/product');
            $product = $this->model['product']->getRow(array('product_id' => $this->request->post['product_id']));
            $this->model['product'] = $this->load->model('inventory/product');
            //$product = $this->model['product']->getArrays('product_code','cost_price',array('product_id' => $this->request->post['product_id']));
//            d($pdcode,true);
            $this->model['unit'] = $this->load->model('inventory/unit');
            $unit = $this->model['unit']->getRow(array('unit_id' => $product['unit_id']));
            $html='';
            if($unit) {
                $html .= '<option value="' . $unit['unit_id'] . '">' . $unit['name'] . '</option>';
            }
            $json = array(
                'success' => true,
                'html' => $html,
                'cost_price' => $product['cost_price'],
                'product_code' => $product['product_code']
            );
        } else {
            $this->load->language('inventory/product');
            $json = array(
                'success' => false,
                'error' => $this->language->get('error_select_product')
            );
        }

        $this->response->setOutput(json_encode($json));
    }

    public function getCostPrice(){

        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->request->post['product_id'] ) {
            $this->model['product'] = $this->load->model('inventory/product');
            $costprice = $this->model['product']->getRow('cost_price');
            $json = array(
                'success' => true,
                'charges' => $costprice
            );
        } else {
            $this->load->language('inventory/product');
            $json = array(
                'success' => false,
                'error' => $this->language->get('error_select_product')
            );
        }

    }

    public function getProductInformation() {
        //  d($this->request->post['product_id']);
        if($this->request->server['REQUEST_METHOD'] == 'POST' && $this->request->post['product_id']) {
            $this->model['product'] = $this->load->model('inventory/product');
            $filter = array(
                'company_id' => $this->session->data['company_id'],
                'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                'product_id' => $this->request->post['product_id'],
            );
            $filter1 = 'product_id';
            $product = $this->model['product']->getRow($filter);
            $filter['company_branch_id'] = $this->session->data['company_branch_id'];
            $product_stock = $this->model['product']->getProductStock($filter);
            $this->model['unit'] = $this->load->model('inventory/unit');
            $unit = $this->model['unit']->getRow(array('unit_id' => $product['unit_id']));
            $html='';
            if($unit) {
                $html .= '<option value="' . $unit['unit_id'] . '">' . $unit['name'] . '</option>';
            }
            $json = array(
                'success' => true,
                'product_id' => $product['product_id'],
                'unit' => $html,
                'rate' => $product['cost_price'],
                'sale_price' => $product['sale_price'],
                'code' => $product['product_code'],
                'name' => $product['name'],
                'avg_rate' => $product_stock['rate'],
                'quantity' => $product_stock['qty'],
                'product_service' => $product['product_service'],
            );
        }
        else {
            $this->load->language('inventory/product');
            $json = array(
                'success' => false,
                'error' => $this->language->get('error_select_product')
            );
        }

        $this->response->setOutput(json_encode($json));
    }

    public function getProductStock() {
        //d($this->request->get['product_id']);
        if($this->request->server['REQUEST_METHOD'] == 'POST' && $this->request->post['warehouse_id'] ) {
            $post = $this->request->post;
            //d($post);
            $this->model['stock'] = $this->load->model('common/stock');
            $filter = array(
                'company_id' => $this->session->data['company_id'],
                'company_branch_id' => $this->session->data['company_branch_id'],
                'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                'warehouse_id' => $post['warehouse_id'],
                'product_id' => $post['product_id'],

            );

            $product_stock = $this->model['stock']->getStock($filter);
//            d(array($product_stock,$filter),true);
            $json = array(
                'success' => true,
                'avg_rate' => $product_stock['rate'],
                'quantity' => empty($product_stock['qty'])?0:$product_stock['qty'],
                'product_stock' => $product_stock,
            );
        } else {
            $this->load->language('inventory/product');
            $json = array(
                'success' => false,
                'error' => $this->language->get('error_select_product')
            );
        }

        //d($json,true);
        $this->response->setOutput(json_encode($json));
    }

    public function getProductStockWithCode() {
        //d($this->request->get['product_id']);
        if($this->request->server['REQUEST_METHOD'] == 'POST' && $this->request->post['warehouse_id'] ) {
            $post = $this->request->post;
            //d($post);
            $this->model['stock'] = $this->load->model('common/stock');
            $filter = array(
                'company_id' => $this->session->data['company_id'],
                'company_branch_id' => $this->session->data['company_branch_id'],
                'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                'warehouse_id' => $post['warehouse_id'],
                'product_id' => $post['product_id'],

            );
            $this->model['product'] = $this->load->model('inventory/product');
            $product = $this->model['product']->getRow(array('product_id' => $this->request->post['product_id']));
            $this->model['unit'] = $this->load->model('inventory/unit');
            $unit = $this->model['unit']->getRow(array('unit_id' => $product['unit_id']));
            $html='';
            if($unit) {
                $html .= '<option value="' . $unit['unit_id'] . '">' . $unit['name'] . '</option>';
            }
            $product_stock = $this->model['stock']->getStock($filter);
            $json = array(
                'success' => true,
                'unit' => $html,
                'avg_rate' => round($product_stock['rate'],3),
                'code' => $product['product_code'],
                'quantity' => empty($product_stock['qty'])?0:$product_stock['qty'],
                'product_stock' => $product_stock,
            );
        } else {
            $this->load->language('inventory/product');
            $json = array(
                'success' => false,
                'error' => $this->language->get('error_select_product')
            );
        }

//        d($json,true);
        $this->response->setOutput(json_encode($json));
    }

    public function getProductInformationCode() {
        $this->load->language('inventory/product');
        if($this->request->server['REQUEST_METHOD'] == 'POST' && $this->request->post['product_code']) {
            $this->model['product'] = $this->load->model('inventory/product');
            $product = $this->model['product']->getRow(array('company_id' => $this->session->data['company_id'],'product_code' => $this->request->post['product_code']));
            //d($product,true);
            if($product) {
                $json = array(
                    'success' => true,
                    'product_code' => $product['product_code'],
                    'product_id' => $product['product_id']
                );
            } else {
                $json = array(
                    'success' => false,
                    'error' => $this->language->get('error_product_code'),
                );

            }
//            d($json,true);
        }

        $this->response->setOutput(json_encode($json));
    }

    public function quickSearch() {
        $this->load->language('inventory/product');
        $this->model['product'] = $this->load->model('inventory/product');
        $products = $this->model['product']->getQuickSearch(array('criteria' =>array('orderby'=>' order by product_code ASC')));
//        d($products,true);
        $html = "";
        if ($products) {
            $ref_id = $this->request->get['ref_id'];
            $callback = $this->request->get['callback'];

//            $html .= "<div class='row'>";
            $html .= "<div class='col-md-12'>";
            $html .= "<div class='table-responsive'>";
            $html .= "<table id='tblQuickSearch' class='table table-striped' style='width: 100%'>";
            $html .= "<thead>";
            $html .= "<tr>";
            $html .= "<th>&nbsp;</th>";
            $html .= "<th>" . $this->language->get('column_code') . "</th>";
            $html .= "<th>" . $this->language->get('column_barcode') . "</th>";
            $html .= "<th>" . $this->language->get('column_name') . "</th>";
            $html .= "<th>" . $this->language->get('column_thickness') . "</th>";
            $html .= "<th>" . $this->language->get('column_width') . "</th>";
            $html .= "<th>" . $this->language->get('column_model') . "</th>";
            $html .= "</tr>";
            $html .= "</thead>";
            $html .= "<tbody>";
            foreach ($products as $product) {
                $html .= "<tr>";
                $html .= "<td><a href='javascript:void(0);' onClick=\"_quickSearch('" . $ref_id . "','" . $product['product_id'] . "','" . $callback . "');\">Select</a></td>";

                $html .= "<td>" . $product['product_code'] . "</td>";
                $html .= "<td>" . $product['barcode'] . "</td>";
                $html .= "<td>" . $product['name'] . "</td>";
                $html .= "<td>" . $product['thickness'] . "</td>";
                $html .= "<td>" . $product['width'] . "</td>";
                $html .= "<td>" . $product['model'] . "</td>";
                $html .= "</tr>";
            }
            $html .= "</tbody>";
            $html .= "<tfoot>";
            $html .= "<tr>";
            $html .= "<th>&nbsp;</th>";
            $html .= "<th>" . $this->language->get('column_code') . "</th>";
            $html .= "<th>" . $this->language->get('column_barcode') . "</th>";
            $html .= "<th>" . $this->language->get('column_name') . "</th>";
            $html .= "<th>" . $this->language->get('column_thickness') . "</th>";
            $html .= "<th>" . $this->language->get('column_width') . "</th>";
            $html .= "<th>" . $this->language->get('column_model') . "</th>";
            $html .= "</tr>";
            $html .= "</tfoot>";
            $html .= "</table>";
            $html .= "</div>";
            $html .= "</div>";
//            $html .= "</div>";
            $json = array(
                'success' => 1,
                'html' => $html
            );
        } else {
            $json = array(
                'success' => 0,
                'error' => $this->language->get('error_no_result')
            );
        }

        $this->response->setOutput(json_encode($json));
    }

    public function ProductCostSearch() {
        $this->load->language('inventory/product');
        $this->model['product'] = $this->load->model('inventory/product');
        $products = $this->model['product']->getProductCostSearch();
        $html = "";
        if ($products) {
            $ref_id = $this->request->get['ref_id'];
            $callback = $this->request->get['callback'];

//            $html .= "<div class='row'>";
            $html .= "<div class='col-md-12'>";
            $html .= "<div class='table-responsive'>";
            $html .= "<table id='tblQuickSearch' class='table table-striped' style='width: 100%'>";
            $html .= "<thead>";
            $html .= "<tr>";
            $html .= "<th>&nbsp;</th>";
            $html .= "<th>" . $this->language->get('column_name') . "</th>";
            $html .= "<th>" . $this->language->get('column_cost') . "</th>";
            $html .= "<th>" . $this->language->get('column_percent') . "</th>";

            $html .= "</tr>";
            $html .= "</thead>";
            $html .= "<tbody>";
            foreach ($products as $product) {
                $html .= "<tr>";
                $html .= "<td><a href='javascript:void(0);' onClick=\"_quickSearch('" . $ref_id . "','" . $product['product_code'] . "','" . $callback . "');\">Select</a></td>";


                $html .= "<td>" . $product['name'] . "</td>";
                $html .= "<td>" . $product['cost_price'] . "</td>";
                $html .= "<td>" . $product['percent'] . "</td>";

                $html .= "</tr>";
            }
            $html .= "</tbody>";
            $html .= "<tfoot>";
            $html .= "<tr>";
            $html .= "<th>&nbsp;</th>";
            $html .= "<th>" . $this->language->get('column_name') . "</th>";
            $html .= "<th>" . $this->language->get('column_cost') . "</th>";
            $html .= "<th>" . $this->language->get('column_percent') . "</th>";
            $html .= "</tr>";
            $html .= "</tfoot>";
            $html .= "</table>";
            $html .= "</div>";
            $html .= "</div>";
//            $html .= "</div>";
            $json = array(
                'success' => 1,
                'html' => $html
            );
        } else {
            $json = array(
                'success' => 0,
                'error' => $this->language->get('error_no_result')
            );
        }

        $this->response->setOutput(json_encode($json));
    }

    protected function validateDelete($id)
    {
        if (!$this->user->hasPermission('delete', $this->getAlias())) {
            $this->error['warning'][] = $this->language->get('error_permission_delete');
        } else {
            $row = $this->model[$this->getAlias()]->getRow(array($this->getPrimaryKey() => $id));
            $this->model['ledger'] = $this->load->model('common/stock_ledger');
            $rows = $this->model['ledger']->getRows(array('company_id' => $this->session->data['company_id'],'product_id' => $id));
            //d(array(count($row), $rows), true);
            if(count($rows) > 0) {
                $this->error['warning'][] = sprintf("Cannot Delete `%s`. Product is in use.", $row['name']);
            }
        }

        if (!$this->error) {
            //d($this->error, true);
            return true;
        } else {
            $this->session->data['warning'] = implode('<br />', $this->error['warning']);
            return false;
        }
    }

}

class PDF extends TCPDF {
    public $data = array();

//    //Page header
    public function Header() { }
//    // Page footer
   public function Footer() { }
}

?>