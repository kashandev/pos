<?php
class ModelToolReminderEmail extends HModel {
    protected $isAdmin = true;
    protected function getTable() {
        return 'reminder_email';
    }


}
?>