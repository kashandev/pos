<?php

class ModelUserUserPermission extends HModel {
    protected $isAdmin = true;

    protected function getAlias() {
        return 'user/user_permission';
    }
    
    protected function getTable() {
        return 'user_permission';
    }

}

?>