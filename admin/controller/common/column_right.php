<?php

class ControllerCommonColumnRight extends Controller {

    public function index() {
//        $this->data['lang'] = $this->load->language('common/column_right');
        $this->template = 'common/column_right.tpl';

        $this->render();
    }

}

?>