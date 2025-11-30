<?php
class ModelToolReminder extends HModel {
    protected $isAdmin = true;
    protected function getTable() {
        return 'reminder';
    }


}
?>