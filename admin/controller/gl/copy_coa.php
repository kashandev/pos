<?php

class ControllerGlCOPYCOA extends HController {

    protected function validateDocument() {
        return false;
    }

    protected function getAlias() {
        return 'gl/copy_coa';
    }

    protected function init() {
        $this->data['lang'] = $this->load->language($this->getAlias());
        $this->document->setTitle($this->data['lang']['heading_title']);
        $this->data['token'] = $this->session->data['token'];
    }

    protected function getList() {
        parent::getList();

        $this->model['company'] = $this->load->model('setup/company');
        $this->data['companies'] = $this->model['company']->getRows(array('status' => 'Active'));

        $this->data['action_copy'] = $this->url->link($this->getAlias() .'/copyCoa', 'token=' . $this->session->data['token'], 'SSL');

        $this->template = $this->getAlias() . '.tpl';
        $this->response->setOutput($this->render());
    }

    public function copyCoa() {
        ini_set('memory_limit','2048M');

        $post = $this->request->post;
        $this->load->language('gl/copy_coa');
        $this->model['coa'] = $this->load->model('gl/coa');
        $arrCoa = $this->model['coa']->getRows(array('company_id' => $post['company_id']));

        $this->model['coa_level1'] = $this->load->model('gl/coa_level1');
        $arrCoaCheck = $this->model['coa_level1']->getRows(array('company_id' => $this->session->data['company_id']));

        if(empty($arrCoaCheck)){
            $this->db->beginTransaction();
            $coa = array();
            $arrMapping = array();
            foreach($arrCoa as $c) {
                if(!isset($coa[$c['level1_code']])) {
                    $coa[$c['level1_code']] = array(
                        'level1_code' => $c['level1_code'],
                        'name' => $c['level1_name'],
                        'company_id' => $this->session->data['company_id'],
                        'gl_type_id' => $c['gl_type_id'],
                        'status'     => 1,
                        'level2' => array()
                    );

                    $insert_data = $coa[$c['level1_code']];
                    $this->model['coa_level1'] = $this->load->model('gl/coa_level1');
                    $level1_id =$this->model['coa_level1']->add('coa_level1',$insert_data);

                }

                if(!isset($coa[$c['level1_code']]['level2'][$c['level2_code']])) {
                    $coa[$c['level1_code']]['level2'][$c['level2_code']] = array(
                        'coa_level1_id' => $level1_id,
                        'level1_code' => $c['level1_code'],
                        'level2_code' => $c['level2_code'],
                        'name' => $c['level2_name'],
                        'company_id' => $this->session->data['company_id'],
                        'status'     => 1,
                        'level3' => array()
                    );

                    $insert_data = $coa[$c['level1_code']]['level2'][$c['level2_code']];
                    $this->model['coa_level2'] = $this->load->model('gl/coa_level2');
                    $level2_id =$this->model['coa_level2']->add('coa_level2',$insert_data);

                }
                $coa[$c['level1_code']]['level2'][$c['level2_code']]['level3'][$c['level3_code']] = array(
                    'coa_level1_id' => $level1_id,
                    'coa_level2_id' => $level2_id,
                    'level1_code' => $c['level1_code'],
                    'level2_code' => $c['level2_code'],
                    'level3_code' => $c['level3_code'],
                    'name' => $c['level3_name'],
                    'company_id' => $this->session->data['company_id'],
                    'status' => 1,
                );

                $insert_data = $coa[$c['level1_code']]['level2'][$c['level2_code']]['level3'][$c['level3_code']];
                $this->model['coa_level3'] = $this->load->model('gl/coa_level3');
                $level3_id =$this->model['coa_level3']->add('coa_level3',$insert_data);

                // ADD MAPPING  //
                $arrMapping[$c['coa_level3_id']] = $level3_id;

            }
            // $this->model['mapping_coa'] = $this->load->model('gl/mapping_coa');
            // $arrMappings = $this->model['mapping_coa']->getRows(array('company_id' => $post['company_id']));

            // foreach($arrMappings as $arrmapp){

            //     unset($arrmapp['mapping_coa_id']);
            //     $insert_data =  $arrmapp;
            //     $insert_data['coa_level3_id'] = $arrMapping[$insert_data['coa_level3_id']];
            //     $insert_data['company_id'] = $this->session->data['company_id'];

            //     $this->model['mapping_coa']->add($this->getAlias(), $insert_data);
            // }
            $this->db->commit();
            $this->session->data['success'] = $this->language->get('success_insert');
            $this->redirect($this->url->link($this->getAlias() , '&'. 'token=' . $this->session->data['token'] , 'SSL'));
        } else {
            $this->session->data['warning'] =$this->language->get('error_exists');
            $this->redirect($this->url->link($this->getAlias() , '&'. 'token=' . $this->session->data['token'] , 'SSL'));

        }
    }

}

?>