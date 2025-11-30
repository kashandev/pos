<?php

class ControllerCommonFunction extends Controller {

    protected function getAlias() {
        return 'common/function';
    }

    // Random Request For Session
    public function randomRequest(){ $this->response->setOutput(json_encode( array( 'success' => true ) )); }


    public function openFileManager() {
        $action = $this->url->link($this->getAlias().'/uploadFile', 'token=' . $this->session->data['token'], 'SSL');
        $post = $this->request->post;
        $data = http_build_query($post);

        $html = '<form action="'.$action.'" method="post" enctype="multipart/form-data" id="file_upload">';
        $html .= '<div class="form-group">';
        $html .= '  <div class="input-group">';
        $html .= '    <input type="file" class="form-control" name="uploaded_file" id="uploaded_file" value="Upload File" readonly/>';
        $html .= '    <span class="input-group-addon"><a href="javascript:void(0);" onclick="$(\'#file_upload\').submit();" title="Upload File" data-toggle="tooltip"><i class="fa fa-cloud-upload"></i></a></span>';
        $html .= '  </div>';
        $html .= '</div>';
        $html .= '<div class="progress">';
        $html .= '  <div style="width: 0%" role="progressbar" class="progress-bar progress-bar-primary progress-bar-striped">';
        $html .= '      <span class="sr-only">40% Complete (success)</span>';
        $html .= '  </div>';
        $html .= '</div>';
        $html .= '</form>';
        $html .= '<script src="plugins/jQuery/jQuery-form.js"></script>';
        $html .= '<script type="text/javascript">';
        $html .= '$(\'#file_upload\').ajaxForm({';
        $html .= 'dataType:  "json",';
        $html .= 'data: "' . $data . '",';
        $html .= 'beforeSend: function() {';
        $html .= '  $(\'#file_upload .progress-bar\').css("width",0)';
        $html .= '},';
        $html .= 'uploadProgress: function(event, $position, $total, $percent) {';
        $html .= '  $(\'#file_upload .progress-bar\').css("width",$percent+"%");';
        $html .= '},';
        $html .= 'complete: function($response) {';
        $html .= '  $json = $response.responseJSON;';
        $html .= '  if($json.success) {';
        $html .= '      $file_name = $json["file_name"];';
        $html .= '      $file_path = $json["file_path"];';
        $html .= '      console.log($json, $("#'.$post['file_name'].'"), $("#'.$post['file_path'].'"));';
        $html .= '      $("#'.$post['file_name'].'").val($file_name);';
        $html .= '      $("#'.$post['file_path'].'").val($file_path);';
        $html .= '  }';
        $html .= '}';
        $html .= '});';
        $html .= '</script>';

        $json  = array(
            'success' => true,
            'html' => $html,
            'post' => $post,
            'data' => $data
        );

        echo json_encode($json);
    }

    public function uploadFile() {

        $file_name = $this->request->files['uploaded_file']['name'];
        $new_file = DIR_UPLOAD . $file_name;
        $tmp_file = $this->request->files['uploaded_file']['tmp_name'];
        $ext = pathinfo($file_name,PATHINFO_EXTENSION);
        $extension = strtolower($ext);

        if ($this->request->files['uploaded_file']['size'] == 0) {
            $json = array(
                'success' => false,
                'error' => $this->data['error_invalid_file_size']
            );
        } elseif($extension != 'csv'){
            $json = array(
                'success' => false,
                'error' => 'Invalid file format!'
            );
        } elseif (is_uploaded_file($this->request->files['uploaded_file']['tmp_name'])) //file was uploaded successfully
        {
            move_uploaded_file($tmp_file, $new_file);
            $json = array(
                'success' => true,
                'file_name' => $file_name,
                'file_path' => $new_file
            );
        }

        echo json_encode($json);
        exit;
    }

    public function validateDate() {
        $lang = $this->load->language('common/function');
        $post = $this->request->post;
        foreach($post as $field => $date) {}
        $date = MySqlDate($date);
        $start_date = $this->session->data['fiscal_date_from'];
        $end_date = $this->session->data['fiscal_date_to'];
        if($date < $start_date || $date > $end_date) {
            // echo json_encode($lang['error_invalid_date']);
            $message = 'false';
        } else {
            // echo json_encode('true');
            $message = 'true';
        }
        echo $message;
    }

    public function getProductByCode() {
        $lang = $this->load->language('inventory/product');

        $product_code = $this->request->post['product_code'];
        $customerID = $this->request->post['partner_id'];
        $branchID = $this->request->post['branch_id'];

        $this->model['product'] = $this->load->model('inventory/product');
        $this->model['stock'] = $this->load->model('common/stock_ledger');


        $where = "company_id = '".$this->session->data['company_id']."' AND LOWER(`product_code`) = '".strtolower($product_code)."'";
        $product = $this->model['product']->getRow($where);

        $this->model['customer_rate'] = $this->load->model('inventory/customer_rate');
        $customerRate = $this->model['customer_rate']->GetCustomerLastRate($customerID,$product['product_id']);
        $customer_rate = $customerRate['rate'];

        $this->model['branch_rate'] = $this->load->model('inventory/branch_rate');
        $BranchRate = $this->model['branch_rate']->GetBranchLastRate($branchID,$product['product_id']);
        $branch_rate = $BranchRate['rate'];


        if($product) {
            $filter = array(
                'company_id' => $this->session->data['company_id'],
                'company_branch_id' => $this->session->data['company_branch_id'],
                'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                'product_id' => $product['product_id'],
            );
            $product['stock'] = $this->model['stock']->getStock($filter);

            $json = array(
                'success' => true,
                'product' => $product,
                'customer_rate' => $customer_rate,
                'branch_rate' => $branch_rate,
            );
        } else {
            $json = array(
                'success' => false,
                'error' => $lang['error_invalid_product']
            );
        }

        echo json_encode($json);
        exit;
    }

    // check exist product function
    public function checkExistProduct() {
        $lang = $this->load->language('inventory/product');

        $product_code = $this->request->post['product_code'];
        $customerID = $this->request->post['partner_id'];
        $branchID = $this->request->post['branch_id'];

        $this->model['product'] = $this->load->model('inventory/product');

        $where = "company_id = '".$this->session->data['company_id']."' AND LOWER(`product_code`) = '".strtolower($product_code)."'";
        $product = $this->model['product']->getRow($where);

        if($product) {
            $json = array(
                'success' => true,
            );
        } else {
            $json = array(
                'success' => false,
            );
        }

        echo json_encode($json);
        exit;
    }




    public function getProductById() {
        $lang = $this->load->language('inventory/product');
        $product_id = $this->request->post['product_id'];

        $customerID = $this->request->post['partner_id'];
        $BranchID = $this->request->post['branch_id'];

        $this->model['product'] = $this->load->model('inventory/product');
        $this->model['stock'] = $this->load->model('common/stock_ledger');
        $product = $this->model['product']->getRow(array('company_id' => $this->session->data['company_id'], 'product_id' => $product_id));

        $this->model['customer_rate'] = $this->load->model('inventory/customer_rate');
        $customerRate = $this->model['customer_rate']->GetCustomerLastRate($customerID,$product_id);
        $customer_rate = $customerRate['rate'];

        $this->model['branch_rate'] = $this->load->model('inventory/branch_rate');
        $BranchRate = $this->model['branch_rate']->GetBranchLastRate($BranchID,$product['product_id']);
        $branch_rate = $BranchRate['rate'];

       // d($product,true);

        if($product) {
            $filter = array(
                'company_id' => $this->session->data['company_id'],
                'company_branch_id' => $this->session->data['company_branch_id'],
                'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                'product_id' => $product['product_id'],
            );
            $product['stock'] = $this->model['stock']->getStock($filter);

            $json = array(
                'success' => true,
                'product' => $product,
                'customer_rate' => $customer_rate,
                'branch_rate' => $branch_rate
            );
        } else {
            $json = array(
                'success' => false,
                'post' => $this->request->post,
                'error' => $lang['error_invalid_product']
            );
        }

        echo json_encode($json);
        exit;
    }

    public function getPartnerById() {
        $lang = $this->load->language('common/function');
        $post = $this->request->post;

        $arrWhere = array();
        $arrWhere[] = "`company_id` = '{$this->session->data['company_id']}'";
        $arrWhere[] = "`company_branch_id` = '".$this->session->data['company_branch_id']."'";
        if( isset($post['partner_id']) ) {
            $arrWhere[] = "`partner_id` = '{$post['partner_id']}'";
        }
        $arrWhere = implode(' AND ', $arrWhere);

        $this->model['partner'] = $this->load->model('common/partner');
        $partner = $this->model['partner']->getRow($arrWhere);
        $json = array(
            'success' => true,
            'partner' => $partner,
            'post' => $post,
            'where' => 'WHERE ' . $arrWhere
        );
        echo json_encode($json);
        exit;
    }

    public function QSProduct() {
        $lang = $this->load->language('inventory/product');

        $html = '';
        //$html .= '<div class="table-responsive">';
        $html .= '  <table id="QSDataTable" class="table table-bordered">';
        $html .= '      <thead>';
        $html .= '      <tr>';
        $html .= '          <th class="text-center">&nbsp;</th>';
        $html .= '          <th class="text-center">'.$lang['code'].'</th>';
        $html .= '          <th class="text-center">'.$lang['name'].'</th>';
        $html .= '          <th class="text-center">'.$lang['unit'].'</th>';
        $html .= '      </tr>';
        $html .= '      </thead>';
        $html .= '      <tbody>';
        $html .= '      </tbody>';
        $html .= '  </table>';
        //$html .= '</div>';

        $json = array(
            'success' => true,
            'title' => $lang['heading_title'],
            'html' => $html
        );

        echo json_encode($json);
        exit;
    }

    public function QSAjaxProduct() {
        $lang = $this->load->language('inventory/product');
        $this->model['product'] = $this->load->model('inventory/product');
        $this->model['stock'] = $this->load->model('common/stock_ledger');


        $data = array();
        $aColumns = array('action', 'product_code', 'name','unit');

        /*
         * Paging
         */
        $sLimit = "";
        if (isset($_GET['start']) && $_GET['length'] != '-1') {
            $data['criteria']['start'] = $_GET['start'];
            $data['criteria']['limit'] = $_GET['length'];
        }

        /*
         * Ordering
         */
        $aOrder = array();
        if (isset($_GET['order'])) {
            foreach($_GET['order'] as $order) {
                $aOrder[] = $aColumns[$order['column']] . ' ' . $order['dir'];
            }
            $data['criteria']['orderby'] = ' ORDER BY ' . implode(',', $aOrder);
        }


        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        $arrWhere = array();
        $arrWhere[] = "`company_id` = '".$this->session->data['company_id']."'";
        if (isset($_GET['search']['value']) && $_GET['search']['value'] != "") {
            $arrSSearch = array();
            foreach($_GET['columns'] as $i => $column) {
                if($column['searchable']=='true') {
                    $arrSSearch[] = "LOWER(`" . $aColumns[$i] . "`) LIKE '%" . $this->db->escape(strtolower($_GET['search']['value'])) . "%'";
                }
            }
            if(!empty($arrSSearch)) {
                $arrWhere[] = '(' . implode(' OR ', $arrSSearch) . ')';
            }
        }

        /* Individual column filtering */
        foreach($_GET['columns'] as $i => $column) {
            if($column['searchable']=='true') {
                $arrSSearch[] = "LOWER(`" . $aColumns[$i] . "`) LIKE '%" . $this->db->escape(strtolower($column['search']['value'])) . "%'";
            }
        }

        if (!empty($arrWhere)) {
            //$data['filter']['RAW'] = substr($sWhere, 5, strlen($sWhere) - 5);
            $data['filter']['RAW'] = implode(' AND ', $arrWhere);
        }

        //d($data, true);
        $results = $this->model['product']->getLists($data);
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
            $filter = array(
                'company_id' => $this->session->data['company_id'],
                'company_branch_id' => $this->session->data['company_branch_id'],
                'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                'product_id' => $aRow['product_id'],
            );
            $stock = $this->model['stock']->getStock($filter);

            $this->model['customer_rate'] = $this->load->model('inventory/customer_rate');
            $customerRate = $this->model['customer_rate']->GetCustomerLastRate($this->session->data['customer_id'],$aRow['product_id']);
            $customer_rate = $customerRate['rate'];

            $this->model['branch_rate'] = $this->load->model('inventory/branch_rate');
            $BranchRate = $this->model['branch_rate']->GetBranchLastRate($this->session->data['to_branch_id'],$aRow['product_id']);
            $branch_rate = $BranchRate['rate'];


            $data = array(
                'product_id' => $aRow['product_id'],
                'product_category_id' => $aRow['product_category_id'],
                'product_category' => $aRow['product_category'],
                'product_code' => $aRow['product_code'],
                'name' => htmlentities($aRow['name']),
                'cubic_meter' => $aRow['cubic_meter'],
                'cubic_feet' => $aRow['cubic_feet'],
                'unit_id' => $aRow['unit_id'],
                'unit' => $aRow['unit'],
                'cost_price' => $aRow['cost_price'],
                'sale_price' => $aRow['sale_price'],
                'stock_qty' => ($stock['stock_qty']?$stock['stock_qty']:0),
                'stock_amount' => ($stock['stock_amount']?$stock['stock_amount']:0),
                'avg_stock_rate' => ($stock['avg_stock_rate']?$stock['avg_stock_rate']:0),
                'customer_rate' => $customer_rate,
//                'branch_rate' => $branch_rate,
            );

            if(isset($_GET['callback']) && $_GET['callback'] != '') {
                $strAction = '<button type="button" class="btn btn-primary btn-xs" onclick="'.$_GET['callback'].'(this);"';
            } else {
                $strAction = '<button type="button" class="btn btn-primary btn-xs" onclick="setProductInformation(this);"';
            }
            $strAction .= 'data-element="' . $_GET['element'] . '" ';
            $strAction .= 'data-field="' . $_GET['field'] . '" ';
            foreach($data as $c => $v) {
                $strAction .= 'data-' . $c . '="' .$v . '" ';
            }
            $strAction .= '><i class="fa fa-check"></i></button>';

            for ($i = 0; $i < count($aColumns); $i++) {
                if ($aColumns[$i] == 'action') {
                    $row[] = $strAction;
                } elseif ($aColumns[$i] == 'created_at') {
                    $row[] = stdDateTime($aRow['created_at']);
                } else {
                    $row[] = $aRow[$aColumns[$i]];
                }

            }
            $output['aaData'][] = $row;
        }

        echo json_encode($output);
    }

    public function getDocumentLedger() {
        $lang = $this->load->language($this->getAlias());
        $document_type_id = $this->request->post['document_type_id'];
        $document_id = $this->request->post['document_id'];
        $this->model['ledger'] = $this->load->model('gl/ledger');
        $query = $this->model['ledger']->getDocumentLedger($document_type_id, $document_id);
//        $query = $this->model['ledger']->getDocumentLedger(23, '{70E2A43A-1C3C-4BA8-9727-648E59D97CF1}');
       // echo '<pre>';
       // print_r($query);
       // exit;
        $total_debit = 0;
        $total_credit = 0;
        $html = '<table id="tblLedger" class="table table-bordered table-striped">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th class="text-center">'.$lang['account'].'</th>';
        $html .= '<th class="text-center">'.$lang['debit'].'</th>';
        $html .= '<th class="text-center">'.$lang['credit'].'</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        foreach($query->rows as $row) {
            $html .= '<tr>';
            $html .= '<td class="text-left">'.$row['account'].'</td>';
            $html .= '<td class="text-right">'.number_format($row['debit'],2).'</td>';
            $html .= '<td class="text-right">'.number_format($row['credit'],2).'</td>';
            $html .= '</tr>';
            $total_debit += $row['debit'];
            $total_credit += $row['credit'];
        }
        $html .= '</tbody>';
        $html .= '<tfoot>';
        $html .= '<tr>';
        $html .= '<th>&nbsp;</th>';
        $html .= '<th class="text-right">'.number_format($total_debit,2).'</th>';
        $html .= '<th class="text-right">'.number_format($total_credit,2).'</th>';
        $html .= '</tr>';
        $html .= '</tfoot>';
        $html .= '</table>';

        $json = array(
            'success' => true,
            'post' => $this->request->post,
            'query' => $query,
            'title' => $lang['ledger_entry'],
            'html' => $html
        );

        echo json_encode($json);
        exit;
    }

    public function getPartner() {
        $post = $this->request->post;
        $partner_type_id = $post['partner_type_id'];
        $partner_id = $post['partner_id'];


        $this->model['coa'] = $this->load->model('gl/coa_level3');
        $arrCOAS = $this->model['coa']->getArrays('coa_level3_id', 'level3_display_name', array('company_id' => $this->session->data['company_id']));

        $this->model['partner'] = $this->load->model('common/partner');
        $filter = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'partner_type_id' => $partner_type_id

        );

            $partners = $this->model['partner']->getRows($filter,array('name'));
        $html = '<option value="">&nbsp;</option>';
        $this->model['document'] = $this->load->model('common/document');
        $arrPartners = array();
        foreach($partners as $partner) {
            if($partner['partner_id'] == $partner_id) {
                $html .= '<option value="'.$partner['partner_id'].'" selected="true">'.$partner['name'].'</option>';
            } else {
                $html .= '<option value="'.$partner['partner_id'].'">'.$partner['name'].'</option>';
            }
            $arrPartners[$partner['partner_id']]= $partner;
        }

        $json = array(
            'success' => true,
            'html' => $html,
            'partners' => $arrPartners
        );

        $this->response->setOutput(json_encode($json));
    }
    public function getSaleMonthChart() {

        $companyBranchId = $this->session->data['company_branch_id'];

        $lang = $this->load->language('inventory/pos_invoice');
        $this->model['pos_invoice_detail'] = $this->load->model('inventory/pos_invoice_detail');
        $rows = $this->model['pos_invoice_detail']->getTotalSaleMonth($companyBranchId);

//d($rows,true);

        foreach($rows as $r)
        {
            $categories[$r['date_month']] = $r['date_month'];
        }


        //  d($categories,true);


        foreach($categories as $category) {
            $series['Month'][$category] = 0;
            foreach($rows as $row) {
                if($row['date_month']==$category) {

                    $series['Month'][$category]=$row['total'];
                }}
        }

//        d($series,true);
        $series2 = array();
        foreach($series as $name => $row) {
            $data = array();
            foreach($row as $r) {
                $data[] = floatval($r);
            }
            $series2[] = array(
                'name' => $name,
                'data' => $data
            );
        }
//d(array($series,$series2));
        $data = array(
            'chart' => array(
                'type' => 'column'
            ),
            'title' => array(
                'text' => 'Monthly Sale'
            ),
            'xAxis' => array(
                'categories' => array_values($categories)
            ),
            'yAxis' => array(
                'title' => array(
                    'text' => 'Total Amount'
                ),                ),
            'series' => $series2
        );


       // d($data,true);
        $json = array(
            'success' => true,
            'data' => $data
        );
//d($json,true);
        echo json_encode($json);
        exit;
    }

    public function get_top_5_customers() {

        $companyBranchId = $this->session->data['company_branch_id'];

        $lang = $this->load->language('inventory/pos_invoice');
        $this->model['home'] = $this->load->model('common/home');
        $filter = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
        );
        $rows = $this->model['home']->getTopCustomer($filter);






        foreach($rows as $r)
        {
            $categories[$r['name']] = $r['name'];
        }

        foreach($categories as $category) {
            $series['name'][$category] = 0;
            foreach($rows as $row) {
                if($row['name']==$category) {
                    $series['name'][$category]= $row['total'];
                }
            }
        }

        $series2 = array();
        $data = array();
    //d($series,true);
        foreach($series as $row) {

            foreach ($row as $k=>$key) {
                $data[] = array(
                    'name' => $k,
                    'y'=> floatval($key),
//                    'y'=> floatval(rand(1000,5000)),
                );
            }


            //d($data,true);
            $series2[] = array(
                'name' => 'Customer Name',
                'data' => $data
            );

        }


        $data = array(
            'chart' => array(
                'type' => 'pie'
            ),
            'title' => array(
                'text' => 'Top 5 Customers'
            ),
            'tooltip' => array(
                'pointFormat' => '{point.name}: <b>{point.percentage:.1f}%</b>'
            ),
            'plotOptions' => array(
                'pie' => array(
                    'allowPointSelect' => true,
                    'cursor' => 'pointer',
                    'dataLabels' => array(
                        'enabled' => true,
                        'format' => '<small>{point.name}</small>: {point.y} ',
                        'style' => array(
                            'color' => 'black'
                        ),
                    ),
                    'showInLegend' => false
                )
            ),
//            'xAxis' => array(
//                'categories' => array_values($categories)
//            ),
//            'yAxis' => array(
//                'title' => array(
//                    'text' => 'Balance Amount'
//                ),
//            ),
            'series' => $series2
        );


        $json = array(
            'success' => true,
            'data' => $data
        );
//d($json,true);
        echo json_encode($json);
        exit;
    }
   
   public function getWarehouseStock() {
        $post = $this->request->post;
        $this->model['stock'] = $this->load->model('common/stock_ledger');
        $stock = $this->model['stock']->getWarehouseStock($post['product_id'], $post['warehouse_id'], $post['document_identity'], MySqlDate($post['document_date']));
        $json = array(
            'success' => true,
            'stock_qty' => $stock['stock_qty'],
            'avg_stock_rate' => $stock['avg_stock_rate'],
            'stock_amount' => ($stock['stock_qty'] * $stock['avg_stock_rate']),
        );
        echo json_encode($json);
        exit;
    }

    public function getCustomerUnit(){

        $post = $this->request->post;

        $partner_id = $post['partner_id'];

        //$this->model['coa'] = $this->load->model('gl/coa_level3');
        //$arrCOAS = $this->model['coa']->getArrays('coa_level3_id', 'level3_display_name', array('company_id' => $this->session->data['company_id']));

        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['customer_unit'] = $this->load->model('inventory/customer_unit');

        $filter = array(
            'company_id' => $this->session->data['company_id'],
            //  'company_branch_id' => $this->session->data['company_branch_id'],
            'partner_id' => $partner_id
        );

        //$partners = $this->model['partner']->getRows($filter);
        //d($filter,true);
        $customer_units = $this->model['customer_unit']->getRows($filter);

        //d($customer_units,true);

        $html = '<option value="">&nbsp;</option>';
        //$this->model['document'] = $this->load->model('common/document');
        $arrCustomerUnits = array();
        foreach($customer_units as $customer_unit) {
            //if($customer_unit['customer_unit_id'] == $customer_unit_id) {
                $html .= '<option value="'.$customer_unit['customer_unit_id'].'" selected="true">'.$customer_unit['customer_unit'].'</option>';
            //} else {
              //  $html .= '<option value="'.$customer_unit['customer_unit_id'].'">'.$customer_unit['customer_unit'].'</option>';
            //}
            $arrCustomerUnits[$customer_unit['customer_unit_id']]= $customer_unit;
        }
        $json = array(
            'success' => true,
            'html' => $html,
            'customer_units' => $arrCustomerUnits
        );

        $this->response->setOutput(json_encode($json));

    }
}

?>