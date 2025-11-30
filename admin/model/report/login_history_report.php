<?php

class ModelReportLoginHistoryReport extends HModel {
    protected $isAdmin = true;

    public function getReports($datefrom,$dateto,$UserID,$first=false) {

        $sql  = " SELECT *";
        if($first){
            $sql.=",min(login_time) as first_login";
        }
        $sql .= " from login_history";

        $sql .= " WHERE CAST(login_time AS DATE) >= '".$datefrom."' AND  CAST(login_time AS DATE) <= '".$dateto."'";


        if($UserID != '')
        {
            $sql .= " AND user_id = '".$UserID."' " ;
        }
        if($first){
            $sql .= "GROUP BY user_id,DAY(login_time)";
        }
        if($first){
            $sql .= " order by login_time " ;

        }else{
            $sql .= " order by login_time desc " ;

        }



//        d($sql,true);
        $query = $this->conn->query($sql);
        return $query->rows;
    }
}
?>