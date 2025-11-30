<?php

class ModelInventoryStockIn extends HModel {

    protected function getTable() {
        return 'ina_stock_in';
    }

    protected function getView() {
        return 'vw_ina_stock_in';
    }

}

?>