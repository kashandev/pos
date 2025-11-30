<?php

class ControllerProductionDashboard extends HController
{

    protected function getAlias()
    {
        return 'production/dashboard';
    }

    protected function init()
    {
        $this->data = $this->load->language($this->getAlias());
        $this->document->setTitle($this->data['heading_title']);
        $this->data['token'] = $this->session->data['token'];
    }

    protected function getList()
    {
        parent::getList();

        $this->model['company'] = $this->load->model('setup/company');
        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        if ($company['sale_tax_account_id'] == null || $company['purchase_discount_account_id'] == null || $company['sale_discount_account_id'] == null) {
            $this->session->data['error_warning'] = $this->data['error_required_company_setting'];

            $this->redirect($this->url->link('setup/company_setting', 'token=' . $this->session->data['token'] , 'SSL'));

        }
        $this->data['href_bill_of_material'] = $this->url->link('production/bom', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_production_expense'] = $this->url->link('production/expense', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_production'] = $this->url->link('production/production', 'token=' . $this->session->data['token'], 'SSL');

        $this->template = $this->getAlias() . '.tpl';
        $this->response->setOutput($this->render());
    }
}

?>