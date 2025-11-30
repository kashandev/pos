<?php

class ControllerTravelHotel extends HController {

    protected function validateDocument() {
        return false;
    }

    protected function getAlias() {
        return 'travel/hotel';
    }

    protected function getPrimaryKey() {
        return 'hotel_id';
    }

    protected function getList() {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }

    public function getAjaxLists() {
        $this->load->language('travel/hotel');
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        $data = array();
        $aColumns = array('action','destination_name', 'hotel_name','created_at');

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

        $this->model['destination'] = $this->load->model('travel/destination');
        $this->data['destinations'] = $this->model['destination']->getRows();

        $this->model['room_type'] = $this->load->model('travel/room_type');
        $this->data['room_types'] = $this->model['room_type']->getRows();

        $this->data['total_rooms'] = 0;
        $this->data['avg_meal_charges'] = 0;

        if (isset($this->request->get['hotel_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $result = $this->model[$this->getAlias()]->getRow(array($this->getPrimaryKey() => $this->request->get[$this->getPrimaryKey()]));
            foreach($result as $field => $value) {
                $this->data[$field] = $value;
            }

            $this->model['hotel_room'] = $this->load->model('travel/hotel_room');
            $this->data['hotel_rooms'] = $this->model['hotel_room']->getRows(array('hotel_id' => $result['hotel_id']), array('sort_order'));
        }

        $this->data['href_send_registration_code'] = $this->url->link($this->getAlias() . '/sendRegistrationCode', 'token=' . $this->session->data['token'] . '&hotel_id=' . $this->request->get['hotel_id']);
        $this->data['action_validate_name'] = $this->url->link($this->getAlias() . '/validateName', 'token=' . $this->session->data['token'] . '&hotel_id=' . $this->request->get['hotel_id']);
        $this->data['strValidation']="{
            'rules':{
                'destination_id': {'required':true},
                'hotel_name': {'required':true, 'minlength': 3, 'remote':  {url: '" . $this->data['action_validate_name'] . "', type: 'post'}},
                'total_rooms': {'required':true, 'min': 1},
            },
            messages: {
                'total_rooms': 'Add Minimum 1 Room',
            },
            ignore: [],
        }";

        $this->response->setOutput($this->render());
    }

    public function validateName()
    {
        $hotel_name = $this->request->post['hotel_name'];
        $hotel_id = $this->request->get['hotel_id'];

        $this->load->language('travel/hotel');
        if ($hotel_name) {
            $this->model['hotel'] = $this->load->model('travel/hotel');
            $arrWhere = array();
            //$arrWhere[] = "`company_id` = '".$company_id."'";
            $arrWhere[] = "LOWER(`hotel_name`) = '".$hotel_name."'";
            $arrWhere[] = "`hotel_id` != '".$hotel_id."'";
            $where = implode(' AND ', $arrWhere);
            $row = $this->model['hotel']->getRow($where);
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

    protected function insertData($data) {
        $hotel_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);
        $this->model['hotel_room'] = $this->load->model('travel/hotel_room');
        foreach($data['hotel_rooms'] as $sort_order => $roomData) {
            $roomData['hotel_id'] = $hotel_id;
            $roomData['destination_id'] = $data['destination_id'];
            $roomData['sort_order'] = $sort_order;

            $this->model['hotel_room']->add($this->getAlias(), $roomData);
        }
        return $hotel_id;
    }

    protected function updateData($primary_key, $data) {
        $this->model['hotel_room'] = $this->load->model('travel/hotel_room');
        $this->model['hotel_room']->deleteBulk($this->getAlias(), array('hotel_id' => $primary_key));
        foreach($data['hotel_rooms'] as $sort_order => $roomData) {
            $roomData['hotel_id'] = $primary_key;
            $roomData['destination_id'] = $data['destination_id'];
            $roomData['sort_order'] = $sort_order;

            $this->model['hotel_room']->add($this->getAlias(), $roomData);
        }
        $this->model[$this->getAlias()]->edit($this->getAlias(), $primary_key, $data);
        return $primary_key;
    }

    protected function deleteData($primary_key) {
        $this->model['hotel_room'] = $this->load->model('travel/hotel_room');
        $this->model['hotel_room']->deleteBulk($this->getAlias(), array('hotel_id' => $primary_key));
        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);
    }

}

?>