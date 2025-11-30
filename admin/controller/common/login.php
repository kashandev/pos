<?php

class ControllerCommonLogin extends Controller {

//    private $error = array();

    public function index() {
        $this->data['lang'] = $this->load->language('common/login');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->data['company_logo']= HTTP_IMAGE.'logo2-light_2x.png';
        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $this->data['base'] = HTTPS_BASE;
        } else {
            $this->data['base'] = HTTP_BASE;
        }

        if ($this->user->isLogged() && isset($this->request->get['token']) && ($this->request->get['token'] == $this->session->data['token'])) {
            $this->redirect($this->url->link('setup/report', 'token=' . $this->session->data['token'], 'SSL'));
        }

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->session->data['token'] = md5(mt_rand());

            $this->redirect($this->url->link('common/preset', 'token=' . $this->session->data['token'], 'SSL'));
        }

        if ((isset($this->session->data['token']) && !isset($this->request->get['token'])) || ((isset($this->request->get['token']) && (isset($this->session->data['token']) && ($this->request->get['token'] != $this->session->data['token']))))) {
            $this->error['warning'] = $this->language->get('error_token');
        }

        if (isset($this->error['error_warning'])) {
            $this->data['error_warning'] = $this->error['error_warning'];
        } elseif (isset($this->error['warning'])) {
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

        $this->data['action'] = $this->url->link('common/login', '', 'SSL');

        if (isset($this->request->post['username'])) {
            $this->data['username'] = $this->request->post['username'];
        } else {
            $this->data['username'] = '';
        }

        if (isset($this->request->post['password'])) {
            $this->data['password'] = $this->request->post['password'];
        } else {
            $this->data['password'] = '';
        }


        if (isset($this->request->get['route'])) {
            $route = $this->request->get['route'];

            unset($this->request->get['route']);

            if (isset($this->request->get['token'])) {
                unset($this->request->get['token']);
            }

            $url = '';

            if ($this->request->get) {
                $url .= http_build_query($this->request->get);
            }

            $this->data['redirect'] = $this->url->link($route, $url, 'SSL');
        } else {
            $this->data['redirect'] = '';
        }

        $this->data['forgotten'] = $this->url->link('common/forgotten', '', 'SSL');

        $this->template = 'common/login.tpl';
//        $this->children = array(
//            'common/header',
//            'common/footer',
//        );

        $this->response->setOutput($this->render());
    }

    private function validate() {
        if (isset($this->request->post['login_name']) && isset($this->request->post['login_password']))
        {
            $this->model['user'] = $this->load->model('user/user');
            $login_name = $this->request->post['login_name'];
            $login_password = md5($this->request->post['login_password']);
            $user = $this->model['user']->getRow(array('login_name' => $login_name, 'login_password' => $login_password,'status' => 'Active'));
            $currentTime =date("H:i:s");
            // d($currentTime,true);
            $fromTime = ($user['from_time']?$user['from_time']:'00:00:00');
            $toTime = ($user['to_time']?$user['to_time']:'23:59:59');
            //d([$fromTime, $toTime, $currentTime], true);
            if($currentTime >= $fromTime && $currentTime <= $toTime){
                //d([$fromTime, $toTime, $currentTime], true);
                if($user) {
                    $this->model['user_permission'] = $this->load->model('user/user_permission');
                    $user_permission = $this->model['user_permission']->getRow(array('user_permission_id' => $user['user_permission_id']));

                    $UserData =  array(
                        'login_time' => date("Y-m-d H:i:s"),
                        'user_name' => $user['user_name'],
                        'login_name' => $user['login_name'],
                        'user_id' => $user['user_id'],
                    );
//d($UserData,true);
//                    $this->model['login_history'] = $this->load->model('common/login_history');
//                    $this->model['login_history']->add('common/login_history', $UserData);

                    $data = $user;
                    $data['permissions'] = unserialize($user_permission['permission']);
                    $this->user->set($data);
                    $this->session->data['user_id'] = $user['user_id'];
                    $this->session->data['user_permission'] = $user['user_permission_id'];

                } else {
                    $this->error['warning'] = $this->language->get('error_login');
                }
            }else{
                $this->error['warning'] = "Login Time Out ";
            }
//            d(array($currentTime,$fromTime,$toTime), true);

        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

}

?>