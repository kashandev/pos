<?php

final class Session {

    public $data = array();

    public function __construct($name='fas') {
        session_name($name);
        session_start();
        if (!session_id()) {
            ini_set('session.use_cookies', 'On');
            ini_set('session.use_trans_sid', 'Off');

            //session_set_cookie_params(86400, '/');
        }
        $_SESSION['start_time'] = date('Y-m-d H:i:s');
        $json = serialize($_SESSION);
        //setcookie(session_name(),session_id(),time()+86400,'/');
        //setcookie(session_name(),$json,time()+86400,'/');
        $this->data = & $_SESSION;
    }

}

?>