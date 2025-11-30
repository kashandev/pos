<?php

class ModelInventorySalePOS extends HModel {

    protected function getTable() {
        return 'sale_pos';
    }

    protected function getView() {
        return 'vw_sale_pos';
    }

}

?>