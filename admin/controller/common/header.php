<?php

class ControllerCommonHeader extends Controller {

    protected function index() {
        $this->data = $this->load->language('common/header');
        $company_name = $this->session->data['company_name'];

        $this->data['page_title'] = $company_name . ' | ' . $this->document->getTitle();
        if(isset($this->session->data['token'])) {
            $this->data['action_open_file_manager'] = $this->url->link('common/function/openFileManager', 'token=' . $this->session->data['token'],'SSL');
            $this->data['action_upload_file'] = $this->url->link('common/function/uploadFile', 'token=' . $this->session->data['token'],'SSL');
            $this->data['href_get_partner'] = $this->url->link('common/function/getPartner', 'token=' . $this->session->data['token'],'SSL');
            $this->data['href_get_document_ledger'] = $this->url->link('common/function/getDocumentLedger', 'token=' . $this->session->data['token'],'SSL');
            $this->data['href_get_product_by_code'] = $this->url->link('common/function/getProductByCode', 'token=' . $this->session->data['token'],'SSL');
            $this->data['href_get_product_by_id'] = $this->url->link('common/function/getProductById', 'token=' . $this->session->data['token'],'SSL');
            $this->data['href_get_warehouse_stock'] = $this->url->link('common/function/getWarehouseStock', 'token=' . $this->session->data['token'],'SSL');
            $this->data['href_get_customer_unit'] = $this->url->link('common/function/getCustomerUnit', 'token=' . $this->session->data['token'],'SSL');

            $this->data['href_get_partner_by_id'] = $this->url->link('common/function/getPartnerById', 'token=' . $this->session->data['token'],'SSL');
            $this->data['href_random_request'] = $this->url->link('common/function/randomRequest', 'token=' . $this->session->data['token'], 'SSL');

        } else {
            $this->data['action_open_file_manager'] = '';
            $this->data['action_upload_file'] = '';
        }

        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $this->data['base'] = HTTPS_BASE;
        } else {
            $this->data['base'] = HTTP_BASE;
        }

        $this->template = 'common/header.tpl';
        $this->render();
    }

}

?>