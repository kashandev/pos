<?php

class ControllerProductionProduction extends HController {
    protected $document_type_id = 28;

    protected function getAlias() {
        return 'production/production';
    }

    protected function getPrimaryKey() {
        return 'production_id';
    }

    protected function getList() {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }

    public function getAjaxLists() {
        $lang = $this->load->language($this->getAlias());
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        $data = array();
        $aColumns = array('action', 'document_date', 'document_identity', 'product_code', 'product_name', 'actual_quantity', 'remarks', 'created_at', 'check_box');

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
        $this->model['product'] = $this->load->model('inventory/product');
        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        // d($this->session->data,true);
        $this->data['restrict_out_of_stock'] = $this->session->data['restrict_out_of_stock'];
        $filter = array(
            'company_id' => $this->session->data['company_id']
        );
        $this->data['products'] = $this->model['product']->getRows($filter);
        $filter['company_branch_id'] = $this->session->data['company_branch_id'];
        $this->data['warehouses'] = $this->model['warehouse']->getRows($filter);
        $this->model['currency'] = $this->load->model('setup/currency');
        $this->data['currencys'] = $this->model['currency']->getRows();
        $this->data['base_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['base_currency'] = $this->session->data['base_currency_name'];
        $this->data['document_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['conversion_rate'] = "1.00";
        $this->data['document_date'] = stdDate();

        if (isset($this->request->get['production_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $this->data['isEdit'] =1;
            $result = $this->model[$this->getAlias()]->getRow(array($this->getPrimaryKey() => $this->request->get[$this->getPrimaryKey()]));
            foreach ($result as $field => $value) {
                if($field == 'document_date') {
                    $this->data[$field] = stdDate($value);
                } else {
                    $this->data[$field] = $value;
                }
            }
            $this->model['production_detail'] = $this->load->model('production/production_detail');
            $this->data['production_details'] = $this->model['production_detail']->getRows(array('production_id' => $this->request->get['production_id']));
            // d($this->data['production_details'],true);
        }


        $this->data['action_post'] = $this->url->link($this->getAlias() . '/post', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_print'] = $this->url->link($this->getAlias() . '/printDocument', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['href_get_bom'] = $this->url->link('production/production/getBOM', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');

        $this->data['strValidation'] = "{
            'rules': {
                'product_id': {'required': true},
                'unit_id': {'required': true},
                'warehouse_id': {'required': true},
                'expected_quantity': {'required': true},
                'actual_quantity': {'required': true},
               
            },
            'ignore': [],
        }";
        // $this->data['strValidation'] = "{
        //     'rules': {
        //         'product_id': {'required': true},
        //         'unit_id': {'required': true},
        //         'warehouse_id': {'required': true},
        //         'expected_quantity': {'required': true},
        //         'actual_quantity': {'required': true},
        //          'amount': {'required': true, 'min': 1},
        //         'rate': {'required': true, 'min': 1},
               
        //     },
        //     'ignore': [],
        // }";

        $this->response->setOutput($this->render());
    }

    public function getBOM() {
        $post = $this->request->post;
        $this->model['bom_detail'] = $this->load->model('production/bom_detail');
        $this->model['stock'] = $this->load->model('common/stock_ledger');
        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $warehouses = $this->model['warehouse']->getRows(array('company_id' => $this->session->data['company_id'], 'company_branch_id' => $this->session->data['company_branch_id']));
        $rows = $this->model['bom_detail']->getRows(array('company_id' => $this->session->data['company_id'], 'master_product_id' => $post['product_id']));
        // d($rows,true);
        $html = '';
        foreach($rows as $row_id => $row) {
            $stock = $this->model['stock']->getStock(array('company_id' => $this->session->data['company_id'], 'product_id' => $row['product_id']));
            // d($stock,true);
            $html .= '<tr id="row_id_'.$row_id.'" data-row_id="'.$row_id.'">';
            $html .= '<td>';
            $html .= '<select class="form-control" id="production_detail_warehouse_id_'.$row_id.'" name="production_details['.$row_id.'][warehouse_id]">';
            if(count($warehouses)>1) {
                $html .= '<option value="">&nbsp;</option>';
            }
            foreach($warehouses as $warehouse) {
                $html .= '<option value="'.$warehouse['warehouse_id'].'">'.$warehouse['name'].'</option>';
            }
            $html .= '</select>';
            $html .= '</td>';
            $html .= '<td><input type="text" class="form-control" id="production_detail_product_code_'.$row_id.'" name="production_details['.$row_id.'][product_code]" value="'.$row['product_code'].'" readonly="true"/></td>';
            $html .= '<td>';
            $html .= '<input type="hidden" class="form-control" id="production_detail_product_id_'.$row_id.'" name="production_details['.$row_id.'][product_id]" value="'.$row['product_id'].'"/>';
            $html .= '<input type="text" class="form-control" id="production_detail_product_name_'.$row_id.'" name="production_details['.$row_id.'][product_name]" value="'.$row['product_name'].'" readonly="true"/>';
            $html .= '</td>';
            $html .= '<td>';
            $html .= '<input type="hidden" class="form-control" id="production_detail_unit_id_'.$row_id.'" name="production_details['.$row_id.'][unit_id]" value="'.$row['unit_id'].'"/>';
            $html .= '<input type="text" class="form-control" id="production_detail_unit_'.$row_id.'" name="production_details['.$row_id.'][unit]" value="'.$row['unit'].'" readonly="true"/>';
            $html .= '</td>';
            $html .= '<td hidden="hidden">';
            $html .= '<input type="text" class="form-control text-right" id="production_detail_unit_quantity_'.$row_id.'" name="production_details['.$row_id.'][unit_quantity]" value="'.$row['qty'].'" readonly="true"/>';
            $html .= '</td>';
            $html .= '<td>';
            $html .= '<input type="text" class="form-control text-right" id="production_detail_expected_quantity_'.$row_id.'" name="production_details['.$row_id.'][expected_quantity]" value="0" readonly="true"/>';
            $html .= '</td>';
            $html .= '<td>';
            $html .= '<input onchange="calculateRowTotal('.$row_id.');" type="text" class="form-control text-right" id="production_detail_actual_quantity_'.$row_id.'" name="production_details['.$row_id.'][actual_quantity]" value="0" />';
            $html .= '<input type="hidden" class="form-control text-right" id="production_detail_stock_quantity_'.$row_id.'" name="production_details['.$row_id.'][stock_quantity]" value="'.($stock['stock_qty']?$stock['stock_qty']:0).'" />';
            $html .= '</td>';
            $html .= '<td hidden = "hidden"><input type="text" class="form-control text-right" id="production_detail_cog_rate_'.$row_id.'" name="production_details['.$row_id.'][cog_rate]" value="'.($stock['avg_cogs_rate']<0)?$stock['cost_price']:$stock['avg_cogs_rate'].'" readonly="true"/></td>';
            $html .= '<td td hidden = "hidden"><input type="text" class="form-control text-right" id="production_detail_cog_amount_'.$row_id.'" name="production_details['.$row_id.'][cog_amount]" value="1" readonly="true" /></td>';
            $html .= '</tr>';
        }

        $json = array(
            'post' => $post,
            'stock' => $stock,
            'rows' => $rows,
            'html' => $html,
            'success' => true
        );

        echo json_encode($json);
        exit;
    }

    protected function insertData($data) {
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
        $data['base_rate'] = $data['rate'] * $data['conversion_rate'];
        $data['base_amount'] = $data['amount'] * $data['conversion_rate'];

        $production_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);
        $data['document_id'] = $production_id;

        $this->model['document'] = $this->load->model('common/document');
        $insert_document = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'document_type_id' => $this->document_type_id,
            'document_id' => $data['document_id'],
            'document_identity' => $data['document_identity'],
            'document_date' => $data['document_date'],
            'document_currency_id' => $data['document_currency_id'],
            'document_amount' => $data['amount'],
            'conversion_rate' => $data['conversion_rate'],
            'base_currency_id' => $data['base_currency_id'],
            'base_amount' => ($data['amount'] * $data['conversion_rate']),
        );
        $document_id = $this->model['document']->add($this->getAlias(), $insert_document);

        $gl_data = array();
        $stock_ledger = array();
        $stock_ledger[] = array(
            'warehouse_id' => $data['warehouse_id'],
            'product_id' => $data['product_id'],
            'document_unit_id' => $data['unit_id'],
            'document_qty' => $data['actual_quantity'],
            'unit_conversion' => 1,
            'base_unit_id' => $data['unit_id'],
            'base_qty' => ($data['actual_quantity']),
            'document_rate' => $data['rate'],
            'document_amount' => ($data['amount']),
            'base_rate' => ($data['rate'] * $data['conversion_rate']),
            'base_amount' => ($data['amount'] * $data['conversion_rate']),
            'remarks' => $data['remarks'],
        );

        $this->model['production_detail'] = $this->load->model('production/production_detail');
        foreach ($data['production_details'] as $sort_order => $detail) {
            $detail['production_id'] = $production_id;
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['sort_order'] = $sort_order;
            $detail['document_currency_id'] = $data['document_currency_id'];
            $detail['base_currency_id'] = $data['base_currency_id'];
            $detail['conversion_rate'] = $data['conversion_rate'];
            $detail['base_cog_rate'] = $detail['cog_rate'] * $data['conversion_rate'];
            $detail['base_cog_amount'] = $detail['cog_amount'] * $data['conversion_rate'];
            $production_detail_id=$this->model['production_detail']->add($this->getAlias(), $detail);

            $stock_ledger[] = array(
                'document_detail_id' => $production_detail_id,
                'warehouse_id' => $detail['warehouse_id'],
                'product_id' => $detail['product_id'],
                'document_unit_id' => $detail['unit_id'],
                'document_qty' => $detail['actual_quantity'],
                'unit_conversion' => 1,
                'base_unit_id' => $detail['unit_id'],
                'base_qty' => (-1 * $detail['actual_quantity']),
                'document_rate' => $detail['cog_rate'],
                'document_amount' => (-1 * $detail['cog_amount']),
                'base_rate' => ($detail['cog_rate'] * $data['conversion_rate']),
                'base_amount' => (-1 * $detail['cog_amount'] * $data['conversion_rate']),
                'remarks' => $data['remarks'],
            );
        }
        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');
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
        return $production_id;
    }

    protected function updateData($primary_key, $data) {
        $this->model['production_detail'] = $this->load->model('production/production_detail');
        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');
        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['document'] = $this->load->model('common/document');

        $production_id = $primary_key;
        $data['document_date'] = MySqlDate($data['document_date']);
        $this->model[$this->getAlias()]->edit($this->getAlias(), $primary_key, $data);

        $this->model['production_detail']->deleteBulk($this->getAlias(), array('production_id' => $primary_key));
        $this->model['stock_ledger']->deleteBulk($this->getAlias(), array('document_id' => $primary_key));
        $this->model['ledger']->deleteBulk($this->getAlias(), array('document_id' => $primary_key));
        $this->model['document']->delete($this->getAlias(), $primary_key);

        $insert_document = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'document_type_id' => $this->document_type_id,
            'document_id' => $data['document_id'],
            'document_identity' => $data['document_identity'],
            'document_date' => $data['document_date'],
            'document_currency_id' => $data['document_currency_id'],
            'document_amount' => $data['amount'],
            'conversion_rate' => $data['conversion_rate'],
            'base_currency_id' => $data['base_currency_id'],
            'base_amount' => ($data['amount'] * $data['conversion_rate']),
        );
        $document_id = $this->model['document']->add($this->getAlias(), $insert_document);

        $gl_data = array();
        $stock_ledger = array();
        $stock_ledger[] = array(
            'warehouse_id' => $data['warehouse_id'],
            'product_id' => $data['product_id'],
            'document_unit_id' => $data['unit_id'],
            'document_qty' => $data['actual_quantity'],
            'unit_conversion' => 1,
            'base_unit_id' => $data['unit_id'],
            'base_qty' => ($data['actual_quantity']),
            'document_rate' => $data['rate'],
            'document_amount' => ($data['amount']),
            'base_rate' => ($data['rate'] * $data['conversion_rate']),
            'base_amount' => ($data['amount'] * $data['conversion_rate']),
            'remarks' => $data['remarks'],
        );

        foreach ($data['production_details'] as $sort_order => $detail) {
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['production_id'] = $production_id;
            $detail['sort_order'] = $sort_order;

            $production_detail_id = $this->model['production_detail']->add($this->getAlias(), $detail);
            $stock_ledger[] = array(
                'document_detail_id' => $production_detail_id,
                'warehouse_id' => $detail['warehouse_id'],
                'product_id' => $detail['product_id'],
                'document_unit_id' => $detail['unit_id'],
                'document_qty' => $detail['actual_quantity'],
                'unit_conversion' => 1,
                'base_unit_id' => $detail['unit_id'],
                'base_qty' => (-1 * $detail['actual_quantity']),
                'document_rate' => $detail['cog_rate'],
                'document_amount' => (-1 * $detail['cog_amount']),
                'base_rate' => ($detail['cog_rate'] * $data['conversion_rate']),
                'base_amount' => (-1 * $detail['cog_amount'] * $data['conversion_rate']),
                'remarks' => $data['remarks'],
            );
        }

        //d(array($data,$stock_ledger),true);
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
        return $production_id;
    }

    protected function deleteData($primary_key) {
        $this->model['production_detail'] = $this->load->model('production/production_detail');
        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');
        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['document'] = $this->load->model('common/document');

        $this->model['document']->delete($this->getAlias(),$primary_key);
        $this->model['production_detail']->deleteBulk($this->getAlias(),array('production_id' => $primary_key));
        $this->model['stock_ledger']->deleteBulk($this->getAlias(),array('document_id' => $primary_key));
        $this->model['ledger']->deleteBulk($this->getAlias(),array('document_id' => $primary_key));

        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);
    }

}
?>
