<?php

class ControllerUserUserProfile extends HController {

    protected function getAlias() {
        return 'user/user_profile';
    }

    protected function getPrimaryKey() {
        return 'user_id';
    }

    public function index() {
        $this->redirect($this->url->link($this->getAlias().'/update', 'token=' . $this->session->data['token'] . '&user_id=' . $this->user->getId(), 'SSL'));
    }

    protected function init() {
        $this->model[$this->getAlias()] = $this->load->model('user/user');
        $this->data['lang'] = $this->load->language('user/user_profile');
        $this->document->setTitle($this->data['lang']['heading_title']);
        $this->data['token'] = $this->session->data['token'];
    }

    public function update() {
        $this->init();
        if($this->request->get['user_id'] != $this->user->getId()) {
            $this->session->data['error_warning'] = $this->data['lang']['error_invalid_request'];
            $this->redirect($this->url->link($this->getAlias().'/update', 'token=' . $this->session->data['token'] . '&user_id=' . $this->user->getId(), 'SSL'));
        }

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateUpdate()) {
            $this->db->beginTransaction();
            $id = $this->updateData($this->request->get[$this->getPrimaryKey()], $this->request->post);
            $this->db->commit();

            $this->session->data['success'] = $this->language->get('success_update');

            $this->updateRedirect($id, $this->request->post);
        }
        $this->getForm();
    }

    protected function getForm() {
        parent::getForm();

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->data['lang']['dashboard'],
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'class' => 'fa fa-dashboard',
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->data['lang']['user'],
            'href' => $this->url->link('user/user', 'token=' . $this->session->data['token'], 'SSL'),
            'class' => 'fa fa-user',
            'separator' => false
        );

        $this->model['company'] = $this->load->model('setup/company');
        $this->data['companies'] = $this->model['company']->getRows(array('status' => 'Active'));

        $this->model['user_permission'] = $this->load->model('user/user_permission');
        $this->data['user_permissions'] = $this->model['user_permission']->getRows();

        if (isset($this->request->get['user_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $result = $this->model[$this->getAlias()]->getRow(array($this->getPrimaryKey() => $this->request->get[$this->getPrimaryKey()]));
            foreach ($result as $field => $value) {
                $this->data[$field] = $value;
            }
            $this->data['companies'] = $this->model['company']->getRows();
        }

        $this->model['image'] = $this->load->model('tool/image');
        $this->data['no_image'] = $this->model['image']->resize('no_user.jpg', 300, 300);

        if ($this->data['user_image'] && file_exists(DIR_IMAGE . $this->data['user_image']) && is_file(DIR_IMAGE . $this->data['user_image'])) {
            $this->data['src_user_image'] = $this->model['image']->resize($this->data['user_image'], 300, 300);
        } else {
            $this->data['src_user_image'] = $this->model['image']->resize('no_user.jpg', 300, 300);
        }

        $this->data['action_validate_name'] = $this->url->link($this->getAlias() . '/validateName', 'token=' . $this->session->data['token'] . '&user_id=' . $this->request->get['user_id']);
        $this->data['action_validate_email'] = $this->url->link($this->getAlias() . '/validateEmail', 'token=' . $this->session->data['token'] . '&user_id=' . $this->request->get['user_id']);
        $this->data['strValidation'] = "{
            'rules':{
                'login_name': {'required':true, 'minlength': 3, 'remote':  {url: '" . $this->data['action_validate_name'] . "', type: 'post'}},
                'user_name': {'required':true},
                'email': {'email': true, 'required': true, 'remote':  {url: '" . $this->data['action_validate_email'] . "', type: 'post'}},
                'login_password': {'minlength': 8},
                'confirm': {'equalTo': '#password'},
            },
        }";

        $this->template = 'user/user_profile.tpl';
        $this->response->setOutput($this->render());
    }

    protected function updateData($primary_key, $data)
    {
        if ($data['login_password']) {
            $data['login_password'] = md5($data['login_password']);
        } else {
            unset($data['login_password']);
        }

        $this->model[$this->getAlias()]->edit($this->getAlias(), $primary_key, $data);
    }

    public function validateName()
    {
        $login_name = $this->request->post['login_name'];
        $user_id = $this->request->get['user_id'];
        $this->load->language('user/user');
        if ($login_name) {
            $this->model['user'] = $this->load->model('user/user');
            $where = "`login_name` = '".$login_name."' AND `user_id` != '".$user_id."'";
            $user = $this->model['user']->getRow($where);
            if ($user) {
                echo json_encode($this->language->get('error_duplicate_login_name'));
            } else {
                echo json_encode("true");
            }
        } else {
            echo json_encode($this->language->get('error_invalid'));
        }
        exit;
    }

    public function validateEmail() {
        $email = $this->request->post['email'];
        $user_id = $this->session->data['user_id'];
        $this->load->language('user/user');
        if($email) {
            $this->model['user'] = $this->load->model('user/user');
            $where = "`email` = '".$email."' AND `user_id` != '".$user_id."'";
            $user = $this->model['user']->getRow($where);
            if($user) {
                echo json_encode($this->language->get('error_duplicate_email'));
            } else {
                echo json_encode("true");
            }
        } else {
            echo json_encode($this->language->get('error_email'));
        }
        exit;
    }
}

?>