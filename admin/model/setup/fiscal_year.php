<?php

class ModelSetupFiscalYear extends HModel {
    protected $isAdmin = true;

    protected function getTable() {
        return 'fiscal_year';
    }
}

?>