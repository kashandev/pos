<?php

class ControllerCommonPreset extends Controller {

   // private $error = array();

    public function index() {
        $this->data['lang'] = $this->load->language('common/preset');

        $this->document->setTitle($this->language->get('heading_title'));
        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $this->data['base'] = HTTPS_BASE;
        } else {
            $this->data['base'] = HTTP_BASE;
        }
        $model_fiscal_year = $this->load->model('setup/fiscal_year');
        $model_company = $this->load->model('setup/company');
        $model_company_branch = $this->load->model('setup/company_branch');
        $model_setting = $this->load->model('common/setting');
        $model_image = $this->load->model('tool/image');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $company = $model_company->getRow(array('company_id' => $this->request->post['company_id']));
            $partner_types = unserialize($company['partner_types']);
            $company_branch = $model_company_branch->getRow(array('company_id' => $this->request->post['company_id'], 'company_branch_id' => $this->request->post['company_branch_id']));
            $fiscal_year = $model_fiscal_year->getRow(array('fiscal_year_id' => $this->request->post['fiscal_year_id']));
            $this->session->data['partner_types'] = array();
            foreach($partner_types as $partner_type) {
                if(isset($partner_type['selected']) && $partner_type['selected']==1) {
                    $this->session->data['partner_types'][$partner_type['partner_type_id']] = array(
                        'partner_type_id' => $partner_type['partner_type_id'],
                        'name' => $partner_type['name']
                    );
                }
            }
            $this->session->data['company_id'] = $this->request->post['company_id'];
            $this->session->data['company_name'] = html_entity_decode($company['name']);
            $this->session->data['company_branch_id'] = $this->request->post['company_branch_id'];
            $this->session->data['company_branch_name'] = $company_branch['name'];
            $this->session->data['branch_code'] = $company_branch['branch_code'];
            $this->session->data['fiscal_year_id'] = $this->request->post['fiscal_year_id'];
            $this->session->data['fiscal_title'] = $fiscal_year['name'];
            $this->session->data['fy_code'] = $fiscal_year['fy_code'];
            $this->session->data['fiscal_date_from'] = $fiscal_year['date_from'];
            $this->session->data['fiscal_date_to'] = $fiscal_year['date_to'];
            $this->session->data['db_name'] = $fiscal_year['db_name'];


            $this->model['setting'] = $this->load->model('common/setting');
            $settings = $this->model['setting']->getRows(array(
                'company_id' => $this->session->data['company_id'],
                'company_branch_id' => $this->session->data['company_branch_id'],
                'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                'module' => 'general',
            ));
            foreach($settings as $setting) {
                if($setting['field'] == 'company_logo') {
                    if(file_exists(DIR_IMAGE . $setting['value'])) {
                        $this->session->data['company_logo'] = DIR_IMAGE . $setting['value'];
                        $this->session->data['company_image'] = $setting['value'];
                    } else {
                        $this->session->data['company_logo'] = '';
                        $this->session->data['company_image'] = '';
                    }
                } else {
                    $this->session->data[$setting['field']] = $setting['value'];
                }

            }

            if (isset($this->request->post['redirect'])) {
                $this->redirect($this->request->post['redirect'] . '&token=' . $this->session->data['token']);
            } else {
                $this->redirect($this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'));
            }
        }

        $this->data['heading_title'] = $this->language->get('heading_title');

        $this->data['text_preset'] = $this->language->get('text_preset');

        $this->data['entry_company'] = $this->language->get('entry_company');
        $this->data['entry_company_branch'] = $this->language->get('entry_company_branch');
        $this->data['entry_fiscal_year'] = $this->language->get('entry_fiscal_year');

        $this->data['button_submit'] = $this->language->get('button_submit');

        if ((isset($this->session->data['token']) && !isset($this->request->get['token'])) || ((isset($this->request->get['token']) && (isset($this->session->data['token']) && ($this->request->get['token'] != $this->session->data['token']))))) {
            $this->error['warning'] = $this->language->get('error_token');
        }

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

        $this->data['action'] = $this->url->link('common/preset', 'token=' . $this->session->data['token'], 'SSL');

        if (isset($this->request->get['redirect'])) {
            $this->data['redirect'] = base64_decode($this->request->get['redirect']);
        } else {
            $this->data['redirect'] = '';
        }

        $model_branch_access = $this->load->model('user/user_branch_access');
        $user_company_accesses = $model_branch_access->getArrays('company_id','company_id',array('user_id' => $this->session->data['user_id']));
        //d($user_company_accesses,true);
        $model_company = $this->load->model('setup/company');
        $companies = $model_company->getRows(array('status' => 'Active'));
        //d(array($this->session->data['user_id'], $companies, $user_company_accesses), true);
        foreach($companies as $company) {
            if(in_array($company['company_id'],$user_company_accesses)) {
                $this->data['companys'][] = $company;
            }
        }

        $fiscal_years = $model_fiscal_year->getRows(array('status' => 1), array('date_to DESC'));
        $this->data['fiscal_years'] = $fiscal_years;

        if(isset($this->session->data['fiscal_year_id']) && $this->session->data['fiscal_year_id']) {
            $this->data['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
        } else {
            foreach($fiscal_years as $fiscal_year) {
                if($fiscal_year['date_from'] <= date('Y-m-d') && $fiscal_year['date_to'] >= date('Y-m-d')) {
                    $this->data['fiscal_year_id'] = $fiscal_year['fiscal_year_id'];
                }
            }
        }
        if(isset($this->session->data['company_id']) && $this->session->data['company_id']) {
            $this->data['company_id'] = $this->session->data['company_id'];
        } else {
            $this->data['company_id'] = 0;
        }
        if(isset($this->session->data['company_branch_id']) && $this->session->data['company_branch_id']) {
            $this->data['company_branch_id'] = $this->session->data['company_branch_id'];
        } else {
            $this->data['company_branch_id'] = 0;
        }

        $this->data['token'] = $this->session->data['token'];
        $this->data['href_get_branches'] = $this->url->link('common/preset/getBranches', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_get_fiscal_year'] = $this->url->link('common/preset/getFiscalYear', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['action'] = $this->url->link('common/preset', 'token=' . $this->session->data['token'], 'SSL');

        $this->template = 'common/preset.tpl';
//        $this->children = array(
//            'common/header',
//            'common/footer',
//        );

        $this->response->setOutput($this->render());
    }

    private function validate() {
        $post = $this->request->post;
        if(!$post['fiscal_year_id']) {
            $this->error['warning'] = $this->language->get('error_select_fiscal_year');
        }
        if(!$post['company_branch_id']) {
            $this->error['warning'] = $this->language->get('error_select_company_branch');
        }
        if(!$post['company_id']) {
            $this->error['warning'] = $this->language->get('error_select_company');
        }
        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    public function getBranches() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->request->post['company_id']) {
            $model_branch_access = $this->load->model('user/user_branch_access');
            $user_branch_accesses = $model_branch_access->getArrays('company_branch_id','company_branch_id',array('user_id' => $this->session->data['user_id']));

            $this->model['company_branch'] = $this->load->model('setup/company_branch');
            $branches = $this->model['company_branch']->getRows(array('company_id' => $this->request->post['company_id']));
            $company_branches = array();
            foreach($branches as $branch) {
                if(in_array($branch['company_branch_id'],$user_branch_accesses)) {
                    $company_branches[] = $branch;
                }
            }
            $json = array(
                'success' => true,
                'company_branches' => $company_branches,
                'user_branch_access' => $user_branch_accesses,
                'branches' => $branches
            );
        } else {
//            d(array($this->request->server['REQUEST_METHOD'], $this->request->post));
            $this->load->language('setup/company');
            $json = array(
                'success' => false,
                'error' => $this->language->get('error_select_company')
            );
        }
        $this->response->setOutput(json_encode($json));
    }

    public function getFiscalYear() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->request->post['company_id']) {
            $this->model['fiscal_year'] = $this->load->model('setup/fiscal_year');
            $fiscal_years = $this->model['fiscal_year']->getRows(array('status' => 'Active', 'company_id' => $this->request->post['company_id']));

            if(isset($this->session->data['fiscal_year_id']) && $this->session->data['fiscal_year_id']) {
                $fiscal_year_id = $this->session->data['fiscal_year_id'];
            } else {
                foreach($fiscal_years as $fiscal_year) {
                    if($fiscal_year['date_from'] <= date('Y-m-d') && $fiscal_year['date_to'] >= date('Y-m-d')) {
                        $fiscal_year_id = $fiscal_year['fiscal_year_id'];
                    }
                }
            }

            $json = array(
                'success' => true,
                'fiscal_year_id' => $fiscal_year_id,
                'fiscal_years' => $fiscal_years
            );

        } else {
            $this->load->language('setup/company');
            $json = array(
                'success' => false,
                'error' => $this->language->get('error_select_company')
            );
        }
        $this->response->setOutput(json_encode($json));
    }

}

?>