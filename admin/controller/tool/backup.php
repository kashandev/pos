<?php

class ControllerToolBackup extends HController {

    protected function getAlias() {
        return 'tool/backup';
    }

    public function index() {
        $this->model['backup'] = $this->load->model('tool/backup');
        $this->data['lang'] = $this->load->language($this->getAlias());
        $this->document->setTitle($this->data['lang']['heading_title']);
        $this->data['token'] = $this->session->data['token'];

        $this->data['heading_title'] = $this->language->get('heading_title');

        $this->data['text_select_all'] = $this->language->get('text_select_all');
        $this->data['text_unselect_all'] = $this->language->get('text_unselect_all');

        $this->data['entry_restore'] = $this->language->get('entry_restore');
        $this->data['entry_backup'] = $this->language->get('entry_backup');

        $this->data['button_backup'] = $this->language->get('button_backup');
        $this->data['button_restore'] = $this->language->get('button_restore');

        $this->data['tab_general'] = $this->language->get('tab_general');

        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $this->data['success'] = '';
        }

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('tool/backup', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['restore'] = $this->url->link('tool/backup', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['backup'] = $this->url->link('tool/backup/backup', 'token=' . $this->session->data['token'], 'SSL');

        $this->model['backup'] = $this->load->model('tool/backup');
        $tables = $this->model['backup']->getTables();
        foreach($tables as $table) {
            if($table != 'audit' && $table != 'audit_detail' && strrpos($table, "vw_") === false && strrpos($table, "_not_") === false) {
                $this->data['tables'][] = $table;
            }
        }

        $this->template = 'tool/backup.tpl';
        $this->children = array(
            'common/header',
            'common/footer',
            'common/column_left'         
        );

        $this->response->setOutput($this->render());
    }

    public function backup() {
        ini_set('max_execution_time',0);
        ini_set('memory_limit','3072M');
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
            $this->response->addheader('Pragma: public');
            $this->response->addheader('Expires: 0');
            $this->response->addheader('Content-Description: File Transfer');
            $this->response->addheader('Content-Type: application/octet-stream');
            $this->response->addheader('Content-Disposition: attachment; filename=Backup-'.date('YmdHis').'.sql');
            $this->response->addheader('Content-Transfer-Encoding: binary');

            $this->model['backup'] = $this->load->model('tool/backup');
            $this->response->setOutput($this->model['backup']->backup($this->request->post['backup']));
        } else {
            return $this->forward('error/permission');
        }
    }

    private function validate() {
        if (!$this->user->hasPermission('insert', 'tool/backup')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

}

?>