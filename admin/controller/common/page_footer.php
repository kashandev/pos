<?php

class ControllerCommonPageFooter extends Controller {

    public function index() {
        $lang = $this->load->language('common/page_footer');
        $this->data['lang']['copyright'] = sprintf($lang['copyright'],date('Y'));
        $this->data['lang']['developed_by'] = sprintf($lang['developed_by'],date('Y'));
        $this->template = 'common/page_footer.tpl';
        $this->render();
    }

}

?>