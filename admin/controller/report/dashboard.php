<?php

class ControllerReportDashboard extends HController {

    protected function getAlias() {
        return 'report/dashboard';
    }

    protected function init() {
        $this->data = $this->load->language($this->getAlias());
        $this->document->setTitle($this->data['heading_title']);
        $this->data['token'] = $this->session->data['token'];
    }

    protected function getList() {
        parent::getList();

        $this->data['purchase_order_report'] = $this->url->link('report/purchase_order_report', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['goods_received_report'] = $this->url->link('report/goods_received_report', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['purchase_invoice_report'] = $this->url->link('report/purchase_invoice_report', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['purchase_report'] = $this->url->link('report/purchase_report', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['delivery_challan_report'] = $this->url->link('report/delivery_challan_report', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['sale_report'] = $this->url->link('report/sale_report', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['sale_profit'] = $this->url->link('report/sale_profit', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['stock_report'] = $this->url->link('report/stock', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['ledger_report'] = $this->url->link('report/ledger_report', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['entity_ledger'] = $this->url->link('report/entity_ledger', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['party_ledger'] = $this->url->link('report/party_ledger', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['coa_report'] = $this->url->link('report/coa', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['trial_balance'] = $this->url->link('report/trial_balance', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['balance_sheet'] = $this->url->link('report/balance_sheet', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['income_statement'] = $this->url->link('report/income_statement', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['outstanding_report'] = $this->url->link('report/outstanding', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['aging_report'] = $this->url->link('report/aging', 'token=' . $this->session->data['token'], 'SSL');


        $this->template = $this->getAlias() . '.tpl';
        $this->response->setOutput($this->render());
    }
}
?>		