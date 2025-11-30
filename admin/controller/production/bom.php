<?php

class ControllerProductionBOM extends HController {
    protected $document_type_id = 27;

    protected function getAlias() {
        return 'production/bom';
    }

    protected function getPrimaryKey() {
        return 'bom_id';
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
        $aColumns = array('action', 'document_date', 'document_identity', 'product_code', 'product_name', 'created_at','check_box');

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
                'text' => $lang['delete'],
                'href' => 'javascript:void(0);',
                'click' => "ConfirmDelete('" . $this->url->link($this->getAlias() . '/delete', 'token=' . $this->session->data['token'] . '&id=' . $aRow[$this->getPrimaryKey()], 'SSL') . "')",
                'btn_class' => 'btn btn-danger btn-xs',
                'class' => 'fa fa-times'
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
        $this->model['unit'] = $this->load->model('inventory/unit');

        $filter = array(
            'company_id' => $this->session->data['company_id']
        );
        $this->data['products'] = $this->model['product']->getRows($filter);

        $this->data['document_date'] = stdDate();

        if (isset($this->request->get['bom_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $result = $this->model[$this->getAlias()]->getRow(array($this->getPrimaryKey() => $this->request->get[$this->getPrimaryKey()]));
            foreach ($result as $field => $value) {
                    $this->data[$field] = $value;
            }
            $this->model['bom_detail'] = $this->load->model('production/bom_detail');
            $this->data['bom_details'] = $this->model['bom_detail']->getRows(array('bom_id' => $this->request->get['bom_id']), array('sort_order'));
        }


        $this->data['action_validate_product'] = $this->url->link('production/bom/validateProduct', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['strValidation'] = "{
            'rules': {
                'product_id': {'required': true, 'remote':  {url: '" . $this->data['action_validate_product'] . "', type: 'post'}},
                'unit_id': {'required': true},
            },
            'ignore': [],
        }";

        $this->response->setOutput($this->render());
    }

    public function validateProduct() {
        $lang = $this->load->language($this->getAlias());
        $post = $this->request->post;
        $bom_id = $this->request->get['bom_id'];

        $this->model['bom'] = $this->load->model('production/bom');
        $where = "`company_id` = '".$this->session->data['company_id']."' AND `product_id` = '".$post['product_id']."' AND `bom_id` != '".$bom_id."'";
        $row = $this->model['bom']->getRow($where);
        if($row) {
            echo json_encode($lang['error_bom_exists']);
        } else {
            echo json_encode("true");
        }
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
        $data['base_amount'] = $data['total_amount'] * $data['conversion_rate'];

        $bom_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);
        $data['document_id'] = $bom_id;

        $this->model['bom_detail'] = $this->load->model('production/bom_detail');
        foreach ($data['bom_details'] as $sort_order => $detail) {
            $detail['bom_id'] = $bom_id;
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['sort_order'] = $sort_order;
            $detail['document_currency_id'] = $data['document_currency_id'];
            $detail['base_currency_id'] = $data['base_currency_id'];
            $detail['conversion_rate'] = $data['conversion_rate'];
            $bom_detail_id=$this->model['bom_detail']->add($this->getAlias(), $detail);
        }
        return $bom_id;

    }

    protected function updateData($primary_key, $data) {
        $bom_id = $primary_key;

        $this->model[$this->getAlias()]->edit($this->getAlias(), $primary_key, $data);
        $this->model['product'] = $this->load->model('inventory/product');
        $products = $this->model['product']->getRows();
        foreach($products as $product) {
            $arrProducts[$product['product_id']] = array(
                'product_id' => $product['product_id'],
                'name' => $product['name'],
                'product_code' => $product['product_code'],
            );
        }

        $data['product_code'] = $arrProducts[$data['product_id']]['product_code'];
        $data['product_name'] = $arrProducts[$data['product_id']]['name'];

        $this->model['bom_detail'] = $this->load->model('production/bom_detail');
        $this->model['bom_detail']->deleteBulk($this->getAlias(), array('bom_id' => $bom_id));

        $this->model['bom_detail'] = $this->load->model('production/bom_detail');

        foreach ($data['bom_details'] as $sort_order => $detail) {
            $detail['bom_id'] = $bom_id;
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['sort_order'] = $sort_order;
            $detail['sort_order'] = $sort_order;
            $detail['product_code'] = $arrProducts[$detail['product_id']]['product_code'];
            $detail['product_name'] = $arrProducts[$detail['product_id']]['name'];

            $bom_detail_id = $this->model['bom_detail']->add($this->getAlias(), $detail);
        }

        return $bom_id;
    }

    protected function deleteData($primary_key) {
        $this->model['bom_detail'] = $this->load->model('production/bom_detail');
        $this->model['bom_detail']->deleteBulk($this->getAlias(),array('bom_id' => $primary_key));

        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);
    }

}
?>