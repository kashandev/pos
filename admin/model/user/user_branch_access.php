<?php

class ModelUserUserBranchAccess extends HModel {
    protected $isAdmin = true;

    protected function getAlias() {
        return 'user/user_branch_access';
    }
    
    protected function getTable() {
        return 'user_branch_access';
    }

}

?>