<?php

class ModelInventoryPOSInvoice extends HModel {

    protected function getTable() {
        return 'ins_pos_invoice';
    }

    protected function getView() {
        return 'vw_ins_pos_invoice';
    }

}

?>