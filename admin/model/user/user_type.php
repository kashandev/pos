<?php

class ModelUserUserType extends HModel {

    protected function getAlias() {
        return 'user/user_type';
    }
    
    protected function getTable() {
        return 'user_type';
    }


    public function getuserType($data = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX . "user_type";

        $sql .= " ORDER BY user_type_id";

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }



}

?>