<?php

class ModelSchoolStudent extends HModel {

    protected function getTable() {
        return 'sch_student';
    }

    protected function getView() {
        return 'vw_sch_student';
    }

}

?>