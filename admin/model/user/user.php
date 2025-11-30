<?php

class ModelUserUser extends HModel {
    protected $isAdmin = true;

    protected function getAlias() {
        return 'user/user';
    }

    protected function getTable() {
        return 'user';
    }

    public function getBranchUsers($company_id) {
        $sql = "SELECT u.*";
        $sql .= " FROM `" . DB_PREFIX . $this->getView(). "` u";
        $sql .= " INNER JOIN `" . DB_PREFIX . "user_branch_access` uba
         ON uba.user_id = u.user_id AND uba.company_id = '".$company_id."'";

        $query = $this->conn->query($sql);
        return $query->rows;
    }
}

?>