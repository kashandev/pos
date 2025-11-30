<?php

class ModelSetupCompany extends HModel {
    protected $isAdmin = true;

    protected function getTable() {
        return 'company';
    }

}

?>