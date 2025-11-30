<?php

class ControllerGLIndex extends HController {

    protected function getAlias() {
        return 'gl/index';
    }
    
    protected function init() {
        $this->data = $this->load->language($this->getAlias());
        $this->document->setTitle($this->data['heading_title']);
        $this->data['token'] = $this->session->data['token'];
    }

    protected function getList() {
        parent::getList();
        
        $this->data['coa_level1'] = $this->url->link('gl/coa_level1', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['coa_level2'] = $this->url->link('gl/coa_level2', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['coa_level3'] = $this->url->link('gl/coa_level3', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['copy_coa'] = $this->url->link('gl/copy_coa', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['opening_account'] = $this->url->link('gl/opening_account', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['cash_payment'] = $this->url->link('gl/cash_payment', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['bank_payment'] = $this->url->link('gl/bank_payment', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['cash_receipt'] = $this->url->link('gl/cash_receipt', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['bank_receipt'] = $this->url->link('gl/bank_receipt', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['journal_voucher'] = $this->url->link('gl/journal_voucher', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['advance_payment'] = $this->url->link('gl/advance_payment', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['advance_receipt'] = $this->url->link('gl/advance_receipt', 'token=' . $this->session->data['token'], 'SSL');

        $this->template = $this->getAlias() . '.tpl';
        $this->response->setOutput($this->render());
    }

}

?>