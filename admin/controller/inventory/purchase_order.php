<?php

class ControllerInventoryPurchaseOrder extends HController {
    protected $document_type_id = 4;

    protected function getAlias() {
        return 'inventory/purchase_order';
    }

    protected function getPrimaryKey() {
        return 'purchase_order_id';
    }

    protected function getList() {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }

    public function getAjaxLists() {
        $lang = $this->load->language('inventory/purchase_order');
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        $data = array();
        $aColumns = array('action', 'document_date','document_identity', 'partner_name', 'net_amount','check_box');

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
                $strAction .= '<a '.(isset($action['btn_class'])?'class="'.$action['btn_class'].'"':'').' '.(isset($action['target'])?'target="'.$action['target'].'"':'').' '.' href="' . $action['href'] . '" data-toggle="tooltip" title="' . $action['text'] . '" ' . (isset($action['click']) ? 'onClick="' . $action['click'] . '"' : '') . '>';
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
                } elseif ($aColumns[$i] == 'check_box') {
                    if($aRow['is_post']==0) {
                        $row[] = '<input type="checkbox" name="selected[]" value="' . $aRow[$this->getPrimaryKey()] . '" />';    
                    }
                    else
                    {
                        $row[] = '';
                    }   
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
        $this->data['products'] = $this->model['product']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['supplier'] = $this->load->model('setup/supplier');
        $this->data['suppliers'] = $this->model['supplier']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['currency'] = $this->load->model('setup/currency');
        $this->data['currencys'] = $this->model['currency']->getRows();

        $this->model['unit'] = $this->load->model('inventory/unit');
        $this->data['units'] = $this->model['unit']->getRows(array('company_id' => $this->session->data['company_id']));
        $arrUnits = $this->model['unit']->getArrays('unit_id','name',array('company_id' => $this->session->data['company_id']));

        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->data['warehouses'] = $this->model['warehouse']->getRows(array('company_id' => $this->session->data['company_id'], 'company_branch_id' => $this->session->data['company_branch_id']));
        $this->data['arrWarehouses'] = json_encode($this->data['warehouses']);

        $this->data['base_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['base_currency'] = $this->session->data['base_currency_name'];
        $this->data['document_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['conversion_rate'] = "1.00";

        $this->data['partner_types'] = $this->session->data['partner_types'];
        $this->data['partner_type_id'] = 1;

        $this->data['document_date'] = stdDate();
        if (isset($this->request->get['purchase_order_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $this->data['isEdit'] = 1;
            $result = $this->model[$this->getAlias()]->getRow(array($this->getPrimaryKey() => $this->request->get[$this->getPrimaryKey()]));
            foreach ($result as $field => $value) {
                if ($field == 'document_date') {
                    $this->data[$field] = stdDate($value);
                } else {
                    $this->data[$field] = $value;
                }
            }

            $this->model['purchase_order_detail'] = $this->load->model('inventory/purchase_order_detail');
            $rows = $this->model['purchase_order_detail']->getRows(array('purchase_order_id' => $this->request->get['purchase_order_id']),array('sort_order desc'));
            foreach($rows as $row_no => $row) {
                $this->data['purchase_order_details'][$row_no] = $row;
                $this->data['purchase_order_details'][$row_no]['unit'] = $arrUnits[$row['unit_id']];
            }

        }

        $this->data['href_get_product_json'] = $this->url->link($this->getAlias() . '/getProductJson', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_post'] = $this->url->link($this->getAlias() . '/post', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_print'] = $this->url->link($this->getAlias() . '/printDocument', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');

        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
        $this->data['strValidation'] = "{
            'rules': {
                'document_date': {'required': true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},
                'partner_id': {'required': true},
                'net_amount': {'required': true},
            },
            messages: {
            document_date:{
                remote: 'Invalid Date'
            }}
        }";

        $this->response->setOutput($this->render());
    }

    public function getProductJson() {
        $search = $this->request->post['q'];
        $page = $this->request->post['page'];

        $this->model['product'] = $this->load->model('inventory/product');
        $rows = $this->model['product']->getProductJson($search, $page);

        echo json_encode($rows);
    }

    protected function insertData($data) {
        //d($data,true);
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

        $purchase_order_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);

        $this->model['document'] = $this->load->model('common/document');
        $insert_document = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'document_type_id' => $this->document_type_id,
            'document_id' => $purchase_order_id,
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

        $this->model['purchase_order_detail'] = $this->load->model('inventory/purchase_order_detail');
        foreach ($data['purchase_order_details'] as $sort_order => $detail) {
            $detail['purchase_order_id'] = $purchase_order_id;
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['document_currency_id'] = $data['document_currency_id'];
            $detail['base_currency_id'] = $data['base_currency_id'];
            $detail['sort_order'] = $sort_order;
            // d($detail,true);
            $purchase_order_detail_id=$this->model['purchase_order_detail']->add($this->getAlias(), $detail);
        }

        return $purchase_order_id;
    }

    protected function insertRedirect($id, $data) {
        $url = $this->getURL();
        $this->redirect($this->url->link($this->getAlias().'/update', 'token=' . $this->session->data['token'] . '&purchase_order_id=' . $id, 'SSL'));
    }

    protected function updateData($primary_key, $data) {
        $purchase_order_id = $primary_key;
        $data['document_date'] = MySqlDate($data['document_date']);
        $this->model['purchase_order'] = $this->load->model('inventory/purchase_order');
        $this->model['purchase_order_detail'] = $this->load->model('inventory/purchase_order_detail');

        $this->model['purchase_order']->edit($this->getAlias(), $primary_key, $data);
        $this->model['purchase_order_detail']->deleteBulk($this->getAlias(), array('purchase_order_id' => $purchase_order_id));

        $this->model['document'] = $this->load->model('common/document');
        $this->model['document']->deleteBulk($this->getAlias(), array('document_id' => $purchase_order_id));
        $insert_document = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'document_type_id' => $this->document_type_id,
            'document_id' => $purchase_order_id,
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

        foreach ($data['purchase_order_details'] as $sort_order => $detail) {
            $detail['purchase_order_id'] = $purchase_order_id;
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['sort_order'] = $sort_order;
            $purchase_order_detail_id=$this->model['purchase_order_detail']->add($this->getAlias(), $detail);
        }

        return $purchase_order_id;
    }

    protected function updateRedirect($id, $data) {
        $url = $this->getURL();
        $this->redirect($this->url->link($this->getAlias().'/update', 'token=' . $this->session->data['token'] . '&purchase_order_id=' . $id, 'SSL'));
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
        $this->model['purchase_order_detail'] = $this->load->model('inventory/purchase_order_detail');
        $this->model['purchase_order_detail']->deleteBulk($this->getAlias(),array('purchase_order_id' => $primary_key));

        $this->model['document'] = $this->load->model('common/document');
        $this->model['document']->deleteBulk($this->getAlias(), array('document_id' => $primary_key));

        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['ledger']->deleteBulk($this->getAlias(), array('document_id' => $primary_key));

        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);
    }

//    public function getPurchaseOrder() {
//        if($this->request->server['REQUEST_METHOD'] == 'POST') {
//
//            $document_id = $this->request->post['ref_document_id'];
//            $supplier_id = $this->request->post['supplier_id'];
//            $purchase_order_id = $this->request->get['purchase_order_id'];
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
//            $this->model['purchase_order'] = $this->load->model('inventory/purchase_order');
//
//            $rows = $this->model['purchase_order']->getPurchaseOrder($document_id,$supplier_id);
//
//            $this->model['purchase_order'] = $this->load->model('inventory/purchase_order');
//            $html = '';
//            foreach($rows as $grid_row => $row) {
//                $order_qty = $this->model['purchase_order']->getOrderQty($document_id, $row['product_id']);
//                $received_qty = $this->model['purchase_order']->getUtilizedProduct($document_id, $row['product_id'], $purchase_order_id);
//                $html .= '<tbody id="grid_row_' . $grid_row . '" row_id="'. $grid_row .'">';
//                $html .='<tr>';
//                $html .='<td><input  type="text" name="purchase_order_details['. $grid_row .'][product_code]" id="purchase_order_detail_code_'. $grid_row .'" class="" value="'.$row['product_code'].'" readonly="true" /></td>';
//                $html .='<td>';
////                $html .='<div class="form-group input-group">';
//                $html .='<input  type="hidden" name="purchase_order_details['. $grid_row .'][product_id]" id="purchase_order_detail_id_'. $grid_row .'" class="" value="'.$row['product_id'].'" />';
//                $html .='<select class="" id="purchase_order_detail_product_id_' . $grid_row . '" name="purchase_order_details[' . $grid_row . '][product_id]" disabled="true">';
//                $html .='<option value=""></option>';
//                foreach($products as $product) {
//                    $html .='<option value="' . $product['product_id'] . '" '.($product['product_id'] == $row['product_id']?'selected="selected"':'').' >' . $product['name'] .'</option>';
//                }
//                $html .='</select>';
////                $html .='<span class="input-group-btn ">';
////                $html .='<button type="button"  model="setup/product" ref_id="purchase_order_detail_product_id_' . $grid_row . '" callback="getProductInformation"  value="..." class="QSearch btn btn-default" ><i class="fa fa-search"></i></button>';
////                $html .='</span>';
////                $html .='</div>';
//                $html .='</td>';
//                $html .='<td><select class="" name="purchase_order_details['. $grid_row .'][warehouse_id]">';
//                $html .='<option value=""></option>';
//                foreach($warehouses as $warehouse) {
//                    $html .='<option value="' . $warehouse['warehouse_id'] .'" '.($warehouse['warehouse_id'] == $row['warehouse_id']?'selected="selected"':'').' >' . $warehouse['name'] . '</option>';
//                }
//                $html .='</select></td>';
//                $html .='<td>';
//                $html .='<input type="hidden" class="fDecimal" id="purchase_order_detail_order_qty_'. $grid_row .'" name="purchase_order_details['. $grid_row .'][order_qty]" value="'.$order_qty.'" />';
//                $html .='<input type="hidden" class="fDecimal" id="purchase_order_detail_received_qty_'. $grid_row .'" name="purchase_order_details['. $grid_row .'][received_qty]" value="'.$received_qty.'" />';
//                $html .='<input type="text" class="fDecimal" id="purchase_order_detail_qty_'. $grid_row .'" name="purchase_order_details['. $grid_row .'][qty]" value="'.($order_qty-$received_qty).'" onchange="calcRowTotal('. $grid_row .',\'qty\')" title="OQ:'.$order_qty.',RQ:'.$received_qty.',BQ:'.($order_qty-$received_qty).'" />';
//                $html .='</td>';
//                $html .='<td>';
//                $html .='<input  type="hidden" name="purchase_order_details['. $grid_row .'][unit_id]" id="purchase_order_detail_unit_id_'. $grid_row .'" class="" value="'.$row['unit_id'].'" />';
//                $html .='<select class="" id="purchase_order_detail_unit_id_' . $grid_row . '" name="purchase_order_details['. $grid_row .'][unit_id]" disabled="true">';
//                $html .='<option value=""></option>';
//                foreach($units as $unit) {
//                    $html .='<option value="' . $unit['unit_id'] . '" '.($unit['unit_id'] == $row['unit_id']?'selected="selected"':'').' >' . $unit['name'] . '</option>';
//                }
//                $html .='</select></td>';
//                $html .='<td><input type="text" class="fDecimal" id="purchase_order_detail_rate_'. $grid_row .'" name="purchase_order_details['. $grid_row .'][rate]" value="'.$row['rate'].'" onchange="calcRowTotal('. $grid_row .',\'rate\')" readonly="true" /></td>';
//                $html .='<td>';
//                $html .='<input  type="hidden" name="purchase_order_details['. $grid_row .'][document_currency_id]" id="purchase_order_detail_document_currency_id_'. $grid_row .'" class="" value="'.$row['document_currency_id'].'" />';
//                $html .= '<select class="" id="purchase_order_detail_document_currency_id_'. $grid_row .'" name="purchase_order_details['. $grid_row .'][document_currency_id]" onchange="getCurrencyRate('. $grid_row .');" disabled="true">';
//                $html .='<option value=""></option>';
//                foreach($currencys as $currency_id => $value) {
//                    $html .='<option value="' . $currency_id . '" '.($currency_id == $row['document_currency_id']?'selected="selected"':'').'>' . $value . '</option>';
//                }
//                $html .='</select></td>';
//                $html .='<td><input type="text" class="fDecimal" id="purchase_order_detail_conversion_rate_'. $grid_row .'" name="purchase_order_details['. $grid_row .'][conversion_rate]" value="'.$row['conversion_rate'].'" onchange="calcRowTotal('. $grid_row .',\'conversion_rate\')" readonly="true" /></td>';
//                $html .='<td><input type="text" class="fDecimal" id="purchase_order_detail_amount_'. $grid_row .'" name="purchase_order_details['. $grid_row .'][amount]" value="'.$row['amount'].'" readonly="readonly" /></td>';
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

    public function ajaxValidateForm() {
        $post  = $this->request->post;
        $lang = $this->load->language('inventory/purchase_order');
        $error = array();

        if($post['voucher_date'] == '') {
            $error[] = $lang['error_voucher_date'];
        }

        if($post['supplier_id'] == '') {
            $error[] = $lang['error_supplier'];
        }

        $details = $post['purchase_order_details'];
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

        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        $lang = $this->load->language($this->getAlias());
        $post = $this->request->post;
        $session = $this->session->data;
        $purchase_order_id = $this->request->get['purchase_order_id'];

        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['purchase_order'] = $this->load->model('inventory/purchase_order');
        $this->model['purchase_order_detail'] = $this->load->model('inventory/purchase_order_detail');
        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $branch = $this->model['company_branch']->getRow(array('company_branch_id' => $session['company_branch_id']));
        $voucher = $this->model['purchase_order']->getRow(array('purchase_order_id' => $purchase_order_id));
        // d($voucher,true);
        $details = $this->model['purchase_order_detail']->getRows(array('purchase_order_id' => $purchase_order_id),array('sort_order'));
        $arrCoas = $this->model['purchase_order']->getArrays('purchase_order_id','level3_display_name', array('company_id' => $this->session->data['company_id']));

        $this->model['setting'] = $this->load->model('common/setting');
        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'general',
            'field' => 'company_logo',
        ));
        $company_logo = $setting['value'];

        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'general',
            'field' => 'company_header_print',
        ));
        $company_header_print = $setting['value'];

        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'general',
            'field' => 'company_footer_print',
        ));
        $company_footer_print = $setting['value'];

        $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Purchase Order');
        $pdf->SetSubject('Purchase Order');

        //Set Header
        $pdf->data = array(
            'company_name' => $branch['company_name'],
            'report_name' => 'Purchase Order',
            'company_logo' => $company_logo,
            'remarks' => $voucher['remarks'],
            'document_identity' => $voucher['document_identity'],
            'manual_ref_no' => $voucher['manual_ref_no'],
            'document_date' => $voucher['document_date'],
            'partner_name' => $voucher['partner_name'],
            'terms' => $voucher['terms'],
            'remarks' => $voucher['remarks'],
            'company_header_print' => $company_header_print,
            'company_footer_print' => $company_footer_print,

        );
        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10, 60, 10);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 60);
        // add a page
        $pdf->AddPage();

        // set font
        $pdf->SetFont('Helvetica', '', 8);

        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        $total_amount = 0;

        foreach($details as $detail) {

            $productName = $detail['product_name'];

            if(multili_var_length_check(array($productName), 85)) {
                $pdf->Cell(22, 7, $detail['product_code'], 1, false, 'C', 0, '', 1);
                $pdf->Cell(90, 7, html_entity_decode($detail['product_name']), 1, false, 'L', 0, '', 1);
                $pdf->Cell(15, 7, $detail['unit'], 1, false, 'C', 0, '', 1);
                $pdf->Cell(19, 7, number_format($detail['qty'],2), 1, false, 'R', 0, '', 1);
                $pdf->Cell(19, 7, number_format($detail['rate'],4), 1, false, 'R', 0, '', 1);
                $pdf->Cell(24, 7, number_format($detail['amount'],2), 'L,R', false, 'R', 0, '', 1);
                $pdf->ln(7);
            } else {


                $arrProduct = splitString($productName, 85);
                $length = max_array_index_count($arrProduct);

                for($index=0; $index <= ($length-1); $index++){
                    if($index==0){
                        $pdf->Cell(22, 4, $detail['product_code'], 'TLR', false, 'C', 0, '', 1);
                        $pdf->Cell(90, 4, html_entity_decode($arrProduct[$index]), 'TLR', false, 'L', 0, '', 1);
                        $pdf->Cell(15, 4, $detail['unit'], 'TLR', false, 'C', 0, '', 1);
                        $pdf->Cell(19, 4, number_format($detail['qty'],2), 'TLR', false, 'R', 0, '', 1);
                        $pdf->Cell(19, 4, number_format($detail['rate'],4), 'TLR', false, 'R', 0, '', 1);
                        $pdf->Cell(24, 4, number_format($detail['amount'],2), 'TLR', false, 'R', 0, '', 1);
                    } else if($index<($length-1)){
                        $pdf->Cell(22, 4, '', 'LR', false, 'C', 0, '', 1);
                        $pdf->Cell(90, 4, html_entity_decode($arrProduct[$index]), 'LR', false, 'L', 0, '', 1);
                        $pdf->Cell(15, 4, '', 'LR', false, 'C', 0, '', 1);
                        $pdf->Cell(19, 4, '', 'LR', false, 'R', 0, '', 1);
                        $pdf->Cell(19, 4, '', 'LR', false, 'R', 0, '', 1);
                        $pdf->Cell(24, 4, '', 'LR', false, 'R', 0, '', 1);
                    } else {
                        $pdf->Cell(22, 4, '', 'LRB', false, 'C', 0, '', 1);
                        $pdf->Cell(90, 4, html_entity_decode($arrProduct[$index]), 'LRB', false, 'L', 0, '', 1);
                        $pdf->Cell(15, 4, '', 'LRB', false, 'C', 0, '', 1);
                        $pdf->Cell(19, 4, '', 'LRB', false, 'R', 0, '', 1);
                        $pdf->Cell(19, 4, '', 'LRB', false, 'R', 0, '', 1);
                        $pdf->Cell(24, 4, '', 'LRB', false, 'R', 0, '', 1);
                    }
                    $pdf->ln(4);
                }

            }

            $total_amount += $detail['amount'];
        
        }

        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->setXY($x,$y);

        $pdf->Ln(-1);

        for ($i = $y; $i <= 200; $i++) {

            $pdf->Ln(1);
            $pdf->Cell(22, 8,'', 'L', false, 'C', 0, '', 1);
            $pdf->Cell(90, 8, '', 'L', false, 'L', 0, '', 1);
            $pdf->Cell(15, 8, '', 'L', false, 'R', 0, '', 1);
            $pdf->Cell(19, 8, '', 'L', false, 'C', 0, '', 1);
            $pdf->Cell(19, 8, '', 'L', false, 'R', 0, '', 1);
            $pdf->Cell(24, 8, '', 'L,R', false, 'R', 0, '', 1);
            $y =$i;
        }

        $pdf->Ln(-1);
        $pdf->Ln(5);

        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->setXY($x,$y);
        
        $pdf->Cell(189, 8, '', 'B', false, 'C', 0, '', 0, false, 'M', 'M');

        $pdf->ln(4);
        $pdf->Cell(146, 6, 'IN WORD: ' . Number2Words(round($total_amount,2)). ' only', 'L,B', false, 'L');
        $pdf->Cell(19, 6, 'Total Amount :' , 'L,B', false, 'L');
        $pdf->Cell(24, 6, number_format($voucher['net_amount'],2), 'R,B', false, 'R');
        $pdf->ln(6);
        $pdf->Cell(15, 5, 'Terms : ', 0, false, 'L', 0, '', 1);
        $pdf->MultiCell(100, 5, $voucher['terms'], 0, 'L', 1, 1, '' ,'', true);

        //Close and output PDF document
        $pdf->Output('Purchase Order - '.$voucher['document_identity'].'.pdf', 'I');

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
        //     $this->Image($image_file, 15, 5, 25, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // }

        if($this->data['company_header_print'] != '') {
            $image_file = DIR_IMAGE.$this->data['company_header_print'];
            // d($image_file, true);
            // $this->Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);
            $this->Image($image_file, 5, 5, 200, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }else
        {
            // Set font
            $this->SetFont('helvetica', 'B', 20);
            $this->Ln(2);
            //Title
            $this->Cell(0, 10, $this->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        }

        // Set font
        // $this->SetFont('helvetica', 'B', 20);
        // $this->Ln(2);
        // // Title
        // $this->Cell(0, 10, $this->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(20);
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 10, $this->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->Ln(10);

        $this->SetFont('Helvetica', '', 9);
        $this->Cell(25, 7, 'Voucher No: ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(90, 7, $this->data['document_identity'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(30, 7, 'Voucher Date: ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(20, 7, stdDate($this->data['document_date']), 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->ln(5);
        $this->Cell(25, 7, 'Supplier Name : ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(90, 7, html_entity_decode($this->data['partner_name']), 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(30, 7, 'Payment Terms : ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(40, 7, $this->data['terms'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->ln(5);
        $this->Cell(25, 7, 'Remarks : ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(90, 7, $this->data['remarks'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
         $this->Cell(30, 7, 'Reference No: ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(40, 7, $this->data['manual_ref_no'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->ln(7);

        $this->SetFont('Helvetica', '', 9);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(22, 8, 'Product Code.', 1, false, 'C', 1, '', 1);
        $this->Cell(90, 8, 'Product Name', 1, false, 'C', 1, '', 1);
        $this->Cell(15, 8, 'Unit', 1, false, 'C', 1, '', 1);
        $this->Cell(19, 8, 'Quantity', 1, false, 'C', 1, '', 1);
        $this->Cell(19, 8, 'Net Rate', 1, false, 'C', 1, '', 1);
        $this->Cell(24, 8, 'Amount', 1, false, 'C', 1, '', 1);
//        $this->ln(20);

    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        
        $this->SetY(-25);
        $y = $this->GetY();

        if($this->data['company_footer_print'] != '') {
            $image_file = DIR_IMAGE.$this->data['company_footer_print'];
            // $this->Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);
            $this->Image($image_file, 5, ($y-10), 200, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        // Set font
        $this->SetFont('helvetica', '', 8);
        // Page number
        $this->Ln(-8);
        $this->Cell(10, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(30, 7, 'Prepared By', 'T', false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(40, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(30, 7, 'Production Manager',  'T', false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(50, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(30, 7, 'Finance Manager',  'T', false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);
        $this->SetFont('helvetica', '', 7);

      //  $this->Cell(0, 5, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

    }

}
