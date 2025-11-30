<?php

class ControllerInventorySaleOrder extends HController {

    protected $document_type_id = 5;

    protected function getAlias() {
        return 'inventory/sale_order';
    }

    protected function getPrimaryKey() {
        return 'sale_order_id';
    }

    protected function getList() {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }

    public function getAjaxLists() {
        $lang = $this->load->language('inventory/sale_order');
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        $data = array();
        $aColumns = array('action', 'document_identity', 'document_date', 'partner_type', 'partner_name', 'net_amount');

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
        $arrWhere[] = "`company_id` = '".$this->session->data['company_id']."'";
        $arrWhere[] = "`company_branch_id` = '".$this->session->data['company_branch_id']."'";
        $arrWhere[] = "`fiscal_year_id` = '".$this->session->data['fiscal_year_id']."'";
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
                'text' => $lang['print'],
                'target' => '_blank',
                'href' => $this->url->link($this->getAlias() . '/printDocument', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $aRow[$this->getPrimaryKey()], 'SSL'),
                'btn_class' => 'btn btn-info btn-xs',
                'class' => 'fa fa-print'
            );

            if($aRow['is_post']==0) {
                $actions[] = array(
                    'text' => $lang['post'],
                    'href' => $this->url->link($this->getAlias() . '/post', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $aRow[$this->getPrimaryKey()], 'SSL'),
                    'btn_class' => 'btn btn-info btn-xs',
                    'class' => 'fa fa-thumbs-up',
                    'click'=> 'return confirm(\'Are you sure you want to post this item?\');'
                );

                $actions[] = array(
                    'text' => $lang['delete'],
                    'href' => 'javascript:void(0);',
                    'click' => "ConfirmDelete('" . $this->url->link($this->getAlias() . '/delete', 'token=' . $this->session->data['token'] . '&id=' . $aRow[$this->getPrimaryKey()], 'SSL') . "')",
                    'btn_class' => 'btn btn-danger btn-xs',
                    'class' => 'fa fa-times'
                );
            }


            $strAction = '';
            foreach ($actions as $action) {
                $strAction .= '<a '.(isset($action['btn_class'])?'class="'.$action['btn_class'].'"':'').' href="' . $action['href'] . '" data-toggle="tooltip" title="' . $action['text'] . '" ' . (isset($action['click']) ? 'onClick="' . $action['click'] . '"' : '') . '>';
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
                }elseif ($aColumns[$i] == 'document_date') {
                    $row[] = stdDate($aRow['document_date']);
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

        $this->model['product'] = $this->load->model('inventory/product');
        $this->data['products'] = $this->model['product']->getRows(array('company_id' => $this->session->data['company_id']),array('name'));

        $this->model['currency'] = $this->load->model('setup/currency');
        $this->data['currencys'] = $this->model['currency']->getRows();

        $this->model['unit'] = $this->load->model('inventory/unit');
        $this->data['units'] = $this->model['unit']->getRows(array('company_id' => $this->session->data['company_id']));
        $arrUnits = $this->model['unit']->getArrays('unit_id','name',array('company_id' => $this->session->data['company_id']));

        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->data['warehouses'] = $this->model['warehouse']->getRows(array('company_id' => $this->session->data['company_id'], 'company_branch_id' => $this->session->data['company_branch_id']));
        $this->data['arrWarehouses'] = json_encode($this->data['warehouses']);

        $this->model['salesman'] = $this->load->model('setup/salesman');
        $this->data['salesmans'] = $this->model['salesman']->getRows(array('company_id' => $this->session->data['company_id'], 'company_branch_id' => $this->session->data['company_branch_id']));

        $this->data['href_get_ref_document_no'] = $this->url->link($this->getAlias() . '/getReferenceDocumentNos', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['href_get_ref_document'] = $this->url->link($this->getAlias() . '/getReferenceDocument', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');

        $this->data['href_get_sale_order'] =  $this->url->link($this->getAlias() . '/getData', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');

        $this->data['base_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['base_currency'] = $this->session->data['base_currency_name'];
        $this->data['document_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['conversion_rate'] = "1.00";

        $this->data['partner_types'] = $this->session->data['partner_types'];

        $this->data['document_date'] = stdDate();
        if (isset($this->request->get['delivery_challan_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $this->data['isEdit'] = 1;
            $result = $this->model[$this->getAlias()]->getRow(array($this->getPrimaryKey() => $this->request->get[$this->getPrimaryKey()]));
            foreach ($result as $field => $value) {
                if ($field == 'document_date') {
                    $this->data[$field] = stdDate($value);
                } else {
                    $this->data[$field] = $value;
                }
            }

            $this->model['sale_order_detail'] = $this->load->model('inventory/sale_order_detail');
            $rows = $this->model['sale_order_detail']->getRows(array('sale_order_id' => $this->request->get['sale_order_id']));
            foreach($rows as $row_no => $row) {
                $this->data['sale_order_details'][$row_no] = $row;
                $this->data['sale_order_details'][$row_no]['unit'] = $arrUnits[$row['unit_id']];
            }

        }

        $this->data['restrict_out_of_stock'] = $this->session->data['restrict_out_of_stock'];
        $this->data['action_post'] = $this->url->link($this->getAlias() . '/post', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_print'] = $this->url->link($this->getAlias() . '/printDocument', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');

        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
        $this->data['strValidation'] = "{
            'rules': {
                'document_date': {'required': true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},
                'partner_id': {'required': true},
                'total_qty': {'required': true, 'min':1},
            },
            messages: {
            document_date:{
                remote: 'Invalid Date'
            }}
        }";

        $this->response->setOutput($this->render());

    }

    protected function insertData($data) {

        $this->model['document_type'] = $this->load->model('common/document_type');
        $document = $this->model['document_type']->getNextDocument($this->document_type_id);

        $data['document_date'] = MySqlDate($data['document_date']);
        $data['company_id'] = $this->session->data['company_id'];
        $data['company_branch_id'] = $this->session->data['company_branch_id'];
        $data['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
        $data['document_type_id'] = $this->document_type_id;
        $data['document_prefix'] = $document['document_prefix'];
        $data['document_no'] = $document['document_no'];
        $data['document_identity'] = $document['document_identity'];
        $data['base_amount'] = $data['net_amount'] * $data['conversion_rate'];

//        d($data,true);

        $sale_order_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);

        $this->model['document'] = $this->load->model('common/document');

        $insert_document = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'document_type_id' => $this->document_type_id,
            'document_id' => $sale_order_id,
            'document_identity' => $data['document_identity'],
            'document_date' => $data['document_date'],
            'partner_type_id' => $data['partner_type_id'],
            'partner_id' => $data['partner_id'],
            'document_currency_id' => $data['document_currency_id'],
            'document_amount' => $data['net_amount'],
            'conversion_rate' => $data['conversion_rate'],
            'base_currency_id' => $data['base_currency_id'],
            'base_amount' => $data['base_amount'],
        );
        $document_id = $this->model['document']->add($this->getAlias(), $insert_document);

        $this->model['sale_order_detail'] = $this->load->model('inventory/sale_order_detail');
        foreach ($data['sale_order_details'] as $sort_order => $detail) {

            $detail['sale_order_id'] = $sale_order_id;
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['sort_order'] = $sort_order;
            $sale_order_detail_id=$this->model['sale_order_detail']->add($this->getAlias(), $detail);
        }
        d($data['sale_order_details'], true);

        return $sale_order_id;
    }

    protected function insertRedirect($id, $data) {
        $url = $this->getURL();
        $this->redirect($this->url->link($this->getAlias().'/update', 'token=' . $this->session->data['token'] . '&sale_order_id=' . $id, 'SSL'));
    }

    protected function updateData($primary_key, $data) {
        $sale_order_id = $primary_key;
        $data['document_date'] = MySqlDate($data['document_date']);
        $this->model['sale_order'] = $this->load->model('inventory/sale_order');
        $this->model['sale_order_detail'] = $this->load->model('inventory/sale_order_detail');

        $this->model['sale_order']->edit($this->getAlias(), $primary_key, $data);
        $this->model['sale_order_detail']->deleteBulk($this->getAlias(), array('sale_order_id' => $sale_order_id));

        $this->model['document'] = $this->load->model('common/document');
        $this->model['document']->deleteBulk($this->getAlias(), array('document_id' => $sale_order_id));
        $insert_document = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'document_type_id' => $this->document_type_id,
            'document_id' => $sale_order_id,
            'document_identity' => $data['document_identity'],
            'document_date' => $data['document_date'],
            'partner_type_id' => $data['partner_type_id'],
            'partner_id' => $data['partner_id'],
            'document_currency_id' => $data['document_currency_id'],
            'document_amount' => $data['net_amount'],
            'conversion_rate' => $data['conversion_rate'],
            'base_currency_id' => $data['base_currency_id'],
            'base_amount' => ($data['net_amount'] * $data['conversion_rate']),
        );
        $document_id = $this->model['document']->add($this->getAlias(), $insert_document);

        foreach ($data['sale_order_details'] as $sort_order => $detail) {
            $detail['sale_order_id'] = $sale_order_id;
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['sort_order'] = $sort_order;
            $sale_order_detail_id=$this->model['sale_order_detail']->add($this->getAlias(), $detail);
        }

        return $sale_order_id;
    }

    protected function updateRedirect($id, $data) {
        $url = $this->getURL();
        $this->redirect($this->url->link($this->getAlias().'/update', 'token=' . $this->session->data['token'] . '&sale_order_id=' . $id, 'SSL'));
    }

    protected function validateDelete() {
        if (!$this->user->hasPermission('delete', $this->getAlias())) {
            $this->error['warning'] = $this->language->get('error_permission_delete');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    protected function deleteData($primary_key) {
        $this->model['sale_order_detail'] = $this->load->model('inventory/sale_order_detail');
        $this->model['sale_order_detail']->deleteBulk($this->getAlias(),array('sale_order_id' => $primary_key));

        $this->model['document'] = $this->load->model('common/document');
        $this->model['document']->deleteBulk($this->getAlias(), array('document_id' => $primary_key));

        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['ledger']->deleteBulk($this->getAlias(), array('document_id' => $primary_key));

        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);
    }

//    public function getSaleOrder() {
//        if($this->request->server['REQUEST_METHOD'] == 'POST') {
//
//            $document_id = $this->request->post['ref_document_id'];
//            $supplier_id = $this->request->post['supplier_id'];
//            $sale_order_id = $this->request->get['sale_order_id'];
//
//            $this->model['product'] = $this->load->model('setup/product');
//            $products = $this->model['product']->getRows(array('company_id' => $this->session->data['company_id']));
//
//            $this->model['warehouse'] = $this->load->model('inventory/warehouse');
//            $warehouses = $this->model['warehouse']->getRows(array('company_id' => $this->session->data['company_id']));
//
//            $this->model['unit'] = $this->load->model('setup/unit');
//            $units = $this->model['unit']->getRows(array('company_id' => $this->session->data['company_id']));
//
//            $this->model['currency'] = $this->load->model('setup/currency');
//            $currencys = $this->model['currency']->getArrays('currency_id','name',array('company_id' => $this->session->data['company_id']));
//
//            $this->model['sale_order'] = $this->load->model('inventory/sale_order');
//
//            $rows = $this->model['sale_order']->getSaleOrder($document_id,$supplier_id);
//
//            $this->model['sale_order'] = $this->load->model('inventory/sale_order');
//            $html = '';
//            foreach($rows as $grid_row => $row) {
//                $order_qty = $this->model['sale_order']->getOrderQty($document_id, $row['product_id']);
//                $received_qty = $this->model['sale_order']->getUtilizedProduct($document_id, $row['product_id'], $sale_order_id);
//                $html .= '<tbody id="grid_row_' . $grid_row . '" row_id="'. $grid_row .'">';
//                $html .='<tr>';
//                $html .='<td><input  type="text" name="sale_order_details['. $grid_row .'][product_code]" id="sale_order_detail_code_'. $grid_row .'" class="" value="'.$row['product_code'].'" readonly="true" /></td>';
//                $html .='<td>';
////                $html .='<div class="form-group input-group">';
//                $html .='<input  type="hidden" name="sale_order_details['. $grid_row .'][product_id]" id="sale_order_detail_id_'. $grid_row .'" class="" value="'.$row['product_id'].'" />';
//                $html .='<select class="" id="sale_order_detail_product_id_' . $grid_row . '" name="sale_order_details[' . $grid_row . '][product_id]" disabled="true">';
//                $html .='<option value=""></option>';
//                foreach($products as $product) {
//                    $html .='<option value="' . $product['product_id'] . '" '.($product['product_id'] == $row['product_id']?'selected="selected"':'').' >' . $product['name'] .'</option>';
//                }
//                $html .='</select>';
////                $html .='<span class="input-group-btn ">';
////                $html .='<button type="button"  model="setup/product" ref_id="sale_order_detail_product_id_' . $grid_row . '" callback="getProductInformation"  value="..." class="QSearch btn btn-default" ><i class="fa fa-search"></i></button>';
////                $html .='</span>';
////                $html .='</div>';
//                $html .='</td>';
//                $html .='<td><select class="" name="sale_order_details['. $grid_row .'][warehouse_id]">';
//                $html .='<option value=""></option>';
//                foreach($warehouses as $warehouse) {
//                    $html .='<option value="' . $warehouse['warehouse_id'] .'" '.($warehouse['warehouse_id'] == $row['warehouse_id']?'selected="selected"':'').' >' . $warehouse['name'] . '</option>';
//                }
//                $html .='</select></td>';
//                $html .='<td>';
//                $html .='<input type="hidden" class="fDecimal" id="sale_order_detail_order_qty_'. $grid_row .'" name="sale_order_details['. $grid_row .'][order_qty]" value="'.$order_qty.'" />';
//                $html .='<input type="hidden" class="fDecimal" id="sale_order_detail_received_qty_'. $grid_row .'" name="sale_order_details['. $grid_row .'][received_qty]" value="'.$received_qty.'" />';
//                $html .='<input type="text" class="fDecimal" id="sale_order_detail_qty_'. $grid_row .'" name="sale_order_details['. $grid_row .'][qty]" value="'.($order_qty-$received_qty).'" onchange="calcRowTotal('. $grid_row .',\'qty\')" title="OQ:'.$order_qty.',RQ:'.$received_qty.',BQ:'.($order_qty-$received_qty).'" />';
//                $html .='</td>';
//                $html .='<td>';
//                $html .='<input  type="hidden" name="sale_order_details['. $grid_row .'][unit_id]" id="sale_order_detail_unit_id_'. $grid_row .'" class="" value="'.$row['unit_id'].'" />';
//                $html .='<select class="" id="sale_order_detail_unit_id_' . $grid_row . '" name="sale_order_details['. $grid_row .'][unit_id]" disabled="true">';
//                $html .='<option value=""></option>';
//                foreach($units as $unit) {
//                    $html .='<option value="' . $unit['unit_id'] . '" '.($unit['unit_id'] == $row['unit_id']?'selected="selected"':'').' >' . $unit['name'] . '</option>';
//                }
//                $html .='</select></td>';
//                $html .='<td><input type="text" class="fDecimal" id="sale_order_detail_rate_'. $grid_row .'" name="sale_order_details['. $grid_row .'][rate]" value="'.$row['rate'].'" onchange="calcRowTotal('. $grid_row .',\'rate\')" readonly="true" /></td>';
//                $html .='<td>';
//                $html .='<input  type="hidden" name="sale_order_details['. $grid_row .'][document_currency_id]" id="sale_order_detail_document_currency_id_'. $grid_row .'" class="" value="'.$row['document_currency_id'].'" />';
//                $html .= '<select class="" id="sale_order_detail_document_currency_id_'. $grid_row .'" name="sale_order_details['. $grid_row .'][document_currency_id]" onchange="getCurrencyRate('. $grid_row .');" disabled="true">';
//                $html .='<option value=""></option>';
//                foreach($currencys as $currency_id => $value) {
//                    $html .='<option value="' . $currency_id . '" '.($currency_id == $row['document_currency_id']?'selected="selected"':'').'>' . $value . '</option>';
//                }
//                $html .='</select></td>';
//                $html .='<td><input type="text" class="fDecimal" id="sale_order_detail_conversion_rate_'. $grid_row .'" name="sale_order_details['. $grid_row .'][conversion_rate]" value="'.$row['conversion_rate'].'" onchange="calcRowTotal('. $grid_row .',\'conversion_rate\')" readonly="true" /></td>';
//                $html .='<td><input type="text" class="fDecimal" id="sale_order_detail_amount_'. $grid_row .'" name="sale_order_details['. $grid_row .'][amount]" value="'.$row['amount'].'" readonly="readonly" /></td>';
//                $html .='<td>&nbsp;</td>';
//                $html .='</tr>';
//                $html .='</tbody>';
//            }
//            $html .= "</tbody>";
//
////d($document,true);
//            $json = array(
//                'success' => true,
//                'html' => $html,
//                'row' => $row,
//            );
//            // d($json,true);
//        }
//        else {
//            $this->load->language('setup/product');
//            $json = array(
//                'success' => false,
//                'error' => $this->language->get('error_select_product')
//            );
//        }
//        echo json_encode($json);
//        exit;
//    }
//
//    public function getPOrderByDocType() {
//        if($this->request->server['REQUEST_METHOD'] == 'POST' && $this->request->post['supplier_id'] && $this->request->post['ref_document_type_id']) {
//            $ref_document_id = $this->request->post['ref_document_id'];
//            $this->model['document'] = $this->load->model('common/document');
//            $documents = $this->model['document']->getRows(array('company_id' =>$this->session->data['company_id'],'fiscal_year_id' =>$this->session->data['fiscal_year_id'],'company_branch_id' =>$this->session->data['company_branch_id'],'document_type_id' => $this->request->post['ref_document_type_id'],'people_id' => $this->request->post['supplier_id']));
//            $html = '<option value="">&nbsp;</option>';
//            foreach($documents as $document) {
//                $html .= '<option value="' . $document['document_id'] . '" '.($document['document_id'] == $ref_document_id ? 'selected="true"': '').'  >'.$document['document_identity'].'</option>';
//            }
//            echo json_encode(array('success' => true, 'identity' => $html));
//        }
//    }


    public function getReferenceDocumentNos() {
        $sale_order_id = $this->request->get['sale_order_id'];
        $post = $this->request->post;
        //d(array($sale_order_id, $post), true);

        //Purchase Order
        $this->model['quotation'] = $this->load->model('inventory/quotation');
        $where = "company_id=" . $this->session->data['company_id'];
        $where .= " AND company_branch_id='" . $this->session->data['company_branch_id'] . "'";
        $where .= " AND fiscal_year_id=" . $this->session->data['fiscal_year_id'];
        $where .= " AND partner_type_id='" . $post['partner_type_id'] . "'";
        $where .= " AND partner_id='" . $post['partner_id'] . "'";
//        $where .= " AND is_post=1";

        $quotations = $this->model['quotation']->getQuotations($where,$sale_order_id);
//        d($quotations, true);
        foreach($quotations as $quotation_id => $quotation) {
            foreach($quotation['products'] as $product_id => $product) {
                if($product['order_qty'] <= $product['utilized_qty']) {
                    unset($quotation['products'][$product_id]);
                }
            }
            if(empty($quotation['products'])) {
                unset($quotations[$quotation_id]);
            }
        }

        $html = "";

            $html .= '<option value="">&nbsp;</option>';
        foreach($quotations as $quotation_id => $quotation) {
                if($quotation['quotation_id']==$post['ref_document_id']) {
                    $html .= '<option value="'.$quotation_id.'" selected="true">'.$quotation['document_identity'].'</option>';
                } else {
                    $html .= '<option value="'.$quotation_id.'">'.$quotation['document_identity'].'</option>';
                }

        }

//        d($quotation,true);
        $json = array(
            'success' => true,
            'sale_order_id' => $quotation_id,
            'post' => $post,
            'where' => $where,
            'html' => $html
        );

        echo json_encode($json);
    }

    public function getReferenceDocument() {
        $sale_order_id = $this->request->get['sale_order_id'];
        $post = $this->request->post;

        //Purchase Order
        $this->model['quotation'] = $this->load->model('inventory/quotation');
        $where = "company_id=" . $this->session->data['company_id'];
        $where .= " AND company_branch_id='" . $this->session->data['company_branch_id'] . "'";
        $where .= " AND fiscal_year_id=" . $this->session->data['fiscal_year_id'];
        $where .= " AND partner_id='" . $post['partner_id'] . "'";
        $where .= " AND document_identity='" . $post['ref_document_identity'] . "'";

        $quotations = $this->model['quotation']->getQuotations($where,$sale_order_id);
        $quotation = $quotations[$post['ref_document_identity']];
//        d($quotation,true);


        $details = array();
        $row_no = 0;
        foreach($quotation['products'] as $product) {
//            $details[$row_no]['balanced_qty'] = ($product['order_qty'] - $product['utilized_qty']);
            if($product['order_qty'] - $product['utilized_qty'] > 0)
            {
                $href = $this->url->link('inventory/quotation/update', 'token=' . $this->session->data['token'] . '&quotation_id=' . $quotation['quotation_id']);
                $details[$row_no] = $product;
                $details[$row_no]['ref_document_identity'] = $quotation['document_identity'];
                $details[$row_no]['row_identity'] = $quotation['document_identity'].'-'.$product['product_code'];
                $details[$row_no]['href'] = $href;
                $details[$row_no]['balanced_qty'] = ($product['order_qty'] - $product['utilized_qty']);
                $details[$row_no]['utilized_qty'] = ($product['order_qty'] - $product['utilized_qty']);

                $row_no++;
            }
        }

        $quotation['products'] = $details;
        $json = array(
            'success' => true,
            'quotation_id' => $quotation_id,
            'post' => $post,
            'data' => $quotation,
        );

        echo json_encode($json);
    }

    public function ajaxValidateForm() {
        $post  = $this->request->post;
        $lang = $this->load->language('inventory/sale_order');
        $error = array();

        if($post['voucher_date'] == '') {
            $error[] = $lang['error_voucher_date'];
        }

        if($post['supplier_id'] == '') {
            $error[] = $lang['error_supplier'];
        }

        $details = $post['sale_order_details'];
        if(empty($details)) {
            $error[] = $lang['error_input_detail'];
        } else {
            $row_no = 0;
            foreach($details as $detail) {
                $row_no++;
                if($detail['product_code'] == '') {
                    $error[] = $lang['error_product_code'] . ' for Row ' . $row_no;
                }
                if($detail['product_id'] == '') {
                    $error[] = $lang['error_product'] . ' for Row ' . $row_no;
                }
                if($detail['warehouse_id'] == '') {
                    $error[] = $lang['error_warehouse'] . ' for Row ' . $row_no;
                }
                if($detail['qty'] == '') {
                    $error[] = $lang['error_qty'] . ' for Row ' . $row_no;
                }
                if($detail['unit_id'] == '') {
                    $error[] = $lang['error_unit'] . ' for Row ' . $row_no;
                }
                if($detail['rate'] == '') {
                    $error[] = $lang['error_rate'] . ' for Row ' . $row_no;
                }
                if($detail['amount'] == '') {
                    $error[] = $lang['error_amount'] . ' for Row ' . $row_no;
                }
                if($detail['qty'] > ($detail['order_qty']-$detail['received_qty'])) {
                    $error[] = $lang['error_qty'] . ' for Row ' . $row_no;
                }
            }
        }

        if (!$error) {
            $json = array(
                'success' => true
            );
        } else {
            $json = array(
                'success' => false,
                'error' => implode("\r\n",$error),
                'errors' => $error,
                'post' => $post
            );
        }

        echo json_encode($json);
        exit;
    }

    public function printDocument() {
        $company_id = $this->session->data['company_id'];
        $company_branch_id = $this->session->data['company_branch_id'];
        $fiscal_year_id = $this->session->data['fiscal_year_id'];

        $sale_order_id = $this->request->get['sale_order_id'];
        $this->data['lang'] = $this->load->language($this->getAlias());

        $this->model['image'] = $this->load->model('tool/image');
        $this->model['company'] = $this->load->model('setup/company');
        $this->model['company_branch'] = $this->load->model('setup/company_branch');

        $company = $this->model['company']->getRow(array('company_id' => $company_id));
        $this->data['company'] = $company;
        if ($company['company_logo'] && file_exists(DIR_IMAGE . $company['company_logo'])) {
            $company_logo = $this->model['image']->resize($company['company_logo'], 75, 75);
        } else {
            $company_logo = "";
        }
        $this->data['company_logo'] = $company_logo;
        $this->data['company_branch'] = $this->model['company_branch']->getRow(array('company_id' => $company_id, 'company_branch_id' => $company_branch_id));

        $this->model['sale_order'] = $this->load->model('inventory/sale_order');
        $row = $this->model['sale_order']->getRow(array('sale_order_id' => $sale_order_id));

        $this->model['partner'] = $this->load->model('common/partner');
        $partner = $this->model['partner']->getRow(array('company_id' => $company_id, 'company_branch_id' => $company_branch_id, 'partner_type_id' => $row['partner_type_id'], 'partner_id' => $row['partner_id']));
        //d($partner, true);

        $this->data['document_date'] = $row['document_date'];
        $this->data['document_no'] = $row['document_identity'];
        $this->data['partner_name'] = $partner['name'];
        $this->data['phone_no'] = $partner['phone'];
        $this->data['address'] = $partner['address'];

        $this->model['sale_order_detail'] = $this->load->model('inventory/sale_order_detail');
        $details = $this->model['sale_order_detail']->getRows(array('sale_order_id' => $sale_order_id));
        foreach($details as $row_no => $detail) {
            $this->data['details'][$row_no] = $detail;
        }
        //d($row,$detail,true);
        $this->template = 'inventory/sale_order_print.tpl';
        $contents = $this->render();
        //d($contents,true);

        try
        {
            // init HTML2PDF
            $html2pdf = new HTML2PDF('L', 'A5', 'en', true, 'UTF-8', array(0, 0, 0, 0));

            // display the full page
            $html2pdf->pdf->SetDisplayMode('fullpage');

            // convert
            $html2pdf->writeHTML($contents);

            // send the PDF
            $html2pdf->Output('Sale Order.pdf');
        }
        catch(HTML2PDF_exception $e) {
            echo $e;
            exit;
        }

    }




}
?>