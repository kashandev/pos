<?php

class ModelProductionBOM extends HModel {

    protected function getTable() {
        return 'prd_bom';
    }

    protected function getView() {
        return 'vw_production_bom';
    }

}

?>