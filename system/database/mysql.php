<?php

final class MySQL {

    private $connection;
    private $isTransactionStarted;

    public function __construct($hostname, $username, $password, $database) {
        if (!$this->connection = mysql_connect($hostname, $username, $password)) {
            exit('Error: Could not make a database connection using ' . $username . '@' . $hostname);
        }

        if (!mysql_select_db($database, $this->connection)) {
            exit('Error: Could not connect to database ' . $database);
        }

        mysql_query("SET NAMES 'utf8'", $this->connection);
        mysql_query("SET CHARACTER SET utf8", $this->connection);
        mysql_query("SET CHARACTER_SET_CONNECTION=utf8", $this->connection);
        mysql_query("SET SQL_MODE = ''", $this->connection);
    }

    public function query($sql) {
        $resource = mysql_query($sql, $this->connection);

        if ($resource) {
            if (is_resource($resource)) {
                $i = 0;

                $data = array();

                while ($result = mysql_fetch_assoc($resource)) {
                    $data[$i] = $result;

                    $i++;
                }

                mysql_free_result($resource);

                $query = new stdClass();
                $query->row = isset($data[0]) ? $data[0] : array();
                $query->rows = $data;
                $query->num_rows = $i;

                unset($data);

                return $query;
            } else {
                return TRUE;
            }
        } else {
            if($this->isTransactionStarted) {
                $this->rollback();
            }
            d(array('Error: ' . mysql_error($this->connection), 'Error No: ' . mysql_errno($this->connection), $sql),true);
           // $_SESSION['error_warning'] = dStr(array('SQL' => $sql, 'Error' => mysql_error($this->connection)));
            //d($this->session->data['error_warning'],true);
          //  header( 'Location: ' . HTTP_SERVER . 'index.php?route=error/error&token=' . $_REQUEST['token'] ) ;
            return true;
         }
    }

    public function escape($value) {
        return mysql_real_escape_string($value, $this->connection);
    }

    public function countAffected() {
        return mysql_affected_rows($this->connection);
    }

    public function getLastId() {
        return mysql_insert_id($this->connection);
    }

    public function beginTransaction() {
        mysql_query("START TRANSACTION", $this->connection);
        $this->isTransactionStarted = 1;
    }

    public function commit() {
        if($this->isTransactionStarted) {
            mysql_query("COMMIT", $this->connection);
        }
        $this->isTransactionStarted = 0;
    }

    public function rollback() {
        mysql_query("ROLLBACK", $this->connection);
        $this->isTransactionStarted = 0;
    }

    public function __destruct() {
        mysql_close($this->connection);
    }

}

?>