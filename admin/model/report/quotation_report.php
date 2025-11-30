<?php

class ModelReportQuotationReport extends HModel{
    protected function getTable() {
        return 'ins_quotation';
    }

    protected function getView() {
        return 'vw_ins_quotation';
    }
}