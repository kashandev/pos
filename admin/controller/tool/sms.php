<?php

class ControllerToolSMS extends HController {

    protected function getAlias() {
        return 'tool/sms';
    }

    public function index() {
        $this->data['lang'] = $this->load->language($this->getAlias());
        $this->document->setTitle($this->data['lang']['heading_title']);
        $this->data['token'] = $this->session->data['token'];

        $this->data['heading_title'] = $this->language->get('heading_title');

        $this->model['sport'] = $this->load->model('setup/sport');
        $this->data['sports'] = $this->model['sport']->getRows(array('status' => 'Active'));

        $this->model['ground_area'] = $this->load->model('ground/area');
        $this->data['ground_areas'] = $this->model['ground_area']->getRows();

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
            'text' => $this->language->get('home'),
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('tool/sms', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );


        $this->data['href_get_members'] = $this->url->link($this->getAlias().'/getMembers', 'token=' . $this->session->data['token'], 'SSL');
        $this->template = 'tool/sms.tpl';
        $this->children = array(
            'common/header',
            'common/page_header',
            'common/footer',
            'common/page_footer',
            'common/column_left'
        );

        $this->response->setOutput($this->render());
    }

    public function getMembers() {
        $post = $this->request->post;

        $from = 'ALNADIL';
        if($post['member_type']=='ALL') {
            $this->model['member'] = $this->load->model('setup/member');
            $members = $this->model['member']->getRows(array(),array('member_name'));
        } elseif($post['member_type']=='Academy') {
            $this->model['member'] = $this->load->model('module/member_activity_schedule');
            $filter = array();
            $filter['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            if(isset($post['sport_id']) && $post['sport_id'] != '') {
                $filter['sport_id'] = $post['sport_id'];
            }
            $members = $this->model['member']->getDistinctRows(array('reg_no','member_name','member_mobile'),$filter,array('member_name'));
        } elseif($post['member_type']=='Ground') {
            $this->model['member'] = $this->load->model('ground/booking_schedule');
            $filter = array();
            $filter['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            if(isset($post['ground_area_id']) && $post['ground_area_id'] != '') {
                $filter['ground_area_id'] = $post['ground_area_id'];
            }
            $members = $this->model['member']->getDistinctRows(array('reg_no','member_name','mobile_no'),$filter,array('member_name'));
        }


        $html = '';
        $sr = 0;
        foreach($members as $member) {
            if(isset($member['member_mobile'])) {
                $member['mobile_no'] = $member['member_mobile'];
            }
            $sr++;
            $html .= '<tr id="row_'.$sr.'" data-id="'.$sr.'" data-mobile_no="'.$member['mobile_no'].'">'.PHP_EOL;
            $html .= '<td>'.$sr.'</td>'.PHP_EOL;
            $html .= '<td>'.$member['reg_no'].'</td>'.PHP_EOL;
            $html .= '<td>'.$member['member_name'].'</td>'.PHP_EOL;
            $html .= '<td>'.$member['mobile_no'].'</td>'.PHP_EOL;
            $html .= '<td class="status">&nbsp;</td>'.PHP_EOL;
            $html .= '</tr>'.PHP_EOL;
        }
        $json = array(
            'success' => true,
            'post' => $post,
            'from' => $from,
            'html' => $html,
        );

        echo json_encode($json);
    }
}

?>