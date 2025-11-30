<?php

class ModelGLPayments extends HModel {

    protected function getTable() {
        return 'glt_payments';
    }

    protected function getView() {
        return 'vw_glt_payments';
    }

}

?>