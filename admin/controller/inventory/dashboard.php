<?php

class ControllerTransactionDashboard extends HController
{

    protected function getAlias()
    {
        return 'transaction/dashboard';
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
        $this->data['purchase_order'] = $this->url->link('transaction/purchase_order', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['goods_received'] = $this->url->link('transaction/goods_received', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['purchase_invoice'] = $this->url->link('transaction/purchase_invoice', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['purchase_return'] = $this->url->link('transaction/purchase_return', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['sale_invoice'] = $this->url->link('transaction/sale_invoice', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['sale_order'] = $this->url->link('transaction/sale_order', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['fi_customer_invoice'] = $this->url->link('transaction/fi_customer_invoice', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['delivery_challan'] = $this->url->link('transaction/delivery_challan', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['sale_inquiry'] = $this->url->link('transaction/sale_inquiry', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['sale_return'] = $this->url->link('transaction/sale_return', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['fi_supplier_invoice'] = $this->url->link('transaction/fi_supplier_invoice', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['stock_adjustment'] = $this->url->link('transaction/stock_adjustment', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['transfer'] = $this->url->link('transaction/transfer', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['work_order'] = $this->url->link('transaction/work_order', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['vehicle_dispatch'] = $this->url->link('transaction/vehicle_dispatch', 'token=' . $this->session->data['token'], 'SSL');

        $this->template = $this->getAlias() . '.tpl';
        $this->response->setOutput($this->render());
    }
}

?>