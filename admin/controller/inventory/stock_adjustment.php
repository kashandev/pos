<?php

class ControllerInventoryStockAdjustment extends HController {
    protected $document_type_id = 18;

    protected function getAlias() {
        return 'inventory/stock_adjustment';
    }

    protected function getPrimaryKey() {
        return 'stock_adjustment_id';
    }

    protected function getList() {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }

    public function getAjaxLists() {
        $lang = $this->load->language('inventory/stock_adjustment');
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        $data = array();
        $aColumns = array('action', 'document_date', 'document_identity', 'warehouse', 'total_qty', 'created_at', 'check_box');

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
                } elseif ($aColumns[$i] == 'document_date') {
                    $row[] = stdDate($aRow['document_date']);
                } elseif ($aColumns[$i] == 'created_at') {
                    $row[] = stdDateTime($aRow['created_at']);
                } elseif ($aColumns[$i] == 'check_box') {
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

        $this->model['image'] = $this->load->model('tool/image');
        $this->data['loader_image'] = (HTTP_SERVER.'/admin/view/img/loading.gif');
        $this->data['form_key'] = date('YmdHis') . mt_rand(1, 999999);

        $this->model['currency'] = $this->load->model('setup/currency');
        $this->data['currencys'] = $this->model['currency']->getRows();

        $this->model['product_category'] = $this->load->model('inventory/product_category');
        $this->data['product_categorys'] = $this->model['product_category']->getRows(array('company_id' => $this->session->data['company_id']),array('name'));


        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->data['warehouses'] = $this->model['warehouse']->getRows(array('company_id' => $this->session->data['company_id'], 'company_branch_id' => $this->session->data['company_branch_id']));
        $this->data['arrWarehouses'] = json_encode($this->data['warehouses']);

        $this->data['base_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['base_currency'] = $this->session->data['base_currency_name'];
        $this->data['document_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['conversion_rate'] = "1.00";

        $this->data['document_date'] = stdDate();
        if (isset($this->request->get['stock_adjustment_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $this->data['isEdit'] = 1;
            $result = $this->model[$this->getAlias()]->getRow(array($this->getPrimaryKey() => $this->request->get[$this->getPrimaryKey()]));
            foreach ($result as $field => $value) {
                if ($field == 'document_date') {
                    $this->data[$field] = stdDate($value);
                } else {
                    $this->data[$field] = $value;
                }
            }

            $this->model['stock_adjustment_detail'] = $this->load->model('inventory/stock_adjustment_detail');
            $rows = $this->model['stock_adjustment_detail']->getRows(array('stock_adjustment_id' => $this->request->get['stock_adjustment_id']), array('sort_order asc'));
            foreach($rows as $row_no => $row) {
                $this->data['stock_adjustment_details'][$row_no] = $row;
            }
        }

        $this->data['href_get_ledger'] = $this->url->link( $this->getAlias() .'/getLedger', 'token=' . $this->session->data['token'],'SSL');
        $this->data['href_add_record_session'] = $this->url->link($this->getAlias() . '/AddRecordSession', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');

        $this->data['href_get_warehouse_stocks'] = $this->url->link($this->getAlias() . '/getWarehouseStockAdjustment', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['href_get_product_json'] = $this->url->link($this->getAlias() . '/getProductJson', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_post'] = $this->url->link($this->getAlias() . '/post', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_print'] = $this->url->link($this->getAlias() . '/printDocument', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');

        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
        $this->data['strValidation'] = "{
                'rules': {
                    'document_date': {'required': true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},
                    'warehouse_id': {'required': true},
                    'total_qty': {'required': true},
                    'total_amount': {'required': true},
                },
                messages: {
                    document_date:{
                    remote: 'Invalid Date'
                }}
            }";

        $this->response->setOutput($this->render());
    }

    public function getLedger() {
        $lang = $this->load->language($this->getAlias());
        $document_type_id = $this->request->post['document_type_id'];
        $document_id = $this->request->post['document_id'];
        $this->model['ledger'] = $this->load->model('gl/ledger');
        $query = $this->model['ledger']->getLedger($document_type_id, $document_id);
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

    public function AddRecordSession()
    {
        $post = $this->request->post;
        $detail_array = array(
            'sort_order'=> $post['sort_order'],
            'product_code'=> $post['product_code'],
            'product_id'=> $post['product_id'],
            'stock_qty' => $post['stock_qty'],
            'hidden_qty'=>$post['hidden_qty'],
            'qty'=>$post['qty'],
            'unit_id'=>$post['unit_id'],
            'hidden_rate'=>$post['hidden_rate'],
            'rate'=>$post['rate'],
            'hidden_amount'=>$post['hidden_amount'],
            'amount'=>$post['amount']
        );

        $this->session->data['detail'][$post['form_key']][$post['sort_order']] = $detail_array;
        $json = array(
            'success'   => true,
            'post' => $post,
            'session_data' => $this->session->data['detail'],
            'detail' => $this->session->data['detail'][$post['form_key']],
        );
        $this->response->setOutput(json_encode($json));
    }


    public function getProductJson() {
        $search = $this->request->post['q'];
        $page = $this->request->post['page'];

        $this->model['product'] = $this->load->model('inventory/product');
        $rows = $this->model['product']->getProductJson($search, $page);

        echo json_encode($rows);
    }

    public function getWarehouseStockAdjustment()
    {
        $post = $this->request->post;
        $product_id = $post['product_id'];
        $warehouse_id = $post['warehouse_id'];
        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');
        $stock = $this->model['stock_ledger']->getWarehouseStockAdjustment($product_id, $warehouse_id);
        $stock['stock_qty']        = (float) $stock['stock_qty'];
        $stock['avg_stock_rate']   = (float) $stock['avg_stock_rate'];
        $stock['stock_amount'] = (float) ($stock['stock_amount']);

        echo json_encode([
            'success' => true,
            'stock' => $stock,
            'post' => $post
        ]);
    }

    protected function insertData($data) {

        $form_key = $data['form_key'];
        $data['stock_adjustment_details'] = $this->session->data['detail'][$form_key];

        $this->model['document_type'] = $this->load->model('common/document_type');
        $document = $this->model['document_type']->getNextDocument($this->document_type_id);

        $data['document_type_id'] = $this->document_type_id;
        $data['document_prefix'] = $document['document_prefix'];
        $data['document_no'] = $document['document_no'];
        $data['document_identity'] = $document['document_identity'];

        $data['company_id'] = $this->session->data['company_id'];
        $data['company_branch_id'] = $this->session->data['company_branch_id'];
        $data['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
        $data['document_date'] = MySqlDate($data['document_date']);
        $data['base_amount'] = $data['total_amount'] * $data['conversion_rate'];

        $stock_adjustment_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);
        $data['document_id'] = $stock_adjustment_id;

        $this->model['stock_adjustment_detail'] = $this->load->model('inventory/stock_adjustment_detail');
        $this->model['document'] = $this->load->model('common/document');
        $this->model['product'] = $this->load->model('inventory/product');
        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');

        $insert_document = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'document_type_id' => $this->document_type_id,
            'document_id' => $data['document_id'],
            'document_identity' => $data['document_identity'],
            'document_date' => $data['document_date'],
            'partner_type_id' => $data['partner_type_id'],
            'partner_id' => $data['partner_id'],
            'document_currency_id' => $data['document_currency_id'],
            'document_amount' => $data['total_amount'],
            'conversion_rate' => $data['conversion_rate'],
            'base_currency_id' => $data['base_currency_id'],
            'base_amount' => $data['base_amount'],
        );
        $document_id = $this->model['document']->add($this->getAlias(), $insert_document);

        $gl_data = array();
        $stock_ledger = array();
        $re_order = 0;
        foreach ($data['stock_adjustment_details'] as $sort_order => $detail) {
            $detail['stock_adjustment_id'] = $stock_adjustment_id;
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['sort_order'] = $sort_order;
            $detail['document_currency_id'] = $data['document_currency_id'];
            $detail['base_currency_id'] = $data['base_currency_id'];
            $detail['conversion_rate'] = $data['conversion_rate'];
            $detail['base_rate'] = ($detail['rate'] * $detail['conversion_rate']);
            $detail['base_amount'] = ($detail['amount'] * $detail['conversion_rate']);
            $stock_adjustment_detail_id=$this->model['stock_adjustment_detail']->add($this->getAlias(), $detail);

            $product = $this->model['product']->getRow(array('product_id' => $detail['product_id']));
            $stock = $this->model['stock_ledger']->getWarehouseStockAdjustment($detail['product_id'], $data['warehouse_id'], $data['document_identity'], $data['document_date']);
            $stock_amount = $stock['stock_amount'];
            $stock_qty = $stock['stock_qty'];
            $stock_rate = floatval(($stock_amount / $stock_qty));
            if( $stock_amount < 0 )
            {
                $gl_data[] = array(
                    'document_detail_id' => $stock_adjustment_detail_id,
                    'coa_id' => $product['inventory_account_id'],
                    'document_credit' => 0,
                    'document_debit' => (-1*$stock_amount),
                    'credit' => 0,
                    'debit' => (-1*$stock_amount * $data['conversion_rate']),
                    'remarks' => $data['remarks'],
                    'product_id' => $detail['product_id'],
                    'qty' => -1*$stock_qty,
                    'document_amount' => (-1*$stock_amount),
                    'amount' => (-1*$stock_amount * $data['conversion_rate']),
                );

                $gl_data[] = array(
                    'document_detail_id' => $stock_adjustment_detail_id,
                    'coa_id' => $product['adjustment_account_id'],
                    'document_debit' => 0,
                    'document_credit' => (-1*$stock_amount),
                    'debit' => 0,
                    'credit' => (-1*$stock_amount * $data['conversion_rate']),
                    'remarks' => $data['remarks'],
                );
            } else if( $stock_amount > 0 ) {
                $gl_data[] = array(
                    'document_detail_id' => $stock_adjustment_detail_id,
                    'coa_id' => $product['inventory_account_id'],
                    'document_credit' => $stock_amount,
                    'document_debit' => 0,
                    'credit' => ($stock_amount * $data['conversion_rate']),
                    'debit' => 0,
                    'remarks' => $data['remarks'],
                    'product_id' => $detail['product_id'],
                    'qty' => $stock_qty,
                    'document_amount' => $stock_amount,
                    'amount' => ($stock_amount * $data['conversion_rate']),
                );

                $gl_data[] = array(
                    'document_detail_id' => $stock_adjustment_detail_id,
                    'coa_id' => $product['adjustment_account_id'],
                    'document_debit' => $stock_amount,
                    'document_credit' => 0,
                    'debit' => ($stock_amount * $data['conversion_rate']),
                    'credit' => 0,
                    'remarks' => $data['remarks'],
                );
            }

            if( floatval($detail['amount']) > 0 )
            {
                $gl_data[] = array(
                    'document_detail_id' => $stock_adjustment_detail_id,
                    'coa_id' => $product['inventory_account_id'],
                    'document_credit' => 0,
                    'document_debit' => $detail['amount'],
                    'credit' => 0,
                    'debit' => ($detail['amount'] * $data['conversion_rate']),
                    'remarks' => $data['remarks'],
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'document_amount' => $detail['amount'],
                    'amount' => ($detail['amount'] * $data['conversion_rate']),
                );

                $gl_data[] = array(
                    'document_detail_id' => $stock_adjustment_detail_id,
                    'coa_id' => $product['adjustment_account_id'],
                    'document_credit' => $detail['amount'],
                    'document_debit' => 0,
                    'credit' => ($detail['amount'] * $data['conversion_rate']),
                    'debit' => 0,
                    'remarks' => $data['remarks'],
                );
            }

            if( $stock_qty != 0 || $stock_amount != 0 )
            {
                $stock_ledger[] = array(
                    'document_detail_id' => getGUID(),
                    'warehouse_id' => $data['warehouse_id'],
                    'product_id' => $detail['product_id'],
                    'document_unit_id' => $detail['unit_id'],
                    'document_qty' => (-1 * $stock_qty),
                    'unit_conversion' => 1,
                    'base_unit_id' => $detail['unit_id'],
                    'base_qty' => (-1 * $stock_qty),
                    'document_rate' => $stock_rate,
                    'document_amount' => (-1 * $stock_amount),
                    'base_rate' => ($stock_rate * $detail['conversion_rate']),
                    'base_amount' => (-1 * $stock_amount * $detail['conversion_rate']),
                    'remarks' => $data['remarks'],
                    'document_flow' => 'OUT',
                    'sort_order' => $re_order,
                );
                $re_order++;
            }

            if( $detail['qty'] > 0 )
            {
                $stock_ledger[] = array(
                    'document_detail_id' => $stock_adjustment_detail_id,
                    'warehouse_id' => $data['warehouse_id'],
                    'product_id' => $detail['product_id'],
                    'document_unit_id' => $detail['unit_id'],
                    'document_qty' => $detail['qty'],
                    'unit_conversion' => 1,
                    'base_unit_id' => $detail['unit_id'],
                    'base_qty' => ($detail['qty']),
                    'document_rate' => $detail['rate'],
                    'document_amount' => ($detail['amount']),
                    'base_rate' => ($detail['rate'] * $detail['conversion_rate']),
                    'base_amount' => ($detail['amount'] * $detail['conversion_rate']),
                    'remarks' => $data['remarks'],
                    'document_flow' => 'IN',
                    'sort_order' => $re_order
                );
                $re_order++;
            }
        }

        foreach($gl_data as $sort_order => $ledger) {
            $ledger['company_id'] = $this->session->data['company_id'];
            $ledger['company_branch_id'] = $this->session->data['company_branch_id'];
            $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $ledger['document_type_id'] = $this->document_type_id;
            $ledger['document_id'] = $data['document_id'];
            $ledger['document_identity'] = $data['document_identity'];
            $ledger['document_date'] = $data['document_date'];
            $ledger['sort_order'] = $sort_order;
            $ledger['base_currency_id'] = $data['base_currency_id'];
            $ledger['document_currency_id'] = $data['document_currency_id'];
            $ledger['conversion_rate'] = $data['conversion_rate'];
            $ledger['partner_type_id'] = $data['partner_type_id'];
            $ledger['partner_id'] = $data['partner_id'];

            $ledger_id = $this->model['ledger']->add($this->getAlias(), $ledger);
        }

        foreach($stock_ledger as $sort_order => $ledger) {
            $ledger['company_id'] = $this->session->data['company_id'];
            $ledger['company_branch_id'] = $this->session->data['company_branch_id'];
            $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $ledger['document_type_id'] = $this->document_type_id;
            $ledger['document_id'] = $data['document_id'];
            $ledger['document_identity'] = $data['document_identity'];
            $ledger['document_date'] = $data['document_date'];
            $ledger['sort_order'] = $sort_order;
            $ledger['base_currency_id'] = $data['base_currency_id'];
            $ledger['document_currency_id'] = $data['document_currency_id'];
            $ledger['currency_conversion'] = $data['conversion_rate'];

            $stock_ledger_id = $this->model['stock_ledger']->add($this->getAlias(), $ledger);
        }

        unset($this->session->data['detail'][$form_key]);
        return $stock_adjustment_id;
    }

    protected function insertRedirect($id, $data) {
        $url = $this->getURL();
        $this->redirect($this->url->link($this->getAlias().'/update', 'token=' . $this->session->data['token'] . '&stock_adjustment_id=' . $id, 'SSL'));
    }

    protected function updateData($primary_key, $data) {

        $form_key = $data['form_key'];
        $data['stock_adjustment_details'] = $this->session->data['detail'][$form_key];
// d([$primary_key, $data],true);

        $stock_adjustment_id = $primary_key;
        $data['document_date'] = MySqlDate($data['document_date']);

        $this->model['stock_adjustment'] = $this->load->model('inventory/stock_adjustment');
        $this->model['stock_adjustment_detail'] = $this->load->model('inventory/stock_adjustment_detail');
        $this->model['document'] = $this->load->model('common/document');
        $this->model['product'] = $this->load->model('inventory/product');
        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');

        $this->model['ledger']->deleteBulk($this->getAlias(), array('document_id' => $stock_adjustment_id));
        $this->model['stock_ledger']->deleteBulk($this->getAlias(), array('document_id' => $stock_adjustment_id));
        $this->model['stock_adjustment_detail']->deleteBulk($this->getAlias(), array('stock_adjustment_id' => $stock_adjustment_id));
        $this->model['document']->deleteBulk($this->getAlias(), array('document_id' => $stock_adjustment_id));
        $this->model['stock_adjustment']->edit($this->getAlias(), $primary_key, $data);

        $insert_document = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'document_type_id' => $this->document_type_id,
            'document_id' => $data['document_id'],
            'document_identity' => $data['document_identity'],
            'document_date' => $data['document_date'],
            'partner_type_id' => $data['partner_type_id'],
            'partner_id' => $data['partner_id'],
            'document_currency_id' => $data['document_currency_id'],
            'document_amount' => $data['total_amount'],
            'conversion_rate' => $data['conversion_rate'],
            'base_currency_id' => $data['base_currency_id'],
            'base_amount' => $data['base_amount'],
        );
        $document_id = $this->model['document']->add($this->getAlias(), $insert_document);

        $gl_data = array();
        $stock_ledger = array();
        $re_order = 0;
        foreach ($data['stock_adjustment_details'] as $sort_order => $detail) {
            $detail['stock_adjustment_id'] = $stock_adjustment_id;
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['sort_order'] = $sort_order;
            $detail['document_currency_id'] = $data['document_currency_id'];
            $detail['base_currency_id'] = $data['base_currency_id'];
            $detail['conversion_rate'] = $data['conversion_rate'];
            $detail['base_rate'] = ($detail['rate'] * $detail['conversion_rate']);
            $detail['base_amount'] = ($detail['amount'] * $detail['conversion_rate']);
            $stock_adjustment_detail_id=$this->model['stock_adjustment_detail']->add($this->getAlias(), $detail);

            $product = $this->model['product']->getRow(array('product_id' => $detail['product_id']));
            $stock = $this->model['stock_ledger']->getWarehouseStockAdjustment($detail['product_id'], $data['warehouse_id'], $data['document_identity'], $data['document_date']);
            $stock_amount = $stock['stock_amount'];
            $stock_qty = $stock['stock_qty'];
            $stock_rate = floatval(($stock_amount / $stock_qty));
            if( $stock_amount < 0 )
            {
                $gl_data[] = array(
                    'document_detail_id' => $stock_adjustment_detail_id,
                    'coa_id' => $product['inventory_account_id'],
                    'document_credit' => 0,
                    'document_debit' => (-1*$stock_amount),
                    'credit' => 0,
                    'debit' => (-1*$stock_amount * $data['conversion_rate']),
                    'remarks' => $data['remarks'],
                    'product_id' => $detail['product_id'],
                    'qty' => -1*$stock_qty,
                    'document_amount' => (-1*$stock_amount),
                    'amount' => (-1*$stock_amount * $data['conversion_rate']),
                );

                $gl_data[] = array(
                    'document_detail_id' => $stock_adjustment_detail_id,
                    'coa_id' => $product['adjustment_account_id'],
                    'document_debit' => 0,
                    'document_credit' => (-1*$stock_amount),
                    'debit' => 0,
                    'credit' => (-1*$stock_amount * $data['conversion_rate']),
                    'remarks' => $data['remarks'],
                );
            } else if( $stock_amount > 0 ) {
                $gl_data[] = array(
                    'document_detail_id' => $stock_adjustment_detail_id,
                    'coa_id' => $product['inventory_account_id'],
                    'document_credit' => $stock_amount,
                    'document_debit' => 0,
                    'credit' => ($stock_amount * $data['conversion_rate']),
                    'debit' => 0,
                    'remarks' => $data['remarks'],
                    'product_id' => $detail['product_id'],
                    'qty' => $stock_qty,
                    'document_amount' => $stock_amount,
                    'amount' => ($stock_amount * $data['conversion_rate']),
                );

                $gl_data[] = array(
                    'document_detail_id' => $stock_adjustment_detail_id,
                    'coa_id' => $product['adjustment_account_id'],
                    'document_debit' => $stock_amount,
                    'document_credit' => 0,
                    'debit' => ($stock_amount * $data['conversion_rate']),
                    'credit' => 0,
                    'remarks' => $data['remarks'],
                );
            }

            if( floatval($detail['amount']) > 0 )
            {
                $gl_data[] = array(
                    'document_detail_id' => $stock_adjustment_detail_id,
                    'coa_id' => $product['inventory_account_id'],
                    'document_credit' => 0,
                    'document_debit' => $detail['amount'],
                    'credit' => 0,
                    'debit' => ($detail['amount'] * $data['conversion_rate']),
                    'remarks' => $data['remarks'],
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'document_amount' => $detail['amount'],
                    'amount' => ($detail['amount'] * $data['conversion_rate']),
                );

                $gl_data[] = array(
                    'document_detail_id' => $stock_adjustment_detail_id,
                    'coa_id' => $product['adjustment_account_id'],
                    'document_credit' => $detail['amount'],
                    'document_debit' => 0,
                    'credit' => ($detail['amount'] * $data['conversion_rate']),
                    'debit' => 0,
                    'remarks' => $data['remarks'],
                );
            }

            if( $stock_qty != 0 || $stock_amount != 0 )
            {
                $stock_ledger[] = array(
                    'document_detail_id' => getGUID(),
                    'warehouse_id' => $data['warehouse_id'],
                    'product_id' => $detail['product_id'],
                    'document_unit_id' => $detail['unit_id'],
                    'document_qty' => (-1 * $stock_qty),
                    'unit_conversion' => 1,
                    'base_unit_id' => $detail['unit_id'],
                    'base_qty' => (-1 * $stock_qty),
                    'document_rate' => $stock_rate,
                    'document_amount' => (-1 * $stock_amount),
                    'base_rate' => ($stock_rate * $detail['conversion_rate']),
                    'base_amount' => (-1 * $stock_amount * $detail['conversion_rate']),
                    'remarks' => $data['remarks'],
                    'document_flow' => 'OUT',
                    'sort_order' => $re_order,
                );
                $re_order++;
            }

            if( $detail['qty'] > 0 )
            {
                $stock_ledger[] = array(
                    'document_detail_id' => $stock_adjustment_detail_id,
                    'warehouse_id' => $data['warehouse_id'],
                    'product_id' => $detail['product_id'],
                    'document_unit_id' => $detail['unit_id'],
                    'document_qty' => $detail['qty'],
                    'unit_conversion' => 1,
                    'base_unit_id' => $detail['unit_id'],
                    'base_qty' => ($detail['qty']),
                    'document_rate' => $detail['rate'],
                    'document_amount' => ($detail['amount']),
                    'base_rate' => ($detail['rate'] * $detail['conversion_rate']),
                    'base_amount' => ($detail['amount'] * $detail['conversion_rate']),
                    'remarks' => $data['remarks'],
                    'document_flow' => 'IN',
                    'sort_order' => $re_order
                );
                $re_order++;
            }
        }

        foreach($gl_data as $sort_order => $ledger) {
            $ledger['company_id'] = $this->session->data['company_id'];
            $ledger['company_branch_id'] = $this->session->data['company_branch_id'];
            $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $ledger['document_type_id'] = $this->document_type_id;
            $ledger['document_id'] = $data['document_id'];
            $ledger['document_identity'] = $data['document_identity'];
            $ledger['document_date'] = $data['document_date'];
            $ledger['sort_order'] = $sort_order;
            $ledger['base_currency_id'] = $data['base_currency_id'];
            $ledger['document_currency_id'] = $data['document_currency_id'];
            $ledger['conversion_rate'] = $data['conversion_rate'];
            $ledger['partner_type_id'] = $data['partner_type_id'];
            $ledger['partner_id'] = $data['partner_id'];

            $ledger_id = $this->model['ledger']->add($this->getAlias(), $ledger);
        }

        foreach($stock_ledger as $sort_order => $ledger) {
            $ledger['company_id'] = $this->session->data['company_id'];
            $ledger['company_branch_id'] = $this->session->data['company_branch_id'];
            $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $ledger['document_type_id'] = $this->document_type_id;
            $ledger['document_id'] = $data['document_id'];
            $ledger['document_identity'] = $data['document_identity'];
            $ledger['document_date'] = $data['document_date'];
            $ledger['sort_order'] = $sort_order;
            $ledger['base_currency_id'] = $data['base_currency_id'];
            $ledger['document_currency_id'] = $data['document_currency_id'];
            $ledger['currency_conversion'] = $data['conversion_rate'];

            $stock_ledger_id = $this->model['stock_ledger']->add($this->getAlias(), $ledger);
        }

        unset($this->session->data['detail'][$form_key]);
        return $stock_adjustment_id;
    }

    protected function updateRedirect($id, $data) {
        $url = $this->getURL();
        $this->redirect($this->url->link($this->getAlias().'/update', 'token=' . $this->session->data['token'] . '&stock_adjustment_id=' . $id, 'SSL'));
    }

    protected function deleteData($primary_key) {
        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['ledger']->deleteBulk($this->getAlias(), array('document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');
        $this->model['stock_ledger']->deleteBulk($this->getAlias(), array('document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $this->model['stock_adjustment_detail'] = $this->load->model('inventory/stock_adjustment_detail');
        $this->model['stock_adjustment_detail']->deleteBulk($this->getAlias(),array('stock_adjustment_id' => $primary_key));

        $this->model['document'] = $this->load->model('common/document');
        $this->model['document']->deleteBulk($this->getAlias(), array('document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);
    }

    public function printDocument() {
        $company_id = $this->session->data['company_id'];
        $company_branch_id = $this->session->data['company_branch_id'];
        $fiscal_year_id = $this->session->data['fiscal_year_id'];

        $stock_adjustment_id = $this->request->get['stock_adjustment_id'];
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

        $this->model['stock_adjustment'] = $this->load->model('inventory/stock_adjustment');
        $row = $this->model['stock_adjustment']->getRow(array('stock_adjustment_id' => $stock_adjustment_id));

        $this->model['partner'] = $this->load->model('common/partner');
        $partner = $this->model['partner']->getRow(array('company_id' => $company_id, 'company_branch_id' => $company_branch_id, 'partner_type_id' => $row['partner_type_id'], 'partner_id' => $row['partner_id']));
//d($partner, true);

        $this->data['document_date'] = $row['document_date'];
        $this->data['document_no'] = $row['document_identity'];
        $this->data['partner_name'] = $partner['name'];
        $this->data['phone_no'] = $partner['phone'];
        $this->data['address'] = $partner['address'];

        $this->model['stock_adjustment_detail'] = $this->load->model('inventory/stock_adjustment_detail');
        $details = $this->model['stock_adjustment_detail']->getRows(array('stock_adjustment_id' => $stock_adjustment_id));
        foreach($details as $row_no => $detail) {
            $this->data['details'][$row_no] = $detail;
        }

        $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

// set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Muhammad Salman');
        $pdf->SetTitle('Stock Adjustment');
        $pdf->SetSubject('Stock Adjustment');

        $pdf->data = array(
            'company_name' => $this->session->data['company_name'],
            'company_logo' => $this->session->data['company_image'],
            'report_name' => 'Stock Adjustment',
            'header_image' => HTTP_IMAGE.'header.jpg',
            'footer_image' => HTTP_IMAGE.'footer.jpg',
        );

        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetMargins(10, 40, 10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 55);

// add a page
        $pdf->AddPage();

        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(23, 7, 'Document Date:', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(25, 7, stdDate($row['document_date']), 'B', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(96, 7, '', '', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(21, 7, 'Document No.', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(25, 7, $row['document_identity'], 'B', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(7);
        $pdf->Cell(18, 7, 'Warehouse:', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(0, 7, $row['warehouse'], 'B', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(7);
        $pdf->Cell(18, 7, 'Remarks:', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(0, 7, $row['remarks'], 'B', false, 'L', 0, '', 0, false, 'M', 'M');


        $pdf->Ln(15);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 7);
// d($details, true);

        $total_qty = 0;
        foreach ($details as  $detail) {
            if( strlen($detail['product_name']) < 50 )
            {
                $pdf->Cell(25, 6, $detail['product_code'], 1, false, 'C', 1, '', 1);
                $pdf->Cell(70, 6, $detail['product_name'], 1, false, 'L', 1, '', 1);
                $pdf->Cell(12, 6, $detail['unit'], 1, false, 'C', 1, '', 1);
                $pdf->Cell(15, 6, $detail['stock_qty'], 1, false, 'C', 1, '', 1);
                $pdf->Cell(18, 6, $detail['qty'], 1, false, 'C', 1, '', 1);
                $pdf->Cell(25, 6, number_format($detail['rate'], 2), 1, false, 'R', 1, '', 1);
                $pdf->Cell(25, 6, number_format($detail['amount'], 2), 1, false, 'R', 1, '', 1);
                $pdf->Ln(6);
            }
            else
            {
                $arrProducts = str_split($detail['product_name'], 50);
                foreach($arrProducts as $index => $product) {
                    if($index==0)
                    {
                        $pdf->Cell(25, 5, $detail['product_code'], 'LTR', false, 'C', 1, '', 1);
                        $pdf->Cell(70, 5, $product, 'LTR', false, 'L', 1, '', 1);
                        $pdf->Cell(12, 5, $detail['unit'], 'LTR', false, 'C', 1, '', 1);
                        $pdf->Cell(15, 5, $detail['stock_qty'], 'LTR', false, 'C', 1, '', 1);
                        $pdf->Cell(18, 5, $detail['qty'], 'LTR', false, 'C', 1, '', 1);
                        $pdf->Cell(25, 5, number_format($detail['rate'], 2), 'LTR', false, 'R', 1, '', 1);
                        $pdf->Cell(25, 5, number_format($detail['amount'], 2), 'LTR', false, 'R', 1, '', 1);
                        $pdf->Ln(5);
                    }
                    elseif($index==count($arrRemarks)-1)
                    {
                        $pdf->Cell(25, 5, '', 'LR', false, 'C', 1, '', 1);
                        $pdf->Cell(70, 5, $product, 'LR', false, 'L', 1, '', 1);
                        $pdf->Cell(12, 5, '', 'LR', false, 'C', 1, '', 1);
                        $pdf->Cell(15, 5, '', 'LR', false, 'C', 1, '', 1);
                        $pdf->Cell(18, 5, '', 'LR', false, 'C', 1, '', 1);
                        $pdf->Cell(25, 5, '', 'LR', false, 'R', 1, '', 1);
                        $pdf->Cell(25, 5, '', 'LR', false, 'R', 1, '', 1);
                        $pdf->Ln(5);
                    }
                    else
                    {
                        $pdf->Cell(25, 5, '', 'LR', false, 'C', 1, '', 1);
                        $pdf->Cell(70, 5, $product, 'LR', false, 'L', 1, '', 1);
                        $pdf->Cell(12, 5, '', 'LR', false, 'C', 1, '', 1);
                        $pdf->Cell(15, 5, '', 'LR', false, 'C', 1, '', 1);
                        $pdf->Cell(18, 5, '', 'LR', false, 'C', 1, '', 1);
                        $pdf->Cell(25, 5, '', 'LR', false, 'R', 1, '', 1);
                        $pdf->Cell(25, 5, '', 'LR', false, 'R', 1, '', 1);
                        $pdf->Ln(5);
                    }
                }
            }

            $total_qty += $detail['qty'];
        }

        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->Cell(25, 6, '', 1, false, 'C', 1, '', 1);
        $pdf->Cell(70, 6, '', 1, false, 'L', 1, '', 1);
        $pdf->Cell(12, 6, '', 1, false, 'C', 1, '', 1);
        $pdf->Cell(15, 6, 'Total', 1, false, 'C', 1, '', 1);
        $pdf->Cell(18, 6, $total_qty, 1, false, 'C', 1, '', 1);
        $pdf->Cell(25, 6, '', 1, false, 'R', 1, '', 1);
        $pdf->Cell(25, 6, '', 1, false, 'R', 1, '', 1);




        $pdf->Output('Stock Adjustment - '.$row['document_identity'].'.pdf', 'I');
    }

}

class PDF extends TCPDF {
    public $data = array();

    public function header(){

        $this->Ln(5);
        $this->SetFont('helvetica', 'B', 24);
        $this->Cell(0, 12, html_entity_decode($this->data['company_name']), 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(8);
        $this->SetFont('helvetica', 'B,U', 12);
        $this->Cell(0, 7, 'Stock Adjustment', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(0, 0, 0);
        $this->setCellPaddings(1);

        $this->ln(45);
        $this->SetFont('helvetica', 'B', 7);
        $this->Cell(25, 6, 'Product Code', 1, false, 'C', 1, '', 1);
        $this->Cell(70, 6, 'Description', 1, false, 'C', 1, '', 1);
        $this->Cell(12, 6, 'Unit', 1, false, 'C', 1, '', 1);
        $this->Cell(15, 6, 'Stock Qty', 1, false, 'C', 1, '', 1);
        $this->Cell(18, 6, 'Adjusted Qty', 1, false, 'C', 1, '', 1);
        $this->Cell(25, 6, 'Adjusted Rate', 1, false, 'C', 1, '', 1);
        $this->Cell(25, 6, 'Adjusted Amount', 1, false, 'C', 1, '', 1);

    }
    public function footer(){
        $this->SetY(-15);
// Set font
        $this->SetFont('times', 'I', 8);
// Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

?>