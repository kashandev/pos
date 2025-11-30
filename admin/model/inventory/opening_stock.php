<?php

class ModelInventoryOpeningStock extends HModel {

    protected function getTable() {
        return 'ina_opening_stock';
    }

    protected function getView() {
        return 'vw_ina_opening_stock';
    }

}

?>