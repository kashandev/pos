<?php
class ControllerCommonFooter extends Controller {
    protected function index() {
        $this->template = 'common/footer.tpl';
        $this->data['user_theme'] = $this->user->getTheme();
        $this->data['href_upload_image'] = $this->url->link('common/filemanager/upload', 'token=' . $this->session->data['token']);

        $this->data['href_quick_search_product'] = $this->url->link('common/function/QSProduct', 'token=' . $this->session->data['token']);
        $this->data['href_quick_search_ajax_product'] = $this->url->link('common/function/QSAjaxProduct', 'token=' . $this->session->data['token']);

        $this->render();
    }
}
?>