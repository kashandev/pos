<?php

class ControllerSetupSupplier extends HController {

    protected function getAlias() {
        return 'setup/supplier';
    }

    protected function getPrimaryKey() {
        return 'supplier_id';
    }

    protected function getList() {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['href_export_excel'] = $this->url->link($this->getAlias() . '/exportDataExcel', 'token=' . $this->session->data['token'].$url, 'SSL');

        $this->response->setOutput($this->render());
    }

    public function exportDataExcel()
    {
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        $results = $this->model[$this->getAlias()]->getRows();
        // d($results,true);

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $rowCount = 1;
        $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowCount.':J'.$rowCount);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->getAlignment()->applyFromArray(
            array('font' => array(
                'bold' => true,
                'size' => 14,
            ),'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
        );
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount,'Supplier List')->getStyle('A'.$rowCount)->getFont()->setBold( true )->setSize(14);
        $rowCount++;
        
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Supplier Name')->getStyle('A'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'Address')->getStyle('B'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Email')->getStyle('C'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'Mobile')->getStyle('D'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'Phone')->getStyle('E'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'GST No.')->getStyle('F'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, 'NTN No.')->getStyle('G'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, 'Currency')->getStyle('H'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, 'Created at')->getStyle('I'.$rowCount)->getFont()->setBold( true );

        $rowCount++;

        // d($results,true);
        foreach($results as $key => $detail) {
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $detail['name']);
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $detail['address']);
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $detail['email']);
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $detail['mobile']);
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $detail['phone']);
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $detail['gst_no']);
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $detail['ntn_no']);
            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $detail['currency']);
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $detail['created_at']);
            
            $rowCount++;

            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="Supplier List.xlsx"');
            header('Cache-Control: max-age=0');
            //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
            //$objWriter->save('some_excel_file.xlsx');
            $objWriter->save('php://output');
            exit;
    }

    public function getAjaxLists() {

        $this->load->language('setup/supplier');
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        $data = array();
        $aColumns = array('action', 'name', 'address', 'email','mobile', 'phone','gst_no','ntn_no','currency', 'created_at','check_box');

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
                'text' => $this->data['lang']['edit'],
                'href' => $this->url->link($this->getAlias() . '/update', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $aRow[$this->getPrimaryKey()], 'SSL'),
                'btn_class' => 'btn btn-primary btn-xs',
                'class' => 'fa fa-pencil'
            );

            $actions[] = array(
                'text' => $this->data['lang']['delete'],
                'href' => 'javascript:void(0);',
                'click' => "ConfirmDelete('" . $this->url->link($this->getAlias() . '/delete', 'token=' . $this->session->data['token'] . '&id=' . $aRow[$this->getPrimaryKey()], 'SSL') . "')",
                'btn_class' => 'btn btn-danger btn-xs',
                'class' => 'fa fa-times'
            );


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

        if (isset($this->request->get['supplier_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $result = $this->model[$this->getAlias()]->getRow(array($this->getPrimaryKey() => $this->request->get[$this->getPrimaryKey()]));
            foreach ($result as $field => $value) {
                if ($field == 'date_from' || $field == 'date_to') {
                    $this->data[$field] = stdDate($value);
                } else {
                    $this->data[$field] = $value;
                }
            }
        }

//        $this->model['mapping_account'] = $this->load->model('gl/mapping_coa');
//        $this->data['cash_accounts'] = $this->model['mapping_account']->getRows(array('company_id' => $this->session->data['company_id'], 'mapping_type_code' => 'CA'));
//        $this->data['outstanding_accounts'] = $this->model['mapping_account']->getRows(array('company_id' => $this->session->data['company_id'], 'mapping_type_code' => 'AP'));
//        $this->data['advance_accounts'] = $this->model['mapping_account']->getRows(array('company_id' => $this->session->data['company_id'], 'mapping_type_code' => 'ADP'));

        $this->model['coa'] = $this->load->model('gl/coa_level3');
        $this->data['coas'] = $this->model['coa']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['currency'] = $this->load->model('setup/currency');
        $this->data['currencies'] = $this->model['currency']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['partner_category'] = $this->load->model('setup/partner_category');
        $this->data['partner_categories'] = $this->model['partner_category']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->data['action_validate_name'] = $this->url->link($this->getAlias() . '/validateName', 'token=' . $this->session->data['token'] . '&supplier_id=' . $this->request->get['supplier_id']);



        $this->data['strValidation']="{
            'rules':{
                'name': {'required':true, 'minlength': 3, 'remote':  {url: '" . $this->data['action_validate_name'] . "', type: 'post'}},
		        'cash_account_id': {'required':true},
		        'outstanding_account_id': {'required': true},
    		    'advance_account_id': {'required': true},
             },
		     'ignore':[]
          }";

        $this->response->setOutput($this->render());
    }

    public function validateName()
    {
        $name = $this->request->post['name'];
        $company_id = $this->session->data['company_id'];
        $company_branch_id = $this->session->data['company_branch_id'];
        $supplier_id = $this->request->get['supplier_id'];

        $this->load->language('setup/supplier');
        if ($name) {
            $this->model['supplier'] = $this->load->model('setup/supplier');
            $where = "company_id='" . $company_id . "'";
            $where .= " AND company_branch_id='" . $company_branch_id . "'";
            $where .= " AND LOWER(name) = '".strtolower($name)."' AND supplier_id != '".$supplier_id."'";
            $coa = $this->model['supplier']->getRow($where);
            if ($coa) {
                echo json_encode($this->language->get('error_duplicate_name'));
            } else {
                echo json_encode("true");
            }
        } else {
            echo json_encode($this->language->get('error_name'));
        }
        exit;
    }

    protected function insertData($data) {
        $data['company_id'] = $this->session->data['company_id'];
        $data['company_branch_id'] = $this->session->data['company_branch_id'];
        $partner_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);

        $this->model['partner'] = $this->load->model('common/partner');
        $partner = $data;
        $partner['partner_type_id'] = 1;
        $partner['partner_type'] = 'Supplier';
        $partner['partner_id'] = $partner_id;
        $this->model['partner']->add($this->getAlias(), $partner);

        return $partner_id;
    }

    protected function updateData($primary_key, $data) {
        $partner_id = $this->model[$this->getAlias()]->edit($this->getAlias(), $primary_key, $data);

        $this->model['partner'] = $this->load->model('common/partner');
        $filter = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'partner_id' => $partner_id
        );
        $partner = $this->model['partner']->getRow($filter);
        $data['partner_type_id'] = 1;
        $data['partner_type'] = 'Supplier';
        $data['partner_id'] = $partner_id;
        if(empty($partner)) {
            $this->model['partner']->add($this->getAlias(), $data);
        } else {
            $this->model['partner']->edit($this->getAlias(), $primary_key, $data);
        }

        return $partner_id;
    }

    protected function deleteData($primary_key) {
        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);
        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['partner']->delete($this->getAlias(), $primary_key);
    }

     protected function validateDelete() {
            $error = array();
            if (!$this->user->hasPermission('delete', $this->getAlias())) {
                $this->error['warning'] = $this->language->get('error_permission_delete');
            }

            if (!$this->error) {
                if(isset($this->request->post['selected'])) {
                    $ids = $this->request->post['selected'];
                } else {
                    $ids = array($this->request->get['id']);
                }
                $this->model['core_document'] = $this->load->model('common/document');
                $this->model['partner'] = $this->load->model('common/partner');
                $lang = $this->load->language('setup/supplier');
                foreach($ids as $id) {
                    $count = $this->model['core_document']->getCount(array('company_id' => $this->session->data['company_id'], 'partner_id' => $id));
                    if($count > 0) {
                        $partner = $this->model['partner']->getRow(array('company_id' => $this->session->data['company_id'], 'partner_id' => $id));
                        $error[] = sprintf($lang['error_delete'].' '.$partner['name'].'. Document exists in Database! ', $partner['name'], $count);
                    }
                }

                $this->error = implode('<br />', $error);
            }

            if (!$this->error) {
                return true;
            } else {
                $this->session->data['error_warning'] = $this->error;
                return false;
            }
        }

}

?>