<?php

class ModelProductionProduction extends HModel {

    protected function getTable() {
        return 'prd_production';
    }

    protected function getView() {
        return 'vw_production';
    }

}

?>