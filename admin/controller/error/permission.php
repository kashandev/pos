<?php

class ControllerErrorPermission extends Controller {

    public function index() {
        $this->data['lang'] = $this->load->language('error/permission');

        $this->document->setTitle($this->language->get('heading_title'));
        $this->data['heading_title'] = $this->language->get('heading_title');
        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $this->data['base'] = HTTPS_BASE;
        } else {
            $this->data['base'] = HTTP_BASE;
        }

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->data['lang']['dashboard'],
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'class' => 'fa fa-dashboard',
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->data['lang']['permission_error'],
            'href' => $this->url->link('error/permission', 'token=' . $this->session->data['token'], 'SSL'),
            'class' => 'fa fa-exclamation-triangle',
            'separator' => false
        );

        $this->data['contents'] = $this->data['lang']['contents'];
//        $this->data['token'] = $this->session->data['token'];

        $this->template = 'error/permission.tpl';
        $this->children = array(
            'common/header',
            'common/column_left',
            'common/column_right',
            'common/page_header',
            'common/page_footer',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }

}

?>