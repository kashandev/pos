<?php

class ModelInventoryStockAdjustment extends HModel {

    protected function getTable() {
        return 'ina_stock_adjustment';
    }

    protected function getView() {
        return 'vw_ina_stock_adjustment';
    }

}

?>