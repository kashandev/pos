<?php

class ModelGLCashPayment extends HModel {

    protected function getTable() {
        return 'glt_cash_payment';
    }

   protected function getView() {
       return 'vw_glt_cash_payment';
   }

}

?>