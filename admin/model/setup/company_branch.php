<?php

class ModelSetupCompanyBranch extends HModel {
    protected $isAdmin = true;

    protected function getTable() {
        return 'company_branch';
    }

    protected function getView() {
        return 'vw_branch';
    }

}

?>