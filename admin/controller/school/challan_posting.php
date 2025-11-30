<?php

class ControllerSchoolChallanPosting extends HController {

    protected $document_type_id = 34;

    protected function getAlias() {
        return 'school/challan_posting';
    }

    protected function getPrimaryKey() {
        return 'challan_posting_id';
    }

    protected function init()
    {
        $this->model[$this->getAlias()] = $this->load->model('school/fee_receipt');
        $this->data['lang'] = $this->load->language('school/challan_posting');
        $this->document->setTitle($this->data['lang']['heading_title']);
        $this->data['token'] = $this->session->data['token'];
    }

    public function index()
    {
        $this->redirect($this->url->link($this->getAlias() . '/update', 'token=' . $this->session->data['token'] . '&school_id=' . $this->session->data['school_id'], 'SSL'));
    }

    protected function getForm() {
        parent::getForm();

        $this->model['class'] = $this->load->model('school/class');
        $filter = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id']
        );
        $this->data['classes'] = $this->model['class']->getRows($filter, array('sort_order', 'class_name'));

        $this->model['fee'] = $this->load->model('school/fee');
        $filter = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
        );
        $this->data['fees'] = $this->model['fee']->getRows($filter, array('fee_name'));

        if (isset($this->request->get['challan_posting_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $result = $this->model[$this->getAlias()]->getRow(array('challan_posting_id' => $this->request->get['challan_posting_id']));
            foreach($result as $field => $value) {
                if($field == 'slot_date') {
                    $this->data[$field] = stdDate($value);
                } else {
                    $this->data[$field] = $value;
                }
            }
            $this->model['class_section'] = $this->load->model('school/class_section');
            $this->data['sections'] = $this->model['class_section']->getRows(array('class_id' => $result['class_id']));

            $this->model['challan_posting_detail'] = $this->load->model('school/challan_posting_detail');
            $template_details = $this->model['challan_posting_detail']->getRows(array('challan_posting_id' => $this->request->get['challan_posting_id']), array('sort_order'));
            foreach($template_details as $detail) {
                $detail['due_month'] = date('M Y', strtotime($detail['due_month']));
                $detail['fee_month'] = date('M Y', strtotime($detail['fee_month']));
                $this->data['template_details'][] = $detail;
            }
        }

        if (isset($this->request->get['clone_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $this->model['challan_posting_detail'] = $this->load->model('school/challan_posting_detail');
            $template_details = $this->model['challan_posting_detail']->getRows(array('challan_posting_id' => $this->request->get['clone_id']), array('sort_order'));
            foreach($template_details as $detail) {
                $detail['due_month'] = date('M Y', strtotime($detail['due_month']));
                $detail['fee_month'] = date('M Y', strtotime($detail['fee_month']));
                $this->data['template_details'][] = $detail;
            }
        }

        $this->data['href_get_class_section'] = $this->url->link($this->getAlias() . '/getClassSection', 'token=' . $this->session->data['token']);
        $this->data['href_validate_class_section'] = $this->url->link($this->getAlias() . '/validateClassSection', 'token=' . $this->session->data['token']);
        $this->data['strValidation']="{
            'rules':{
                'class_id': {'required':true},
                'class_section_id': {'required':true, 'remote':  {url: '" . $this->data['href_validate_class_section'] . "', type: 'post'}},
            },
        }";
        $this->response->setOutput($this->render());
    }

    public function getClassSection()
    {
        $class_id = $this->request->post['class_id'];
        $this->model['class_section'] = $this->load->model('school/class_section');
        $rows = $this->model['class_section']->getRows(array('class_id' => $class_id));
        $html = '<option value="">&nbsp;</option>';
        foreach($rows as $row) {
            $html .= '<option value="'.$row['class_section_id'].'">'.$row['section_name'].'</option>';
        }

        $json = array(
            'success' => true,
            'html' => $html
        );

        echo json_encode($json);
        exit;
    }

    public function validateClassSection() {
        $lang = $this->load->language($this->getAlias());
        $challan_posting_id = $this->request->get['challan_posting_id'];
        $class_section_id = $this->request->post['class_section_id'];
        $company_id = $this->session->data['company_id'];
        $company_branch_id = $this->session->data['company_branch_id'];
        $fiscal_year_id = $this->session->data['fiscal_year_id'];

        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        $where = "`company_id` = '".$company_id."' AND `company_branch_id` = '".$company_branch_id."' AND `fiscal_year_id` = '".$fiscal_year_id."' AND `class_section_id` = '".$class_section_id."' AND `challan_posting_id` != '".$challan_posting_id."'";
        $row = $this->model[$this->getAlias()]->getRow($where);
        if($row) {
            echo json_encode($lang['error_duplicate']);
        } else {
            echo json_encode("true");
        }
        exit;
    }

    protected function validateDelete() {
        $lang = $this->load->language($this->getAlias());
        if (!$this->user->hasPermission('delete', $this->getAlias())) {
            $this->error['warning'] = $lang['error_permission_delete'];
        }

        if(isset($this->request->post['selected'])) {
            $ids = $this->request->post['selected'];
        } elseif(isset($this->request->get['id'])) {
            $ids = array($this->request->get['id']);
        }

        $arrError = array();
        $this->model['fee'] = $this->load->model('fee/fee');
        $this->model['challan_posting_detail'] = $this->load->model('fee/challan_posting_detail');

        foreach($ids as $challan_posting_id) {
            $fee = $this->model['fee']->getRow(array('challan_posting_id'=>$challan_posting_id));
            $where = "challan_posting_id = '" . $challan_posting_id . "' AND (allotted = 'True' OR preregistration_identity != '')";
            $challan_posting_details = $this->model['challan_posting_detail']->getRows($where);
            if(count($challan_posting_details) > 0) {
                $arrError[] = sprintf($lang['error_delete_slot'],'Date:' . stdDate($fee['slot_date']). ', From:' . $fee['time_from'] . ', To:'.$fee['time_to'],count($challan_posting_details));
            }
        }
        if($arrError) {
            $strError = $this->data['lang']['error_delete'];
            $strError .= '<br /><b>' . implode('<br />', $arrError) . '</b>';
            $this->error['warning'] = $strError;
        }

        if (!$this->error) {
            return true;
        } else {
            $this->session->data['error'] = $this->error['warning'];
            return false;
        }
    }

    protected function insertData($data) {
        $this->model['challan_posting_detail'] = $this->load->model('school/challan_posting_detail');
        $details = $data['template_details'];
        unset($data['template_details']);

        $data['company_id'] = $this->session->data['company_id'];
        $data['company_branch_id'] = $this->session->data['company_branch_id'];
        $data['fiscal_year_id'] = $this->session->data['fiscal_year_id'];

        $challan_posting_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);
        foreach($details as $sort_order => $detail) {
            $detail['challan_posting_id'] = $challan_posting_id;
            $detail['company_id'] = $data['company_id'];
            $detail['company_branch_id'] = $data['company_branch_id'];
            $detail['fiscal_year_id'] = $data['fiscal_year_id'];
            $detail['class_id'] = $data['class_id'];
            $detail['class_section_id'] = $data['class_section_id'];
            $detail['sort_order'] = $sort_order;
            $detail['due_month'] = getFormatedDate('M Y',$detail['due_month'],'Y-m-01');
            $detail['fee_month'] = getFormatedDate('M Y',$detail['fee_month'],'Y-m-01');

            $this->model['challan_posting_detail']->add($this->getAlias(), $detail);
        }
        return $challan_posting_id;
    }

    protected function updateData($primary_key, $data) {
        $this->model['challan_posting_detail'] = $this->load->model('school/challan_posting_detail');
        $this->model['challan_posting_detail']->deleteBulk($this->getAlias(), array('challan_posting_id' => $primary_key));
        $details = $data['template_details'];
        unset($data['template_details']);

        $data['company_id'] = $this->session->data['company_id'];
        $data['company_branch_id'] = $this->session->data['company_branch_id'];
        $data['fiscal_year_id'] = $this->session->data['fiscal_year_id'];

        $challan_posting_id = $this->model[$this->getAlias()]->edit($this->getAlias(), $primary_key, $data);
        foreach($details as $sort_order => $detail) {
            $detail['challan_posting_id'] = $challan_posting_id;
            $detail['company_id'] = $data['company_id'];
            $detail['company_branch_id'] = $data['company_branch_id'];
            $detail['fiscal_year_id'] = $data['fiscal_year_id'];
            $detail['class_id'] = $data['class_id'];
            $detail['class_section_id'] = $data['class_section_id'];
            $detail['sort_order'] = $sort_order;
            $detail['due_month'] = getFormatedDate('M Y',$detail['due_month'],'Y-m-01');
            $detail['fee_month'] = getFormatedDate('M Y',$detail['fee_month'],'Y-m-01');

            $this->model['challan_posting_detail']->add($this->getAlias(), $detail);
        }

        return $challan_posting_id;
    }

    protected function deleteData($primary_key) {
        $this->model['challan_posting_detail'] = $this->load->model('fee/challan_posting_detail');
        $this->model['challan_posting_detail']->deleteBulk($this->getAlias(), array('challan_posting_id' => $primary_key));

        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);
    }

}

?>