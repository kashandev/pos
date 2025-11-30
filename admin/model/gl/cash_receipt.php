<?php

class ModelGLCashReceipt extends HModel {

    protected function getTable() {
        return 'glt_cash_receipt';
    }

    protected function getView() {
        return 'vw_glt_cash_receipt';
    }

}

?>