<?php

class ControllerErrorSecurity extends Controller {

    public function index() {
        $this->load->language('error/security');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['heading_title'] = $this->language->get('heading_title');

        $this->data['text_security'] = $this->language->get('text_security');

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('error/security', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->template = 'error/security.tpl';
        $this->children = array(
            'common/header',
            'common/footer',
            'common/column_left'
        );

        $this->response->setOutput($this->render());
    }

}

?>