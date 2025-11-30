<?php

class ControllerSetupCompanySetting extends HController
{

    protected function getAlias()
    {
        return 'setup/company_setting';
    }

    protected function getPrimaryKey()
    {
        return 'company_id';
    }

    protected function init()
    {
        $this->model[$this->getAlias()] = $this->load->model('common/setting');
        $this->data['lang'] = $this->load->language('setup/company_setting');
        $this->document->setTitle($this->data['lang']['heading_title']);
        $this->data['token'] = $this->session->data['token'];
    }

    public function index()
    {
        $this->redirect($this->url->link($this->getAlias() . '/update', 'token=' . $this->session->data['token'] . '&company_id=' . $this->session->data['company_id'], 'SSL'));
    }

    protected function getForm()
    {
        parent::getForm();
        $this->model['image'] = $this->load->model('tool/image');
        $this->data['no_image'] = $this->model['image']->resize('no_logo.jpg', 300, 100);

        $this->data['time_zones'] = getTimeZoneList();


        $this->model['currency'] = $this->load->model('setup/currency');
        $this->data['currencies'] = $this->model['currency']->getArrays('currency_id','name',array('company_id' => $this->session->data['company_id']));



        $this->model['coa'] = $this->load->model('gl/coa_level3');
        $this->data['coas'] = $this->model['coa']->getRows(array('company_id' => $this->session->data['company_id']));




        $filter = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'general',
        );
        $results = $this->model[$this->getAlias()]->getRows($filter);

        $this->data['no_image'] = $this->data['src_company_header_print'] = $this->model['image']->resize('no_image.png', 1000, 200);
        $this->data['no_image'] = $this->data['src_company_footer_print'] = $this->model['image']->resize('no_image.png', 1000, 200);

        foreach ($results as $result) {
            if($result['field']=='inventory_account_id') {
                $this->data[$result['field']][] = $result['value'];
            } elseif($result['field']=='revenue_account_id') {
                $this->data[$result['field']][] = $result['value'];
            } elseif($result['field']=='cogs_account_id') {
                $this->data[$result['field']][] = $result['value'];
            } elseif($result['field']=='adjustment_account_id') {
                $this->data[$result['field']][] = $result['value'];
            } else {
                $this->data[$result['field']] = $result['value'];
            }
        }
        $this->model['company'] = $this->load->model('setup/company');
        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));

        $this->data['description'] = $company['description'];

        if ($this->data['company_logo'] && file_exists(DIR_IMAGE . $this->data['company_logo']) && is_file(DIR_IMAGE . $this->data['company_logo'])) {
            $this->data['src_company_image'] = $this->model['image']->resize($this->data['company_logo'], 300, 100);
        } else {
            $this->data['src_company_image'] = $this->model['image']->resize('no_logo.jpg', 300, 100);
        }


        if ($this->data['company_header_print'] && file_exists(DIR_IMAGE . $this->data['company_header_print']) && is_file(DIR_IMAGE . $this->data['company_header_print'])) {
            $this->data['src_company_header_print'] = $this->model['image']->resize($this->data['company_header_print'], 1000, 200);
        } else {
            $this->data['src_company_header_print'] = $this->model['image']->resize('no_image.png', 1000, 200);
        }

        if ($this->data['company_footer_print'] && file_exists(DIR_IMAGE . $this->data['company_footer_print']) && is_file(DIR_IMAGE . $this->data['company_footer_print'])) {
            $this->data['src_company_footer_print'] = $this->model['image']->resize($this->data['company_footer_print'],  1000, 200);
        } else {
            $this->data['src_company_footer_print'] = $this->model['image']->resize('no_image.png',  1000, 200);
        }

        $this->data['action_update'] = $this->url->link($this->getAlias() . '/update', 'token=' . $this->session->data['token'] . 'SSL');

        $this->data['strValidation'] = "{
            'rules':{
		        'base_currency_id': {'required':true},
		        'time_zone': {'required':true},
		        'suspense_account_id': {'required':true},
                'cash_account_id': {'required':true},
                'company_logo': {'required':false},
             },
            'ignore':[]
        }";

//        d($this->data['description'],true);
        $this->response->setOutput($this->render());
    }

    public function update() {
        $this->init();

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateUpdate()) {
            $this->db->beginTransaction();
            $id = $this->updateData($this->request->post);
            $this->db->commit();

            $this->session->data['success'] = $this->language->get('success_update');

            $this->updateRedirect($id, $this->request->post);
        }
        $this->data['isEdit'] = 1;
        $this->getForm();
    }

    protected function updateData($data) {

//        echo '<pre>';
//        print_r($data);
//        exit;
        $data['description'] = html_entity_decode($data['description']);
        $this->model['company'] = $this->load->model('setup/company');
        $this->model['company']->edit($this->getAlias(),$this->session->data['company_id'], $data);

        $this->model['setting'] = $this->load->model('common/setting');
        $this->model['setting']->deleteBulk($this->getAlias(), array('company_id' => $this->session->data['company_id'], 'company_branch_id' => $this->session->data['company_branch_id'], 'fiscal_year_id' => $this->session->data['fiscal_year_id'], 'module' => 'general'));
        foreach($data as $field => $value) {

            if(is_array($value)) {
                foreach($value as $v) {
                    $insert_data = array(
                        'company_id' => $this->session->data['company_id'],
                        'company_branch_id' => $this->session->data['company_branch_id'],
                        'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                        'module' => 'general',
                        'field' => $field,
                        'value' => $v,
                    );
                    $this->model[$this->getAlias()]->add($this->getAlias(), $insert_data);
                }
            } else {
                $insert_data = array(
                    'company_id' => $this->session->data['company_id'],
                    'company_branch_id' => $this->session->data['company_branch_id'],
                    'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                    'module' => 'general',
                    'field' => $field,
                    'value' => $value,
                );
                $this->model[$this->getAlias()]->add($this->getAlias(), $insert_data);
            }
        }
        return $this->session->data['company_branch_id'];

    }
}

?>