<?php

class ControllerTravelTravelInvoice extends HController {

    protected $document_type_id = 34;

    protected function getAlias() {
        return 'travel/travel_invoice';
    }

    protected function getPrimaryKey() {
        return 'travel_invoice_id';
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
        $aColumns = array('action','booking_date', 'customer_booking_no', 'document_date', 'document_identity','created_at');

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
        //$arrWhere[] = "`company_id` = '".$this->session->data['company_id']."'";
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
                $strAction .= '<a '.(isset($action['btn_class'])?'class="'.$action['btn_class'].'"':'').' '.(isset($action['target'])?'target="'.$action['target'].'"':'').' href="' . $action['href'] . '" data-toggle="tooltip" title="' . $action['text'] . '" ' . (isset($action['click']) ? 'onClick="' . $action['click'] . '"' : '') . '>';
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

        $this->data['document_date'] = stdDate();
        $this->data['document_identity'] = $this->data['lang']['auto'];

        $this->model['member'] = $this->load->model('travel/member');
        $this->data['members'] = $this->model['member']->getRows();

        $this->model['destination'] = $this->load->model('travel/destination');
        $this->data['destinations'] = $this->model['destination']->getRows();

        $this->model['service'] = $this->load->model('travel/service');
        $this->data['services'] = $this->model['service']->getRows();

        $this->model['currency'] = $this->load->model('setup/currency');
        $this->data['currencys'] = $this->model['currency']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->data['base_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['base_currency'] = $this->session->data['base_currency_name'];
        $this->data['document_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['conversion_rate'] = "1.00";

        if (isset($this->request->get['travel_invoice_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $this->data['document_type_id'] = $this->document_type_id;
            $this->data['document_id'] = $this->request->get['travel_invoice_id'];

            $result = $this->model[$this->getAlias()]->getRow(array($this->getPrimaryKey() => $this->request->get[$this->getPrimaryKey()]));
            foreach($result as $field => $value) {
                if($field=='booking_date') {
                    $this->data[$field] = stdDate($value);
                } elseif($field=='document_date') {
                    $this->data[$field] = stdDate($value);
                } else {
                    $this->data[$field] = $value;
                }
            }

            $this->model['hotel'] = $this->load->model('travel/hotel');
            $hotels = $this->model['hotel']->getRows(array(), array('hotel_name'));
            foreach($hotels as $hotel) {
                $this->data['hotels'][$hotel['destination_id']][] = $hotel;
            }

            $this->model['hotel_room'] = $this->load->model('travel/hotel_room');
            $hotel_rooms = $this->model['hotel_room']->getRows(array(), array('hotel_id', 'sort_order'));
            foreach($hotel_rooms as $room) {
                $this->data['room_types'][$room['hotel_id']][] = $room;
            }

            $this->model['travel_invoice_accommodation'] = $this->load->model('travel/travel_invoice_accommodation');
            $this->model['travel_invoice_service'] = $this->load->model('travel/travel_invoice_service');

            $travel_accommodations = $this->model['travel_invoice_accommodation']->getRows(array('travel_invoice_id' => $result['travel_invoice_id']), array('sort_order'));
            foreach($travel_accommodations as $accommodation) {
                if($accommodation['check_in'] != '') {
                    $accommodation['check_in'] = stdDate($accommodation['check_in']);
                }
                if($accommodation['check_out'] != '') {
                    $accommodation['check_out'] = stdDate($accommodation['check_out']);
                }
                $this->data['travel_accommodations'][] = $accommodation;
            }

            $this->data['travel_services'] = $this->model['travel_invoice_service']->getRows(array('travel_invoice_id' => $result['travel_invoice_id']), array('sort_order'));
        }

        $this->data['href_get_member_pax'] = $this->url->link($this->getAlias() . '/getMemberPax', 'token=' . $this->session->data['token'] . '&travel_invoice_id=' . $this->request->get['travel_invoice_id']);
        $this->data['href_get_hotel'] = $this->url->link($this->getAlias() . '/getHotel', 'token=' . $this->session->data['token'] . '&travel_invoice_id=' . $this->request->get['travel_invoice_id']);
        $this->data['href_get_room_type'] = $this->url->link($this->getAlias() . '/getRoomType', 'token=' . $this->session->data['token'] . '&travel_invoice_id=' . $this->request->get['travel_invoice_id']);
        $this->data['href_get_night'] = $this->url->link($this->getAlias() . '/getNight', 'token=' . $this->session->data['token'] . '&travel_invoice_id=' . $this->request->get['travel_invoice_id']);

        $this->data['action_post'] = $this->url->link($this->getAlias() . '/post', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_print'] = $this->url->link($this->getAlias() . '/printDocument', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_validate_name'] = $this->url->link($this->getAlias() . '/validateName', 'token=' . $this->session->data['token'] . '&travel_invoice_id=' . $this->request->get['travel_invoice_id']);
        $this->data['strValidation']="{
            'rules':{
                'travel_invoice_name': {'required':true, 'minlength': 3, 'remote':  {url: '" . $this->data['action_validate_name'] . "', type: 'post'}},
            },
        }";

        $this->response->setOutput($this->render());
    }

    public function validateName()
    {
        $travel_invoice_name = $this->request->post['travel_invoice_name'];
        $travel_invoice_id = $this->request->get['travel_invoice_id'];

        $this->load->language('travel/travel_invoice');
        if ($travel_invoice_name) {
            $this->model['travel_invoice'] = $this->load->model('travel/travel_invoice');
            $arrWhere = array();
            //$arrWhere[] = "`company_id` = '".$company_id."'";
            $arrWhere[] = "LOWER(`travel_invoice_name`) = '".$travel_invoice_name."'";
            $arrWhere[] = "`travel_invoice_id` != '".$travel_invoice_id."'";
            $where = implode(' AND ', $arrWhere);
            $row = $this->model['travel_invoice']->getRow($where);
            if ($row) {
                echo json_encode($this->language->get('error_duplicate_name'));
            } else {
                echo json_encode("true");
            }
        } else {
            echo json_encode($this->language->get('error_name'));
        }
        exit;
    }

    public function getMemberPax() {
        $member_id = $this->request->post['member_id'];

        $this->model['member_pax'] = $this->load->model('travel/member_pax');
        $members = $this->model['member_pax']->getRows(array('member_id' => $member_id));

        $arrMemberPax['Infant'] = 0;
        $arrMemberPax['Child'] = 0;
        $arrMemberPax['Adult'] = 0;
        $arrMemberPax['Total'] = 0;
        foreach($members as $member) {
            $arrMemberPax[$member['pax']] += 1;
            $arrMemberPax['Total'] += 1;
        }

        $json = array(
            'success' => true,
            'post' => $this->request->post,
            'member_pax' => $arrMemberPax
        );

        echo json_encode($json);
    }

    public function getHotel() {
        $destination_id = $this->request->post['destination_id'];

        $this->model['hotel'] = $this->load->model('travel/hotel');
        $hotels = $this->model['hotel']->getRows(array('destination_id' => $destination_id));

        $html='<option value="">&nbsp;</option>';
        foreach($hotels as $hotel) {
            $html .= '<option value="'.$hotel['hotel_id'].'">'.$hotel['hotel_name'].'</option>';
        }

        $json = array(
            'success' => true,
            'html' => $html
        );

        echo json_encode($json);
    }

    public function getRoomType() {
        $hotel_id = $this->request->post['hotel_id'];

        $this->model['hotel_room'] = $this->load->model('travel/hotel_room');
        $room_types = $this->model['hotel_room']->getRows(array('hotel_id' => $hotel_id));

        $html='<option value="">&nbsp;</option>';
        foreach($room_types as $room_type) {
            $html .= '<option data-hotel_room_id="'.$room_type['hotel_room_id'].'" data-room_charges="'.$room_type['room_charges'].'" value="'.$room_type['room_type_id'].'">'.$room_type['room_type'].'</option>';
        }

        $json = array(
            'success' => true,
            'room_types' => $room_types,
            'html' => $html
        );

        echo json_encode($json);
    }

    public function getNight() {
        $check_in_date = $this->request->post['check_in_date'];
        $check_out_date = $this->request->post['check_out_date'];

        if($check_in_date=='' || $check_out_date=='') {
            $night = 0;
        } elseif(validateDate(STD_DATE, $check_in_date) && validateDate(STD_DATE, $check_out_date)) {
            $dStart = new DateTime(MySqlDate($check_in_date));
            $dEnd  = new DateTime(MySqlDate($check_out_date));
            $dDiff = $dStart->diff($dEnd);
            //echo $dDiff->format('%R'); // use for point out relation: smaller/greater
            //echo $dDiff->days;
            $night = $dDiff->days;
        } else {
            $night = 0;
        }
        $json = array(
            'success' => true,
            'night' => $night,
        );

        echo json_encode($json);
    }

    protected function insertData($data) {
        $this->model['module_setting'] = $this->load->model('common/setting');
        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['document'] = $this->load->model('common/document');
        $this->model['document_type'] = $this->load->model('common/document_type');
        $document = $this->model['document_type']->getNextDocument($this->document_type_id);

        $data['document_type_id'] = $this->document_type_id;
        $data['document_prefix'] = $document['document_prefix'];
        $data['document_no'] = $document['document_no'];
        $data['document_identity'] = $document['document_identity'];

        $data['company_id'] = $this->session->data['company_id'];
        $data['company_branch_id'] = $this->session->data['company_branch_id'];
        $data['fiscal_year_id'] = $this->session->data['fiscal_year_id'];

        if($data['document_date'] != '') {
            $data['document_date'] = MySqlDate($data['document_date']);
        }
        if($data['booking_date'] != '') {
            $data['booking_date'] = MySqlDate($data['booking_date']);
        }

        $partner = $this->model['partner']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'partner_id' => $data['partner_id'],
        ));

        $travel_invoice_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);
        $this->model['travel_invoice_accommodation'] = $this->load->model('travel/travel_invoice_accommodation');
        $this->model['travel_invoice_service'] = $this->load->model('travel/travel_invoice_service');

        foreach($data['travel_accommodations'] as $sort_order => $accommodation) {
            $accommodation['travel_invoice_id'] = $travel_invoice_id;
            $accommodation['sort_order'] = $sort_order;
            if($accommodation['check_in'] != '') {
                $accommodation['check_in'] = MySqlDate($accommodation['check_in']);
            }
            if($accommodation['check_out'] != '') {
                $accommodation['check_out'] = MySqlDate($accommodation['check_out']);
            }

            $this->model['travel_invoice_accommodation']->add($this->getAlias(), $accommodation);
        }

        foreach($data['travel_services'] as $sort_order => $service) {
            $service['travel_invoice_id'] = $travel_invoice_id;
            $service['sort_order'] = $sort_order;

            $this->model['travel_invoice_service']->add($this->getAlias(), $service);
        }

        $insert_document = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'document_type_id' => $this->document_type_id,
            'document_id' => $travel_invoice_id,
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
        $data['document_id'] = $this->model['document']->add($this->getAlias(), $insert_document);

        $mapping = $this->model['module_setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            //'company_branch_id' => $this->session->data['company_branch_id'],
            //'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'travel',
            'field' => 'revenue_account_id',
        ));

        $gl_data = array();
        $gl_data[] = array(
            'coa_id' => $partner['outstanding_account_id'],
            'partner_type_id' => $data['partner_type_id'],
            'partner_id' => $data['partner_id'],
            'ref_document_type_id' => $this->document_type_id,
            'ref_document_identity' => $data['document_identity'],
            'document_debit' => $data['net_amount'],
            'document_credit' => 0,
            'debit' => $data['base_amount'],
            'credit' => 0,
        );
        $gl_data[] = array(
            'coa_id' => $mapping['value'],
            'partner_type_id' => $data['partner_type_id'],
            'partner_id' => $data['partner_id'],
            'document_credit' => $data['net_amount'],
            'document_debit' => 0,
            'credit' => $data['base_amount'],
            'debit' => 0,
        );

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
        return $travel_invoice_id;
    }

    protected function updateData($primary_key, $data) {
        //d($data, true);
        $this->model['module_setting'] = $this->load->model('common/setting');
        $this->model['document'] = $this->load->model('common/document');
        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['travel_invoice_accommodation'] = $this->load->model('travel/travel_invoice_accommodation');
        $this->model['travel_invoice_service'] = $this->load->model('travel/travel_invoice_service');

        $this->model['travel_invoice_service']->deleteBulk($this->getAlias(), array('travel_invoice_id' => $primary_key));
        $this->model['travel_invoice_accommodation']->deleteBulk($this->getAlias(), array('travel_invoice_id' => $primary_key));
        $this->model['ledger']->deleteBulk($this->getAlias(), array('document_id' => $primary_key));
        $this->model['document']->delete($this->getAlias(), $primary_key);

        if($data['document_date'] != '') {
            $data['document_date'] = MySqlDate($data['document_date']);
        }
        if($data['booking_date'] != '') {
            $data['booking_date'] = MySqlDate($data['booking_date']);
        }

        $travel_invoice_id = $this->model[$this->getAlias()]->edit($this->getAlias(), $primary_key, $data);

        foreach($data['travel_accommodations'] as $sort_order => $accommodation) {
            $accommodation['travel_invoice_id'] = $travel_invoice_id;
            $accommodation['sort_order'] = $sort_order;
            if($accommodation['check_in'] != '') {
                $accommodation['check_in'] = MySqlDate($accommodation['check_in']);
            }
            if($accommodation['check_out'] != '') {
                $accommodation['check_out'] = MySqlDate($accommodation['check_out']);
            }

            $this->model['travel_invoice_accommodation']->add($this->getAlias(), $accommodation);
        }

        foreach($data['travel_services'] as $sort_order => $service) {
            $service['travel_invoice_id'] = $travel_invoice_id;
            $service['sort_order'] = $sort_order;

            $this->model['travel_invoice_service']->add($this->getAlias(), $service);
        }

        $insert_document = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'document_type_id' => $this->document_type_id,
            'document_id' => $travel_invoice_id,
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
        $data['document_id'] = $this->model['document']->add($this->getAlias(), $insert_document);

        $partner = $this->model['partner']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'partner_id' => $data['partner_id'],
        ));

        $mapping = $this->model['module_setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            //'company_branch_id' => $this->session->data['company_branch_id'],
            //'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'travel',
            'field' => 'revenue_account_id',
        ));

        $gl_data = array();
        $gl_data[] = array(
            'coa_id' => $partner['outstanding_account_id'],
            'partner_type_id' => $data['partner_type_id'],
            'partner_id' => $data['partner_id'],
            'ref_document_type_id' => $this->document_type_id,
            'ref_document_identity' => $data['document_identity'],
            'document_debit' => $data['net_amount'],
            'document_credit' => 0,
            'debit' => $data['base_amount'],
            'credit' => 0,
        );
        $gl_data[] = array(
            'coa_id' => $mapping['value'],
            'partner_type_id' => $data['partner_type_id'],
            'partner_id' => $data['partner_id'],
            'document_credit' => $data['net_amount'],
            'document_debit' => 0,
            'credit' => $data['base_amount'],
            'debit' => 0,
        );

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

        return $travel_invoice_id;
    }

    protected function deleteData($primary_key) {
        $this->model['travel_invoice_accommodation'] = $this->load->model('travel/travel_invoice_accommodation');
        $this->model['travel_invoice_service'] = $this->load->model('travel/travel_invoice_service');
        $this->model['document'] = $this->load->model('common/document');
        $this->model['ledger'] = $this->load->model('gl/ledger');

        $this->model['travel_invoice_service']->deleteBulk($this->getAlias(), array('travel_invoice_id' => $primary_key));
        $this->model['travel_invoice_accommodation']->deleteBulk($this->getAlias(), array('travel_invoice_id' => $primary_key));
        $this->model['ledger']->deleteBulk($this->getAlias(), array('document_type_id' => $this->document_type_id, 'document_id' => $primary_key));
        $this->model['document']->delete($this->getAlias(), $primary_key);

        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);
    }

    public function printDocument() {
        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        //d(array('session' => $this->session->data, 'post' => $this->request->post, 'get' => $this->request->get), true);
        $lang = $this->load->language($this->getAlias());
        $post = $this->request->post;
        $session = $this->session->data;
        $travel_invoice_id = $this->request->get['travel_invoice_id'];

        $this->model['travel_invoice'] = $this->load->model('travel/travel_invoice');
        $this->model['travel_invoice_accommodation'] = $this->load->model('travel/travel_invoice_accommodation');
        $this->model['travel_invoice_service'] = $this->load->model('travel/travel_invoice_service');

        $invoice = $this->model['travel_invoice']->getRow(array('travel_invoice_id' => $travel_invoice_id));
        $accommodations = $this->model['travel_invoice_accommodation']->getRows(array('travel_invoice_id' => $travel_invoice_id), array('sort_order'));
        $services = $this->model['travel_invoice_service']->getRows(array('travel_invoice_id' => $travel_invoice_id), array('sort_order'));

        $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Travel Invoice');
        $pdf->SetSubject('Travel Invoice');

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $session['company_image']
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(15, 35, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set font
        $pdf->SetFont('times', '', 10);

        // add a page
        $pdf->AddPage();

        $pdf->Cell(25, 8, $lang['document_date'] .':', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 8, stdDate($invoice['document_date']), 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(55, 8, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(40, 8, $lang['document_no'] .':', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(40, 8, $invoice['document_identity'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->ln(8);
        $pdf->Cell(25, 8, $lang['booking_date'] .':', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 8, empty($invoice['booking_date'])?'':stdDate($invoice['booking_date']), 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(55, 8, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(40, 8, $lang['booking_no'] .':', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(40, 8, $invoice['customer_booking_no'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->ln(8);
        $pdf->Cell(25, 8, $lang['member'] .':', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(75, 8, $invoice['partner_name'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->ln(8);

        $pdf->SetFillColor(215, 235, 255);
        $pdf->SetTextColor(127, 31, 0);
        $pdf->Cell(188, 8, $lang['no_of_pax'], 1, false, 'C', 1);
        $pdf->ln(8);
        $pdf->SetFillColor(215, 215, 215);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(47, 8, $lang['adult'], 1, false, 'C', 1);
        $pdf->Cell(47, 8, $lang['child'], 1, false, 'C', 1);
        $pdf->Cell(47, 8, $lang['infant'], 1, false, 'C', 1);
        $pdf->Cell(47, 8, $lang['total'], 1, false, 'C', 1);
        $pdf->ln(8);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(47, 8, $invoice['visa_qty_adult'], 1, false, 'C', 1);
        $pdf->Cell(47, 8, $invoice['visa_qty_child'], 1, false, 'C', 1);
        $pdf->Cell(47, 8, $invoice['visa_qty_infant'], 1, false, 'C', 1);
        $pdf->Cell(47, 8, $invoice['visa_qty_total'], 1, false, 'C', 1);
        $pdf->ln(16);

        // set font
        $pdf->SetFont('times', '', 8);

        $pdf->SetFillColor(215, 235, 255);
        $pdf->SetTextColor(127, 31, 0);
        $pdf->Cell(188, 8, $lang['accommodation'], 1, false, 'C', 1);
        $pdf->ln(8);
        $pdf->SetFillColor(215, 215, 215);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(16, 8, $lang['destination'], 1, false, 'C', 1);
        $pdf->Cell(56, 8, $lang['hotel_name'], 1, false, 'C', 1);
        $pdf->Cell(16, 8, $lang['check_in'], 1, false, 'C', 1);
        $pdf->Cell(16, 8, $lang['check_out'], 1, false, 'C', 1);
        $pdf->Cell(10, 8, $lang['nights'], 1, false, 'C', 1);
        $pdf->Cell(18, 8, $lang['room_type'], 1, false, 'C', 1);
        $pdf->Cell(18, 8, $lang['room_charges'], 1, false, 'C', 1);
        $pdf->Cell(18, 8, $lang['room_qty'], 1, false, 'C', 1);
        $pdf->Cell(20, 8, $lang['room_amount'], 1, false, 'C', 1);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        $total_room_amount = 0;
        foreach($accommodations as $accommodation) {
            $pdf->ln(8);
            $pdf->Cell(16, 8, $accommodation['destination_name'], 1, false, 'L', 1);
            $pdf->Cell(56, 8, $accommodation['hotel_name'], 1, false, 'L', 1);
            $pdf->Cell(16, 8, stdDate($accommodation['check_in']), 1, false, 'C', 1);
            $pdf->Cell(16, 8, stdDate($accommodation['check_out']), 1, false, 'C', 1);
            $pdf->Cell(10, 8, $accommodation['nights'], 1, false, 'C', 1);
            $pdf->Cell(18, 8, $accommodation['room_type'], 1, false, 'l', 1);
            $pdf->Cell(18, 8, $accommodation['room_charges'], 1, false, 'R', 1);
            $pdf->Cell(18, 8, $accommodation['room_qty'], 1, false, 'C', 1);
            $pdf->Cell(20, 8, $accommodation['room_amount'], 1, false, 'R', 1);

            $total_room_amount += $accommodation['room_amount'];
        }
        $pdf->ln(8);
        $pdf->SetFont('times', 'B', 8);
        $pdf->Cell(168, 8, '', 1, false, 'R', 1);
        $pdf->Cell(20, 8, number_format($total_room_amount,2), 1, false, 'R', 1);
        $pdf->ln(16);

        $pdf->SetFont('times', '', 8);
        $pdf->SetFillColor(215, 235, 255);
        $pdf->SetTextColor(127, 31, 0);
        $pdf->Cell(188, 8, $lang['services'], 1, false, 'C', 1);
        $pdf->ln(8);
        $pdf->SetFillColor(215, 215, 215);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(88, 8, $lang['service_name'], 1, false, 'C', 1);
        $pdf->Cell(25, 8, $lang['adult'], 1, false, 'C', 1);
        $pdf->Cell(25, 8, $lang['child'], 1, false, 'C', 1);
        $pdf->Cell(25, 8, $lang['infant'], 1, false, 'C', 1);
        $pdf->Cell(25, 8, $lang['total'], 1, false, 'C', 1);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        $total_adult_charges=0;
        $total_child_charges=0;
        $total_infant_charges=0;
        $total_service_charges=0;
        foreach($services as $service) {
            $pdf->ln(8);
            $pdf->Cell(88, 8, $service['service_name'], 1, false, 'L', 1);
            $pdf->Cell(25, 8, number_format($service['adult_charges'],2), 1, false, 'R', 1);
            $pdf->Cell(25, 8, number_format($service['child_charges'],2), 1, false, 'R', 1);
            $pdf->Cell(25, 8, number_format($service['infant_charges'],2), 1, false, 'R', 1);
            $pdf->Cell(25, 8, number_format($service['total_charges'],2), 1, false, 'R', 1);

            $total_adult_charges += $service['adult_charges'];
            $total_child_charges += $service['child_charges'];
            $total_infant_charges += $service['infant_charges'];
            $total_service_charges += $service['total_charges'];
        }
        $pdf->ln(8);
        $pdf->SetFont('times', 'B', 8);
        $pdf->Cell(88, 8, '', 1, false, 'R', 1);
        $pdf->Cell(25, 8, number_format($total_adult_charges,2), 1, false, 'R', 1);
        $pdf->Cell(25, 8, number_format($total_child_charges,2), 1, false, 'R', 1);
        $pdf->Cell(25, 8, number_format($total_infant_charges,2), 1, false, 'R', 1);
        $pdf->Cell(25, 8, number_format($total_service_charges,2), 1, false, 'R', 1);
        $pdf->ln(8);
        $pdf->SetFont('times', '', 10);
        $html = '';
        $html .= '<table border="0">';
        $html .= '  <tr>';
        $html .= '      <td width="60%">';
        $html .= '          <table border="0">';
        $html .= '              <tr>';
        $html .= '                  <td><strong>'.$lang['booking_note'].":</strong><br />".str_replace("\n","<br  />",$invoice['booking_note']).'</td>';
        $html .= '              </tr>';
        $html .= '              <tr>';
        $html .= '                  <td>&nbsp;</td>';
        $html .= '              </tr>';
        $html .= '              <tr>';
        $html .= '                  <td><strong>'.$lang['voucher_note'].":</strong><br />".str_replace("\n","<br  />",$invoice['voucher_note']).'</td>';
        $html .= '              </tr>';
        $html .= '          </table>';
        $html .= '      </td>';
        $html .= '      <td width="40%">';
        $html .= '          <table border="0" cellspacing="7">';
        $html .= '              <tr>';
        $html .= '                  <td align="right">'.$lang['gross_amount'].':</td>';
        $html .= '                  <td align="right">'.number_format($invoice['gross_amount'],2).'</td>';
        $html .= '              </tr>';
        $html .= '              <tr>';
        $html .= '                  <td align="right">'.$lang['adjustment_amount'].'</td>';
        $html .= '                  <td align="right">'.number_format($invoice['adjustment_amount'],2).'</td>';
        $html .= '              </tr>';
        $html .= '              <tr>';
        $html .= '                  <td align="right">'.$lang['net_amount'].' ('.$invoice['currency_code'].')'.':</td>';
        $html .= '                  <td align="right">'.number_format($invoice['net_amount'],2).'</td>';
        $html .= '              </tr>';
        $html .= '              <tr>';
        $html .= '                  <td align="right">'.$lang['currency_rate'].':</td>';
        $html .= '                  <td align="right">'.number_format($invoice['conversion_rate'],2).'</td>';
        $html .= '              </tr>';
        $html .= '              <tr>';
        $html .= '                  <td align="right">'.$lang['invoice_amount'].':</td>';
        $html .= '                  <td align="right">'.number_format($invoice['base_amount'],2).'</td>';
        $html .= '              </tr>';
        $html .= '          </table>';
        $html .= '      </td>';
        $html .= '  </tr>';
        $html .= '</table>';
        $pdf->writeHTML($html);


        //Close and output PDF document
        $pdf->Output('Travel Invoice - '.$invoice['document_identity'].'.pdf', 'I');

    }

}

class PDF extends TCPDF {
    public $data = array();

    //Page header
    public function Header() {
        // Logo
        if($this->data['company_logo'] != '') {
            $image_file = DIR_IMAGE.$this->data['company_logo'];
            //$this->Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);
            $this->Image($image_file, 10, 10, 30, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        // Set font
        $this->SetFont('helvetica', 'B', 20);
        $this->Ln(2);
        // Title
        $this->Cell(0, 10, $this->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);
        $this->Cell(0, 10, $this->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
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

?>