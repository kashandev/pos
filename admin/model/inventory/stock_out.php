<?php

class ModelInventoryStockOut extends HModel {

    protected function getTable() {
        return 'ina_stock_out';
    }

    protected function getView() {
        return 'vw_ina_stock_out';
    }

}

?>