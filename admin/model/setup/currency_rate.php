<?php

class ModelSetupCurrencyRate extends HModel {

    protected function getTable() {
        return 'core_currency_rate';
    }

    public function getCurrencyRate($currency_id, $date) {
        $sql = "";
        $sql .= " SELECT *";
        $sql .= " FROM core_currency_rate";
        $sql .= " WHERE currency_id = '$currency_id'";
        $sql .= " AND DATE <='$date'";
        $sql .= " ORDER BY DATE DESC";

        $query = $this->conn->query($sql);
        return $query->row;
    }

}

?>