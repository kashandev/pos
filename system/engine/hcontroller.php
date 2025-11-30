<?php

class HController extends Controller {

//    protected $error = array();
//    protected $model = array();

    protected function init() {
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        $this->data['lang'] = $this->load->language($this->getAlias());
        $this->document->setTitle($this->data['lang']['heading_title']);
        $this->data['token'] = $this->session->data['token'];
    }

    protected function getDefaultOrder() {
        return $this->getPrimaryKey();
    }

    protected function getDefaultSort() {
        return 'DESC';
    }

    public function index() {
        $this->init();

        $this->getList();
    }

    public function insert() {
        $this->init();

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateInsert()) {
            $this->db->beginTransaction();
            $id = $this->insertData($this->request->post);
            $this->db->commit();
            $this->session->data['success'] = $this->language->get('success_insert');
            $this->insertRedirect($id, $this->request->post);
        }

        $this->getForm();
    }

    protected function insertData($data) {
        return $this->model[$this->getAlias()]->add($this->getAlias(), $data);
    }

    protected function insertRedirect($id, $data) {
        $url = $this->getURL();
        $this->redirect($this->url->link($this->getAlias(), 'token=' . $this->session->data['token'] . $url, 'SSL'));
    }

    public function update() {
        $this->init();

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateUpdate()) {
            $this->db->beginTransaction();
            $id = $this->updateData($this->request->get[$this->getPrimaryKey()], $this->request->post);
            $this->db->commit();

            $this->session->data['success'] = $this->language->get('success_update');

            $this->updateRedirect($id, $this->request->post);
        }
        $this->data['isEdit'] = 1;
        $this->getForm();
    }

    protected function updateData($primary_key, $data) {
        return $this->model[$this->getAlias()]->edit($this->getAlias(), $primary_key, $data);
    }

    protected function updateRedirect($id, $data) {
        $url = $this->getURL();
        $this->redirect($this->url->link($this->getAlias(), 'token=' . $this->session->data['token'] . $url, 'SSL'));
    }

    public function delete() {
        $this->init();

        if (isset($this->request->post['selected'])) {
            $this->db->beginTransaction();
            foreach ($this->request->post['selected'] as $id) {
                if($this->validateDelete($id)) {
                    $this->deleteData($id);
                    $this->session->data['success'] = $this->language->get('success_delete');
                }
            }
            $this->db->commit();
        } elseif (isset($this->request->get['id'])) {
            $id = $this->request->get['id'];
            $this->db->beginTransaction();
            if($this->validateDelete($id)) {
                $this->deleteData($id);
                $this->session->data['success'] = $this->language->get('success_delete');
            }
            $this->db->commit();
        }

        $url = $this->getURL();
        $this->redirect($this->url->link($this->getAlias(), 'token=' . $this->session->data['token'] . $url, 'SSL'));
    }

    protected function deleteData($primary_key) {
        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);
    }

    public function post() {
        $lang = $this->load->language($this->getAlias());
        if (!$this->user->hasPermission('post', $this->getAlias())) {
            $this->session->data['error_warning'] = $lang['error_permission_post'];
        } else {
            $data = array(
                'is_post' => 1,
                'post_date' => date('Y-m-d H:i:s'),
                'post_by_id' => $this->session->data['user_id']
            );
            $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
            $this->model[$this->getAlias()]->edit($this->getAlias(),$this->request->get[$this->getPrimaryKey()],$data);

            $this->model['document'] = $this->load->model('common/document');
            $this->model['document']->edit($this->getAlias(),$this->request->get[$this->getPrimaryKey()],$data);
        }

        $this->redirect($this->url->link($this->getAlias(), 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL'));
    }

    protected function getList() {
        $this->getBreadCrumbs();

        $url = $this->getURL();
        $this->data['action_insert'] = $this->url->link($this->getAlias() . '/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
        $this->data['action_delete'] = $this->url->link($this->getAlias() . '/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');
        $this->data['action_filter'] = $this->url->link($this->getAlias(), 'token=' . $this->session->data['token'] . $url, 'SSL');

        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } elseif (isset($this->session->data['error'])) {
            $this->data['error_warning'] = $this->session->data['error'];

            unset($this->session->data['error']);
        } elseif (isset($this->session->data['warning'])) {
            $this->data['error_warning'] = $this->session->data['warning'];

            unset($this->session->data['warning']);
        } elseif (isset($this->session->data['error_warning'])) {
            $this->data['error_warning'] = $this->session->data['error_warning'];

            unset($this->session->data['error_warning']);
        } else {
            $this->data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $this->data['success'] = '';
        }

        $this->template = $this->getAlias() . '_list.tpl';
        $this->children = array(
            'common/header',
            'common/column_left',
            'common/page_header',
            'common/page_footer',
            'common/footer',
        );
    }

    protected function getListData() {
        $data = array(
            'criteria' => $this->getCriteria(),
            'filter' => $this->getFilter(),
        );

        return $this->model[$this->getAlias()]->getLists($data);
    }

    protected function getForm() {

        $this->getBreadCrumbs();

        $url = $this->getURL();

        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } elseif (isset($this->session->data['error'])) {
            $this->data['error_warning'] = $this->session->data['error'];

            unset($this->session->data['error']);
        } elseif (isset($this->session->data['warning'])) {
            $this->data['error_warning'] = $this->session->data['warning'];

            unset($this->session->data['warning']);
        } elseif (isset($this->session->data['error_warning'])) {
            $this->data['error_warning'] = $this->session->data['error_warning'];

            unset($this->session->data['error_warning']);
        } else {
            $this->data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $this->data['success'] = '';
        }

        if (isset($this->error)) {
            $this->data['error'] = $this->error;
        }

        if (!isset($this->request->get[$this->getPrimaryKey()])) {
            $this->data['action_save'] = $this->url->link($this->getAlias() . '/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
        } else {
            $this->data['action_save'] = $this->url->link($this->getAlias() . '/update', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()] . $url, 'SSL');
        }

        $this->data['action_cancel'] = $this->url->link($this->getAlias(), 'token=' . $this->session->data['token'] . $url, 'SSL');
        $this->data['action_post'] = $this->url->link($this->getAlias() . '/post', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_print'] = $this->url->link($this->getAlias() . '/printDocument', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');

        if (isset($this->request->post)) {
            foreach ($this->request->post as $field => $value) {
                $this->data[$field] = $value;
            }
        }

        $this->template = $this->getAlias() . '_form.tpl';
        $this->children = array(
            'common/header',
            'common/column_left',
            'common/page_header',
            'common/page_footer',
            'common/footer',
        );
    }

    protected function getBreadCrumbs() {
        $arrBreadCrumbs = explode('/', $this->getAlias());
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('home'),
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get($arrBreadCrumbs[0]),
            // 'href' => $this->url->link($arrBreadCrumbs[0] . '/index', 'token=' . $this->session->data['token'], 'SSL'),
            'href' => $this->url->link($this->getAlias(), 'token=' . $this->session->data['token'].'#', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link($this->getAlias(), 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );
    }

    protected function setSortURL($columns, $ignore = array()) {
        if ($this->data['sort'] == 'ASC') {
            $sort = 'DESC';
        } else {
            $sort = 'ASC';
        }
        foreach ($columns as $column => $caption) {
            if (!in_array($column, $ignore)) {
                $this->data['sort_' . $column] = $this->url->link($this->getAlias(), 'token=' . $this->session->data['token'] . '&order=' . $column . '&sort=' . $sort, 'SSL');
                $url = $this->url->link($this->getAlias(), 'token=' . $this->session->data['token'] . '&order=' . $column . '&sort=' . $sort, 'SSL');
                $this->data['sorts_' . $column] = '<a href="' . $url . '" '.($this->data['order'] == $column ? 'class="'.  strtolower($this->data['sort']).'"' : '').'>' . $caption . '</a>';
            }
        }
    }

    protected function getURL() {
        $url = '';
        if (isset($this->request->get['order']) && $this->request->get['order']) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['sort']) && $this->request->get['sort']) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        return $url;
    }

    protected function getCriteria() {

        if (isset($this->request->get['order']) && $this->request->get['order']) {
            $this->data['order'] = $this->request->get['order'];
        } else {
            $this->data['order'] = $this->getDefaultOrder();
        }

        if (isset($this->request->get['sort']) && $this->request->get['sort']) {
            $this->data['sort'] = $this->request->get['sort'];
        } else {
            $this->data['sort'] = $this->getDefaultSort();
        }

        if (isset($this->request->get['page']) && $this->request->get['page']) {
            $this->data['page'] = $this->request->get['page'];
        } else {
            $this->data['page'] = 1;
        }

        $criteria = array(
            'order' => $this->data['order'],
            'sort' => $this->data['sort'],
            'start' => ($this->data['page'] - 1) * $this->config->get('config_admin_limit'),
            'limit' => $this->config->get('config_admin_limit')
        );

        return $criteria;
    }

    protected function getFilter() {
        $filter = array();
        if (isset($this->request->post['filter'])) {
            $this->session->data['filter'][$this->getAlias()] = $this->request->post['filter'];
        }
        if (isset($this->session->data['filter'][$this->getAlias()])) {
            $filter = $this->session->data['filter'][$this->getAlias()];
        }
        return $filter;
    }

    protected function setPagination($total) {
        if (isset($this->request->get['page']) && $this->request->get['page']) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }
        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = $this->url->link($this->getAlias(), 'token=' . $this->session->data['token'] . $this->getURL() . '&page={page}', 'SSL');

        $this->data['pagination'] = $pagination->render();
    }

    protected function validateInsert() {
        if (!$this->user->hasPermission('insert', $this->getAlias())) {
            $this->error['warning'] = $this->language->get('error_permission_insert');
        }

        $this->validateForm();

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    protected function validateUpdate() {
        if (!$this->user->hasPermission('update', $this->getAlias())) {
            $this->error['warning'] = $this->language->get('error_permission_update');
        }

        $this->validateForm();

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    protected function validateDelete($id='') {
        if (!$this->user->hasPermission('delete', $this->getAlias())) {
            $this->error['warning'] = $this->language->get('error_permission_delete');
        }

        if (!$this->error) {
            return true;
        } else {
            $this->session->data['error_warning'] = $this->error['warning'];
            return false;
        }
    }

    protected function validateForm() {
        return true;
    }

    protected function getFilterString($filter) {
        $cond = array();
        if(isset($filter['RAW']) && $filter['RAW']) {
            return $filter['RAW'];
        } else {
            if(isset($filter['EQ'])) {
                $cond = array_merge($cond,$this->getFilterEQ($filter['EQ']));
            }
            if(isset($filter['NEQ'])) {
                $cond = array_merge($cond,$this->getFilterNEQ($filter['NEQ']));
            }
            if(isset($filter['LT'])) {
                $cond = array_merge($cond,$this->getFilterLT($filter['LT']));
            }
            if(isset($filter['LTE'])) {
                $cond = array_merge($cond,$this->getFilterLTE($filter['LTE']));
            }
            if(isset($filter['GT'])) {
                $cond = array_merge($cond,$this->getFilterGT($filter['GT']));
            }
            if(isset($filter['GTE'])) {
                $cond = array_merge($cond,$this->getFilterGTE($filter['GTE']));
            }
            if(isset($filter['LKB'])) {
                $cond = array_merge($cond,$this->getFilterLKB($filter['LKB']));
            }
            if(isset($filter['LKF'])) {
                $cond = array_merge($cond,$this->getFilterLKF($filter['LKF']));
            }
            if(isset($filter['LKE'])) {
                $cond = array_merge($cond,$this->getFilterLKE($filter['LKE']));
            }
            return implode(' AND ', $cond);

        }
    }

    private function getFilterEQ($data) {
        $cond = array();
        foreach($data as $column => $value) {
            if(!empty($value)) {
                $cond[] = $column . "='" . addslashes($value) . "'";
            }
        }
        return $cond;
    }

    private function getFilterNEQ($data) {
        $cond = array();
        foreach($data as $column => $value) {
            if(!empty($value)) {
                $cond[] = $column . "!='" . addslashes($value) . "'";
            }
        }
        return $cond;
    }

    private function getFilterGT($data) {
        $cond = array();
        foreach($data as $column => $value) {
            if(!empty($value)) {
                $cond[] = $column . ">'" . addslashes($value) . "'";
            }
        }
        return $cond;
    }

    private function getFilterGTE($data) {
        $cond = array();
        foreach($data as $column => $value) {
            if(!empty($value)) {
                $cond[] = $column . ">='" . addslashes($value) . "'";
            }
        }
        return $cond;
    }

    private function getFilterLT($data) {
        $cond = array();
        foreach($data as $column => $value) {
            if(!empty($value)) {
                $cond[] = $column . " < '" . addslashes($value) . "'";
            }
        }
        return $cond;
    }

    private function getFilterLTE($data) {
        $cond = array();
        foreach($data as $column => $value) {
            if(!empty($value)) {
                $cond[] = $column . "<='" . addslashes($value) . "'";
            }
        }
        return $cond;
    }

    private function getFilterLKB($data) {
        $cond = array();
        foreach($data as $column => $value) {
            if(!empty($value)) {
                $cond[] = $column . " LIKE '%" . addslashes($value) . "%'";
            }
        }
        return $cond;
    }

    private function getFilterLKF($data) {
        $cond = array();
        foreach($data as $column => $value) {
            if(!empty($value)) {
                $cond[] = $column . " LIKE '%" . addslashes($value) . "'";
            }
        }
        return $cond;
    }

    private function getFilterLKE($data) {
        $cond = array();
        foreach($data as $column => $value) {
            if(!empty($value)) {
                $cond[] = $column . " LIKE '" . addslashes($value) . "%'";
            }
        }
        return $cond;
    }

}

?>