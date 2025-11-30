<?php

class ControllerCommonPageHeader extends Controller {

    public function index() {
        $this->data['lang'] = $this->load->language('common/page_header');
        $this->model['image'] = $this->load->model('tool/image');
        $this->model['setting'] = $this->load->model('common/setting');

        $this->data['company_name'] = $this->session->data['company_name'];
        $this->data['branch_name'] = $this->session->data['company_branch_name'];
        $this->data['fiscal_year'] = $this->session->data['fiscal_title'];
        $this->data['user_name'] =  $this->user->getUserName();

        $settings = $this->model['setting']->getRows(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'general',
        ));
        foreach($settings as $setting) {
            $this->data[$setting['field']] = $setting['value'];
        }
        $this->data['company_logo'] = $this->model['image']->resize($this->data['company_logo'],200,50);

        $user_image =  $this->user->getUserImage()?$this->user->getUserImage():'no_user.jpg';
        $this->data['user_image_50_50'] = $this->model['image']->resize($user_image,50,50);
        $this->data['user_image_160_160'] = $this->model['image']->resize($user_image,160,160);

        $base_currency_id = $this->data['base_currency_id'];
        $this->model['currency'] = $this->load->model('setup/currency');
        $currency = $this->model['currency']->getRow(array('currency_id' => $base_currency_id));
        $this->session->data['base_currency_id'] = $currency['currency_id'];
        $this->session->data['base_currency_name'] = $currency['name'];
        //d(array($base_currency_id, $currency, $this->session->data), true);

        $this->data['href_logout'] = $this->url->link('common/logout', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_user_profile'] = $this->url->link('user/user_profile', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_validate_session'] = $this->url->link('common/page_header/validateSession', 'token=' . $this->session->data['token'], 'SSL');

        $this->template = 'common/page_header.tpl';
        $this->render();
    }

    public function validateSession() {
        $json = array(
            'success' => true,
            'session' => $this->session->data,
            'cookie' => unserialize($_COOKIE[session_name()])
        );
        echo json_encode($json);
        exit;
    }
}

?>