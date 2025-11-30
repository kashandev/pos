<?php
class ControllerErrorNotFound extends Controller {
    public function index() {
        $this->data['lang'] = $this->load->language('error/not_found');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('error/not_found', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->template = 'error/not_found.tpl';
        $this->children = array(
            'common/header',
            'common/footer',
            'common/column_left'
        );

        $this->response->setOutput($this->render());
    }
}
?>