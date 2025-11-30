<?php

class ModelReportStockTransferReport extends HModel{
    protected function getTable() {
        return 'ins_stock_transfer';
    }

    protected function getView() {
        return 'vw_ins_stock_transfer';
    }
}