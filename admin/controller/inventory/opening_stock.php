<?php

class ControllerInventoryOpeningStock extends HController {
    protected $document_type_id = 6;

    protected function getAlias() {
        return 'inventory/opening_stock';
    }

    protected function getPrimaryKey() {
        return 'opening_stock_id';
    }

    protected function getList() {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }

    public function getAjaxLists() {

        $lang = $this->load->language('inventory/opening_stock');
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        $data = array();
        $aColumns = array('action','document_date', 'document_identity', 'warehouse', 'remarks', 'created_at','check_box');

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
                $strAction .= '<a target="_blank" '.(isset($action['btn_class'])?'class="'.$action['btn_class'].'"':'').' href="' . $action['href'] . '" data-toggle="tooltip" title="' . $action['text'] . '" ' . (isset($action['click']) ? 'onClick="' . $action['click'] . '"' : '') . '>';
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
                } elseif ($aColumns[$i] == 'document_date') {
                    $row[] = stdDate($aRow['document_date']);
                } elseif ($aColumns[$i] == 'check_box') {
                    if($aRow['is_post']==0) {
                        $row[] = '<input type="checkbox" name="selected[]" value="' . $aRow[$this->getPrimaryKey()] . '" />';
                    } else {
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

//        $this->model['product'] = $this->load->model('inventory/product');
//        $this->data['products'] = $this->model['product']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['currency'] = $this->load->model('setup/currency');
        $this->data['currencys'] = $this->model['currency']->getRows();

        $this->model['unit'] = $this->load->model('inventory/unit');
        $this->data['units'] = $this->model['unit']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->data['warehouses'] = $this->model['warehouse']->getRows(array('company_id' => $this->session->data['company_id'], 'company_branch_id' => $this->session->data['company_branch_id']));
        $this->data['arrWarehouses'] = json_encode($this->data['warehouses']);

        $this->data['base_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['base_currency'] = $this->session->data['base_currency_name'];
        $this->data['document_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['conversion_rate'] = "1.00";
        //d($this->data, true);

        $this->data['partner_types'] = $this->session->data['partner_types'];

        $this->data['document_date'] = stdDate();
        if (isset($this->request->get['opening_stock_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $this->data['isEdit'] = 1;
            $result = $this->model[$this->getAlias()]->getRow(array('opening_stock_id' => $this->request->get[$this->getPrimaryKey()]));
            foreach ($result as $field => $value) {
                if ($field == 'document_date') {
                    $this->data[$field] = stdDate($value);
                } else {
                    $this->data[$field] = $value;
                }
            }
            $this->model['opening_stock_detail'] = $this->load->model('inventory/opening_stock_detail');
            $this->data['opening_stock_details'] = $this->model['opening_stock_detail']->getRows(array('opening_stock_id' => $this->request->get['opening_stock_id']), array('sort_order'));
        }

        $this->data['href_get_product_json'] = $this->url->link($this->getAlias() . '/getProductJson', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['href_get_product_by_code'] = $this->url->link('common/function/getProductByCode', 'token=' . $this->session->data['token']);
        $this->data['href_get_product_by_id'] = $this->url->link('common/function/getProductById', 'token=' . $this->session->data['token']);
        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
        $this->data['strValidation']= "{
            'rules':{
                'document_date': {'required':true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},
                'warehouse_id': {'required':true},
                'conversion_rate': {'required':true},
                'net_amount': {'required':true},
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
        $opening_stock_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);
        $data['document_id'] = $opening_stock_id;

        $this->model['opening_stock'] = $this->load->model('inventory/opening_stock');
        $this->model['opening_stock_detail'] = $this->load->model('inventory/opening_stock_detail');
        $this->model['document'] = $this->load->model('common/document');
        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');
        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['product'] = $this->load->model('inventory/product');
        $this->model['setting'] = $this->load->model('common/setting');



       $row = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'general',
            'field' => 'suspense_account_id',
        ));

      //  d($row,true);
        $suspense_account_id = $row['value'];

        $insert_document = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'document_type_id' => $this->document_type_id,
            'document_id' => $opening_stock_id,
            'document_identity' => $data['document_identity'],
            'document_date' => $data['document_date'],
            'partner_type_id' => '',
            'partner_id' => '',
            'document_currency_id' => $data['document_currency_id'],
            'document_amount' => $data['net_amount'],
            'conversion_rate' => $data['conversion_rate'],
            'base_currency_id' => $data['base_currency_id'],
            'base_amount' => $data['base_amount'],
        );
        $document_id = $this->model['document']->add($this->getAlias(), $insert_document);



        $gl[] = array(
            'partner_type_id' => '',
            'partner_id' => '',
            'reference_document_type_id' => '',
            'reference_document_identity' => '',
            'coa_id' => $suspense_account_id,
            'remarks' => '',
            'document_currency_id' => $data['document_currency_id'],
            'document_debit' => 0,
            'document_credit' => $data['net_amount'],
            'base_currency_id' => $data['base_currency_id'],
            'conversion_rate' => $data['conversion_rate'],
            'debit' => 0,
            'credit' => $data['base_amount'],
        );

        $sort_order = 0;
        foreach ($data['opening_stock_details'] as $detail) {
            $product = $this->model['product']->getRow(array('product_id' => $detail['product_id']));

            $detail['opening_stock_id'] = $opening_stock_id;
            $detail['sort_order'] = $sort_order+1;
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['document_currency_id'] = $data['document_currency_id'];
            $detail['base_currency_id'] = $data['base_currency_id'];
            $detail['conversion_rate'] = $data['conversion_rate'];
            $detail['base_amount'] = $data['conversion_rate'] * $detail['amount'];
            $opening_stock_detail_id = $this->model['opening_stock_detail']->add($this->getAlias(), $detail);

            $stock_ledger = array(
                'company_id' => $detail['company_id'],
                'company_branch_id' => $detail['company_branch_id'],
                'fiscal_year_id' => $detail['fiscal_year_id'],
                'document_type_id' => $data['document_type_id'],
                'document_id' => $data['document_id'],
                'document_identity' => $data['document_identity'],
                'document_date' => $data['document_date'],
                'document_detail_id' => $opening_stock_detail_id,
                'warehouse_id' => $data['warehouse_id'],
                'container_no' => $detail['container_no'],
                'batch_no' => $detail['batch_no'],
                'product_id' => $detail['product_id'],
                'document_unit_id' => $detail['unit_id'],
                'document_qty' => $detail['qty'],
                'unit_conversion' => 1,
                'base_unit_id' => $detail['unit_id'],
                'base_qty' => $detail['qty'],
                'document_currency_id' => $detail['document_currency_id'],
                'document_rate' => $detail['rate'],
                'document_amount' => $detail['amount'],
                'currency_conversion' => $detail['conversion_rate'],
                'base_currency_id' => $detail['base_currency_id'],
                'base_rate' => ($detail['rate'] * $detail['conversion_rate']),
                'base_amount' => ($detail['amount'] * $detail['conversion_rate']),
            );
            $stock_ledger_id = $this->model['stock_ledger']->add($this->getAlias(), $stock_ledger);

            $gl[] = array(
                'document_detail_id' => $opening_stock_detail_id,
                'partner_type_id' => '',
                'partner_id' => '',
                'reference_document_type_id' => $this->document_type_id,
                'reference_document_identity' => $data['document_identity'],
                'coa_id' => $product['inventory_account_id'],
                'remarks' => '',
                'document_currency_id' => $detail['document_currency_id'],
                'document_debit' => $detail['amount'],
                'document_credit' => 0,
                'base_currency_id' => $detail['base_currency_id'],
                'conversion_rate' => $detail['conversion_rate'],
                'debit' => ($detail['amount'] * $detail['conversion_rate']),
                'credit' => 0,
            );
            $sort_order++;
        }

        if($gl) {
            foreach($gl as $sort_order => $ledger) {
                $ledger['company_id'] = $this->session->data['company_id'];
                $ledger['company_branch_id'] = $this->session->data['company_branch_id'];
                $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
                $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
                $ledger['document_type_id'] = $this->document_type_id;
                $ledger['document_id'] = $data['document_id'];
                $ledger['document_identity'] = $data['document_identity'];
                $ledger['document_date'] = $data['document_date'];
                $ledger['sort_order'] = $sort_order;

                $ledger_id = $this->model['ledger']->add($this->getAlias(), $ledger);
            }
        }

        return $opening_stock_id;
    }

    protected function updateData($primary_key, $data) {
        //d($data, true);
        $data['document_date'] = MySqlDate($data['document_date']);
        $data['base_amount'] = $data['net_amount'] * $data['conversion_rate'];
        $this->model['opening_stock'] = $this->load->model('inventory/opening_stock');
        $this->model['opening_stock_detail'] = $this->load->model('inventory/opening_stock_detail');
        $this->model['document'] = $this->load->model('common/document');
        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');
        $this->model['ledger'] = $this->load->model('gl/ledger');
        // $this->model['mapping_coa'] = $this->load->model('gl/mapping_coa');
        $this->model['product'] = $this->load->model('inventory/product');

        $this->model['setting'] = $this->load->model('common/setting');
        $this->model['document']->deleteBulk($this->getAlias(), array('document_id' => $primary_key));
        $this->model['stock_ledger']->deleteBulk($this->getAlias(), array('document_id' => $primary_key));
        $this->model['opening_stock_detail']->deleteBulk($this->getAlias(), array('opening_stock_id' => $primary_key));
        $this->model['ledger']->deleteBulk($this->getAlias(), array('document_id' => $primary_key));

        $this->model['opening_stock']->edit($this->getAlias(), $primary_key, $data);

        $row = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'general',
            'field' => 'suspense_account_id',
        ));
        $suspense_account_id = $row['value'];

        $insert_document = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'document_type_id' => $this->document_type_id,
            'document_id' => $primary_key,
            'document_identity' => $data['document_identity'],
            'document_date' => $data['document_date'],
            'partner_type_id' => '',
            'partner_id' => '',
            'document_currency_id' => $data['document_currency_id'],
            'document_amount' => $data['net_amount'],
            'conversion_rate' => $data['conversion_rate'],
            'base_currency_id' => $data['base_currency_id'],
            'base_amount' => $data['base_amount'],
        );
        $document_id = $this->model['document']->add($this->getAlias(), $insert_document);

        $gl[] = array(
            'partner_type_id' => '',
            'partner_id' => '',
            'reference_document_type_id' => '',
            'reference_document_identity' => '',
            'coa_id' => $suspense_account_id,
            'remarks' => '',
            'document_currency_id' => $data['document_currency_id'],
            'document_debit' => 0,
            'document_credit' => $data['net_amount'],
            'base_currency_id' => $data['base_currency_id'],
            'conversion_rate' => $data['conversion_rate'],
            'debit' => 0,
            'credit' => $data['base_amount'],
        );

        foreach ($data['opening_stock_details'] as $sort_order => $detail) {
            $product = $this->model['product']->getRow(array('product_id' => $detail['product_id']));

            $detail['opening_stock_id'] = $primary_key;
            $detail['sort_order'] = $sort_order+1;
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['document_currency_id'] = $data['document_currency_id'];
            $detail['base_currency_id'] = $data['base_currency_id'];
            $detail['conversion_rate'] = $data['conversion_rate'];
            $detail['base_amount'] = $data['conversion_rate'] * $detail['amount'];
            $opening_stock_detail_id = $this->model['opening_stock_detail']->add($this->getAlias(), $detail);

            $stock_ledger = array(
                'company_id' => $detail['company_id'],
                'company_branch_id' => $detail['company_branch_id'],
                'fiscal_year_id' => $detail['fiscal_year_id'],
                'document_type_id' => $data['document_type_id'],
                'document_id' => $data['document_id'],
                'document_identity' => $data['document_identity'],
                'document_date' => $data['document_date'],
                'document_detail_id' => $opening_stock_detail_id,
                'warehouse_id' => $data['warehouse_id'],
                'container_no' => $detail['container_no'],
                'batch_no' => $detail['batch_no'],
                'product_id' => $detail['product_id'],
                'document_unit_id' => $detail['unit_id'],
                'document_qty' => $detail['qty'],
                'unit_conversion' => 1,
                'base_unit_id' => $detail['unit_id'],
                'base_qty' => $detail['qty'],
                'document_currency_id' => $detail['document_currency_id'],
                'document_rate' => $detail['rate'],
                'document_amount' => $detail['amount'],
                'currency_conversion' => $detail['conversion_rate'],
                'base_currency_id' => $detail['base_currency_id'],
                'base_rate' => ($detail['rate'] * $detail['conversion_rate']),
                'base_amount' => ($detail['amount'] * $detail['conversion_rate']),
            );
            $stock_ledger_id = $this->model['stock_ledger']->add($this->getAlias(), $stock_ledger);
            //d(array($stock_ledger_id, $stock_ledger),true);

            $gl[] = array(
                'document_detail_id' => $opening_stock_detail_id,
                'partner_type_id' => '',
                'partner_id' => '',
                'reference_document_type_id' => $this->document_type_id,
                'reference_document_identity' => $data['document_identity'],
                'coa_id' => $product['inventory_account_id'],
                'remarks' => '',
                'document_currency_id' => $detail['document_currency_id'],
                'document_debit' => $detail['amount'],
                'document_credit' => 0,
                'base_currency_id' => $detail['base_currency_id'],
                'conversion_rate' => $detail['conversion_rate'],
                'debit' => ($detail['amount'] * $detail['conversion_rate']),
                'credit' => 0,
            );
        }

        if($gl) {
            foreach($gl as $sort_order => $ledger) {
                $ledger['company_id'] = $this->session->data['company_id'];
                $ledger['company_branch_id'] = $this->session->data['company_branch_id'];
                $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
                $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
                $ledger['document_type_id'] = $this->document_type_id;
                $ledger['document_id'] = $data['document_id'];
                $ledger['document_identity'] = $data['document_identity'];
                $ledger['document_date'] = $data['document_date'];
                $ledger['sort_order'] = $sort_order;

                $ledger_id = $this->model['ledger']->add($this->getAlias(), $ledger);
            }
        }

        return $primary_key;
    }

    protected function deleteData($primary_key) {
        $this->model['opening_stock'] = $this->load->model('inventory/opening_stock');
        $this->model['opening_stock_detail'] = $this->load->model('inventory/opening_stock_detail');
        $this->model['document'] = $this->load->model('common/document');
        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');

        $this->model['opening_stock_detail']->deleteBulk($this->getAlias(), array('opening_stock_id' => $primary_key));
        $this->model['document']->deleteBulk($this->getAlias(), array('document_id' => $primary_key));
        $this->model['stock_ledger']->deleteBulk($this->getAlias(), array('document_id' => $primary_key));
        $this->model['opening_stock']->delete($this->getAlias(), $primary_key);
    }

    public function getProductJson() {
        $search = $this->request->post['q'];
        $page = $this->request->post['page'];

        $this->model['product'] = $this->load->model('inventory/product');
        $rows = $this->model['product']->getProductJson($search, $page);
        echo json_encode($rows);
    }


    public function printDocument() {

        $post = $this->request->post;
        $lang = $this->load->language($this->getAlias());

        $opening_stock_id = $this->request->get['opening_stock_id'];
        $session = $this->session->data;
        $this->model['company'] = $this->load->model('setup/company');
        $this->model['opening_stock'] = $this->load->model('inventory/opening_stock');
        $this->model['opening_stock_detail'] = $this->load->model('inventory/opening_stock_detail');
        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $company_id = $this->session->data['company_id'];

        $opening_stock =  $this->model['opening_stock']->getRow(array('opening_stock_id'=>$opening_stock_id));
        $opening_stock_details = $this->model['opening_stock_detail']->getRows(array('opening_stock_id'=>$opening_stock_id));
        $branch = $this->model['company_branch']->getRow(array('company_branch_id' => $session['company_branch_id']));
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


      $company = $this->model['company']->getRow(array('company_id' => $company_id));
      $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Muhammad Salman');
        $pdf->SetTitle('Opening Stock');
        $pdf->SetSubject('Opening Stock');

        //Set Header

        $pdf->data = array(
            'company_name' => $branch['name'],
             'report_name' => 'Opening Stock',
             'company_header_print' => $company_header_print,
             'company_footer_print' => $company_footer_print,
        );



        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(7, 2, 7);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(2);

        // set auto page breaks
        //$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set font
        $pdf->SetFont('helvetica', '', 10);

        // add a page
        $pdf->AddPage();

        $pdf->SetTextColor(0,0,0);

        $pdf->Ln(30);
        $pdf->Cell(20,7,'Invoice No : ',0,false,'L',0,'',0,false,'M','M');
        $pdf->Cell(20,7,$opening_stock['document_identity'],0,false,'L',0,'',0,false,'M','M');

        $pdf->Cell(120,7,'Invoice Date : ',0,false,'R',0,'',0,false,'M','M');
        $pdf->Cell(20,7,$opening_stock['document_date'],0,false,'L',0,'',0,false,'M','M');

        $pdf->Ln(8);
        $pdf->Cell(30,7,'Remarks : ',0,false,'L',0,'',0,false,'M','M');
        $pdf->Cell(75,7,$opening_stock['remarks'],0,false,'L',0,'',0,false,'M','M');

        $pdf->Ln(12);

        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(25, 8, 'Product Code', 1, false, 'C', 1, '', 1);
        $pdf->Cell(60, 8, 'Product Name', 1, false, 'C', 1, '', 1);
        $pdf->Cell(30, 8, 'Warehouse', 1, false, 'C', 1, '', 1);
        $pdf->Cell(15, 8, 'Qty', 1, false, 'C', 1, '', 1);
        $pdf->Cell(15, 8, 'Unit', 1, false, 'C', 1, '', 1);
        $pdf->Cell(25, 8, 'Rate', 1, false, 'C', 1, '', 1);
        $pdf->Cell(25, 8, 'Amount', 1, false, 'C', 1, '', 1);

        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);

        $total_amount = 0;
        $total_discount = 0;
        $total_tax = 0;
        $total_net_amount = 0;

        // d($opening_stock_details, true);
        foreach($opening_stock_details as $details){
            $pdf->Ln(8);
            $pdf->Cell(25, 8, $details['product_code'], 1, false, 'L', 1, '', 1);
            $pdf->Cell(60, 8, $details['product'], 1, false, 'L', 1, '', 1);
            $pdf->Cell(30, 8, $details['warehouse'], 1, false, 'C', 1, '', 1);
            $pdf->Cell(15, 8, $details['qty'], 1, false, 'C', 1, '', 1);
            $pdf->Cell(15, 8, $details['unit'], 1, false, 'C', 1, '', 1);
            $pdf->Cell(25, 8, $details['rate'], 1, false, 'C', 1, '', 1);
            $pdf->Cell(25, 8, $details['amount'], 1, false, 'C', 1, '', 1);

            $total_amount += $details['total_price_pkr'];
            // $total_discount += $details['discount_amount'];
            $total_tax += ($details['sales_tax'] + $details['additional_sales_tax'] + $details['income_tax']); // by taikhum
            $total_net_amount += ($details['total_price_pkr'] + ($details['sales_tax'] + $details['additional_sales_tax'] + $details['income_tax']));
        }

        // $x = $pdf->GetX();
        // $y = $pdf->GetY();
        // for ($i = $y; $i <= 200; $i++) {

        //     $pdf->Ln(1);
        //     $pdf->Cell(25, 8,'', 'L', false, 'C', 0, '', 1);
        //     $pdf->Cell(60, 8,'', 'L', false, 'C', 0, '', 1);
        //     $pdf->Cell(30, 8, '', 'L', false, 'L', 0, '', 1);
        //     $pdf->Cell(15, 8, '', 'L', false, 'R', 0, '', 1);
        //     $pdf->Cell(15, 8, '', 'L', false, 'C', 0, '', 1);
        //     $pdf->Cell(25, 8, '', 'L', false, 'R', 0, '', 1);
        //     $pdf->Cell(25, 8, '', 'L,R', false, 'R', 0, '', 1);
        //     $y =$i;
        // }
        // $pdf->Ln(-1);
        // $pdf->Ln(5);
        // $pdf->Cell(190, 8, '', 'B', false, 'C', 0, '', 0, false, 'M', 'M');
        // $pdf->setXY($x,$y);


        // $pdf->SetFillColor(255, 255, 255);
        // $pdf->SetTextColor(0, 0, 0);

        // $pdf->SetFont('helvetica', '', 9);
        // $pdf->ln(9);
        // // $pdf->Cell(130, 7, '', 0, false, 'L');
        // $pdf->Cell(160, 7, $lang['total_amount'].': ', 0, false, 'R');
        // $pdf->Cell(30, 7, number_format($total_amount,2), 0, false, 'R');

        // $pdf->ln(7);

        // // // $pdf->Cell(130, 7, '', 0, false, 'L');
        // // $pdf->Cell(160, 7, $lang['discount_amount'].': ', 0, false, 'R');
        // // $pdf->Cell(30, 7, number_format($total_discount,2), 0, false, 'R');

        // // $pdf->ln(7);


        // // $pdf->Cell(130, 7, '', 0, false, 'L');
        // $pdf->Cell(160, 7, $lang['tax_amount'].': ', 0, false, 'R');
        // $pdf->Cell(30, 7, number_format($total_tax,2), 0, false, 'R');

        // $pdf->ln(7);


        // // $pdf->Cell(130, 7, '', 0, false, 'L');
        // $pdf->Cell(160, 7, $lang['net_amount'].': ', 0, false, 'R');
        // $pdf->Cell(30, 7, number_format($total_net_amount,2), 0, false, 'R');

        // $pdf->ln(7);
        // $pdf->Cell(190, 7, 'Amount In words: ' . Number2Words(round($total_net_amount,2)). ' only', 0, false, 'L');

       // $data= $Quotation;//Close and output PDF document
        $pdf->Output('Opening Stock :'.date('YmdHis').'.pdf', 'I');


}

}


class PDF extends TCPDF {
    public $data = array();

    //Page header
    public function Header() {
        // Logo
//        if($this->data['company_logo'] != '') {
//            $image_file = DIR_IMAGE.$this->data['company_logo'];
//            //$this->Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);
//            $this->Image($image_file, 10, 5, 90, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);
//        }

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
        // Title
        // $this->Cell(0, 10, $this->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(18);
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 10, $this->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
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
        // $this->SetFont('helvetica', 'I', 8);
        // Page number
        // $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

        // Set font
        $this->SetFont('helvetica', '', 8);
        // Page number
        // $this->Ln(-8);
        // $this->Cell(7, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        // $this->Cell(25, 7, 'Prepared By', 'T', false, 'C', 0, '', 0, false, 'M', 'M');
        // $this->Cell(20, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        // $this->Cell(25, 7, 'Production Manager',  'T', false, 'C', 0, '', 0, false, 'M', 'M');
        // $this->Cell(20, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        // $this->Cell(25, 7, 'Finance Manager',  'T', false, 'C', 0, '', 0, false, 'M', 'M');


        // $this->Ln(-7);
        // $this->Cell(7+25+20+25+20+25+15, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        // $this->Cell(45, 7, 'For '. $this->data['company_name'],  'B', false, 'C', 0, '', 0, false, 'M', 'M');

    }
}
