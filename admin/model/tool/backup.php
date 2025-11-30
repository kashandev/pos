<?php
class ModelToolBackup extends Model {
    public function restore($sql) {
        foreach (explode(";\n", $sql) as $sql) {
            $sql = trim($sql);

            if ($sql) {
                $this->conn->query($sql);
            }
        }

        $this->cache->delete('*');
    }

    public function getTables() {
        $table_data = array();

        $query = $this->conn->query("SHOW TABLES FROM `" . DB_DATABASE . "`");

        foreach ($query->rows as $result) {
            $table_data[] = $result['Tables_in_' . DB_DATABASE];
        }

        return $table_data;
    }

    public function backup($tables) {
        $output = '';

        foreach ($tables as $table) {
            if (DB_PREFIX) {
                if (strpos($table, DB_PREFIX) === FALSE) {
                    $status = FALSE;
                } else {
                    $status = TRUE;
                }
            } else {
                $status = TRUE;
            }

            if ($status) {
                $output .= 'DROP TABLE IF EXISTS `' . $table . '`;' . "\n";
                $query = $this->conn->query("SHOW CREATE TABLE `" . $table . "`");
                $row = $query->row;
                $output .=$row['Create Table'].';'."\n";

                $query = $this->conn->query("SELECT * FROM `" . $table . "`");
                foreach ($query->rows as $result) {
                    $fields = '';

                    foreach (array_keys($result) as $value) {
                        $fields .= '`' . $value . '`, ';
                    }

                    $values = '';

                    foreach (array_values($result) as $value) {
                        $value = str_replace(array("\x00", "\x0a", "\x0d", "\x1a"), array('\0', '\n', '\r', '\Z'), $value);
                        $value = str_replace(array("\n", "\r", "\t"), array('\n', '\r', '\t'), $value);
                        $value = str_replace('\\', '\\\\',	$value);
                        $value = str_replace('\'', '\\\'',	$value);
                        $value = str_replace('\\\n', '\n',	$value);
                        $value = str_replace('\\\r', '\r',	$value);
                        $value = str_replace('\\\t', '\t',	$value);

                        $values .= '\'' . $value . '\', ';
                    }

                    $output .= 'INSERT INTO `' . $table . '` (' . preg_replace('/, $/', '', $fields) . ') VALUES (' . preg_replace('/, $/', '', $values) . ');' . "\n";
                }

                $output .= "\n";
            }
        }

        return $output;
    }
}
?>