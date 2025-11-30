<?php

class ModelProductionBOMDetail extends HModel {

    protected function getTable() {
        return 'prd_bom_detail';
    }

    protected function getView() {
        return 'vw_production_bom_detail';
    }

}

?>