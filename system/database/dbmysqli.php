<?php
final class DBMySQLi {
	private $link;
    private $isTransactionStarted;

	public function __construct($hostname, $username, $password, $database) {
		$this->link = new mysqli($hostname, $username, $password, $database);

		if (mysqli_connect_error()) {
			throw new ErrorException('Error: Could not make a database link (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
		}

		$this->link->set_charset("utf8");
		$this->link->query("SET SQL_MODE = ''");
	}

    public function select_db($db_name) {
        $this->link->select_db($db_name);
    }

	public function multi_query($sql) {
        $query = $this->link->multi_query($sql);
        //$query = $this->link->query("SELECT * FROM `company`");
        $multi_result = array();
        if (!$this->link->errno){

            do {
                if ($res=$this->link->store_result()) {
                    if (isset($res->num_rows)) {
                        $data = array();

                        while ($row = $res->fetch_assoc()) {
                            $data[] = $row;
                        }

                        $result = new stdClass();
                        $result->num_rows = count($data);
                        $result->row = isset($data[0]) ? $data[0] : array();
                        $result->rows = $data;
                        $result->sql = $sql;

                        unset($data);
                        $multi_result[] = $result;
                    }
                }
            } while ($this->link->next_result());

            return $multi_result;
        } else {
            d(array('Error: ' . $this->link->error, 'Error No: ' . $this->link->errno, $sql));
            if($this->isTransactionStarted) {
                $this->rollback();
            }
            exit;
        }
    }

    public function query($sql) {
		$query = $this->link->query($sql);

		if (!$this->link->errno){
			if (isset($query->num_rows)) {
				$data = array();

				while ($row = $query->fetch_assoc()) {
					$data[] = $row;
				}

				$result = new stdClass();
				$result->num_rows = $query->num_rows;
				$result->row = isset($data[0]) ? $data[0] : array();
				$result->rows = $data;
				$result->sql = $sql;

				unset($data);

				$query->close();

				return $result;
			} else{
				return true;
			}
        } else {
            d(array('Error: ' . $this->link->error, 'Error No: ' . $this->link->errno, $sql));
            if($this->isTransactionStarted) {
                $this->rollback();
            }
            exit;
		}
	}

	public function escape($value) {
		return $this->link->real_escape_string($value);
	}

	public function countAffected() {
		return $this->link->affected_rows;
	}

	public function getLastId() {
		return $this->link->insert_id;
	}

    public function beginTransaction() {
        $this->link->autocommit(false);
        $this->isTransactionStarted = 1;
    }

    public function commit() {
        if($this->isTransactionStarted) {
            $this->link->commit();
        }
        $this->isTransactionStarted = 0;
        $this->link->autocommit(true);
    }

    public function rollback() {
        $this->link->rollback();
        $this->isTransactionStarted = 0;
        $this->link->autocommit(true);
    }

    public function __destruct() {
		$this->link->close();
	}
}
?>