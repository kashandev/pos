<?php

class ModelGLCOA extends HModel {

    protected function getTable() {
        return 'gl0_coa_level3';
    }

    protected function getView() {
        return 'vw_gl0_coa_all';
    }

    protected function getPrimaryKey() {
        return 'coa_id';
    }

}

?>