<?php

class ControllerSetupDashboard extends HController {

    protected function getAlias() {
        return 'setup/dashboard';
    }

    protected function init() {
        $this->data['lang'] = $this->load->language($this->getAlias());
        $this->document->setTitle($this->data['lang']['heading_title']);
        $this->data['token'] = $this->session->data['token'];
    }

    protected function getList() {
        parent::getList();

        $this->data['company'] = $this->url->link('setup/company', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['company_setting'] = $this->url->link('setup/company_setting', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['company_branch'] = $this->url->link('setup/company_branch', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['fiscal_year'] = $this->url->link('setup/fiscal_year', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['sector'] = $this->url->link('setup/sector', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['unit'] = $this->url->link('setup/unit', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['brand'] = $this->url->link('setup/brand', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['model'] = $this->url->link('setup/model', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['make'] = $this->url->link('setup/make', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['product'] = $this->url->link('setup/product', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['country'] = $this->url->link('setup/country', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['state'] = $this->url->link('setup/zone', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['customer'] = $this->url->link('setup/customer', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['warehouse'] = $this->url->link('inventory/warehouse', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['supplier'] = $this->url->link('setup/supplier', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['currency'] = $this->url->link('setup/currency', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['salesman'] = $this->url->link('setup/salesman', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['manufacture'] = $this->url->link('setup/manufacture', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['product_category'] = $this->url->link('setup/product_category', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['opening_stock'] = $this->url->link('setup/opening_stock', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['user_permission'] = $this->url->link('user/user_permission', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['user'] = $this->url->link('user/user', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['backup_restore'] = $this->url->link('tool/backup', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['mapping_account'] = $this->url->link('gl/mapping_coa/insert', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['transaction_account'] = $this->url->link('gl/transaction_account', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['industry'] = $this->url->link('gl/industry', 'token=' . $this->session->data['token'], 'SSL');

        $this->template = $this->getAlias() . '.tpl';
        $this->response->setOutput($this->render());
    }

}

?>