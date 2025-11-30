<?php

class Url {

    private $url;
    private $ssl;
    private $hook = array();

    public function __construct($url, $ssl) {
        $this->url = $url;
        $this->ssl = $ssl;
    }

    public function link($route, $args = '', $connection = 'NONSSL', $filter = FALSE) {
        if ($connection == 'NONSSL') {
            if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
                $url = $this->ssl;
            } else {
                $url = $this->url;
            }
        } else {
            $url = $this->ssl;
        }

        $url .= 'index.php?route=' . $route;
//        d($registry->session);
//        if(isset($this->session->data['token']) && $this->session->data['token']) {
//            $url .= '&token=' . $this->session->data['token'];
//        }

        if($filter) {
            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }
            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }
            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }
        }
        if ($args) {
//            $url .= str_replace('&', '&amp;', '&' . ltrim($args, '&'));
            $url .= '&' . ltrim($args, '&');
        }

        return $this->rewrite($url);
    }

    public function addRewrite($hook) {
        $this->hook[] = $hook;
    }

    public function rewrite($url) {
        foreach ($this->hook as $hook) {
            $url = $hook->rewrite($url);
        }

        return $url;
    }

}

?>