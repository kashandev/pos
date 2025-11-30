<?php

class ControllerGLProfitAndLossMapping extends HController
{

    protected function getAlias()
    {
        return 'gl/profit_and_loss_mapping';
    }

    protected function getPrimaryKey()
    {
        return 'setting_id';
    }

    protected function init()
    {
        $this->model[$this->getAlias()] = $this->load->model('common/setting');
        $this->data['lang'] = $this->load->language('gl/profit_and_loss_mapping');
        $this->document->setTitle($this->data['lang']['heading_title']);
        $this->data['token'] = $this->session->data['token'];
    }

    public function index()
    {
        $this->redirect($this->url->link($this->getAlias() . '/update', 'token=' . $this->session->data['token'] . '&school_id=' . $this->session->data['school_id'], 'SSL'));
    }

    protected function getForm()
    {
        parent::getForm();

        $filter = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'profit_and_loss',
        );
        $this->model['setting'] = $this->load->model('common/setting');
        $results = $this->model['setting']->getRows($filter);
        foreach ($results as $result) {

            if($result['field']=='sale_revenue_account_id') {
                $this->data[$result['field']][$result['value']] = $result['value'];
            } else if($result['field']=='sale_return_and_discount_account_id') {
                $this->data[$result['field']][$result['value']] = $result['value'];
            }  else if($result['field']=='cogs_account_id') {
                $this->data[$result['field']][$result['value']] = $result['value'];
            }  else if($result['field']=='admin_expense_account_id') {
                $this->data[$result['field']][$result['value']] = $result['value'];
            }  else if($result['field']=='financial_charges_account_id') {
                $this->data[$result['field']][$result['value']] = $result['value'];
            }  else if($result['field']=='sale_marketing_account_id') {
                $this->data[$result['field']][$result['value']] = $result['value'];
            }  else if($result['field']=='non_operating_income_account_id') {
                $this->data[$result['field']][$result['value']] = $result['value'];
            }  else if($result['field']=='tax_paid_account_id') {
                $this->data[$result['field']][$result['value']] = $result['value'];
            } else {
                $this->data[$result['field']] = $result['value'];
            }
        }

        $this->model['coa_level3'] = $this->load->model('gl/coa_level3');
        $this->data['coas'] = $this->model['coa_level3']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->data['action_update'] = $this->url->link($this->getAlias() . '/update', 'token=' . $this->session->data['token'] . 'SSL');
        $this->data['action_validate_name'] = $this->url->link($this->getAlias() . '/validateName', 'token=' . $this->session->data['token'] . '&school_id=' . $this->request->get['school_id']);
        $this->data['strValidation'] = "{
            'rules':{},
            'ignore':[]
        }";

        $this->response->setOutput($this->render());
    }

    public function update() {
        $this->init();

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateUpdate()) {
            $this->db->beginTransaction();
            $id = $this->updateData($this->request->post);
            $this->db->commit();

            $this->session->data['success'] = $this->language->get('success_update');

            $this->updateRedirect($id, $this->request->post);
        }
        $this->data['isEdit'] = 1;
        $this->getForm();
    }

    protected function updateData($data) {
        $this->model['setting'] = $this->load->model('common/setting');
        $this->model['setting']->deleteBulk($this->getAlias(), array('company_id' => $this->session->data['company_id'], 'company_branch_id' => $this->session->data['company_branch_id'], 'fiscal_year_id' => $this->session->data['fiscal_year_id'], 'module' => 'profit_and_loss'));
        foreach($data as $field => $value) {
            if(is_array($value)) {
                foreach($value as $v) {
                    $insert_data = array(
                        'company_id' => $this->session->data['company_id'],
                        'company_branch_id' => $this->session->data['company_branch_id'],
                        'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                        'module' => 'profit_and_loss',
                        'field' => $field,
                        'value' => $v,
                    );
                    $this->model[$this->getAlias()]->add($this->getAlias(), $insert_data);
                }
            } else {
                $insert_data = array(
                    'company_id' => $this->session->data['company_id'],
                    'company_branch_id' => $this->session->data['company_branch_id'],
                    'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                    'module' => 'profit_and_loss',
                    'field' => $field,
                    'value' => $value,
                );
                $this->model[$this->getAlias()]->add($this->getAlias(), $insert_data);
            }
        }
        return $this->session->data['company_branch_id'];

    }
}

?>