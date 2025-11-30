<?php

class ModelCommonLoginHistory extends HModel {

	protected $isAdmin = true;

    protected function getTable() {
        return 'login_history';
    }

}

?>