<?php
final class Cache { 
	private $expire = 3600; 

  	public function __construct() {
		$files = glob(DIR_CACHE . 'cache.*');
		
		if ($files) {
			foreach ($files as $file) {
				$time = substr(strrchr($file, '.'), 1);

      			if ($time < time()) {
					if (file_exists($file)) {
						unlink($file);
					}
      			}
    		}
		}
  	}

	public function get($table, $filter=array()) {
        if($filter) {
            $filter = base64_encode(serialize($filter));
        } else {
            $filter = '';
        }
		$files = glob(DIR_CACHE . 'cache.' . $table . ($filter?'.'.$filter:'') . '.*');

		if ($files) {
			$cache = file_get_contents($files[0]);
			return unserialize($cache);
		}
	}

  	public function set($table, $filter=array(), $value) {

    	try {
            $this->delete($table);
            if($filter) {
                $filter = base64_encode(serialize($filter));
            } else {
                $filter = '';
            }

            $file = DIR_CACHE . 'cache.' . $table . ($filter?'.'.$filter:'') . '.' . (time() + $this->expire);
            //d(array($table, $filter, $value, $file), true);
            $handle = fopen($file, 'w');
            fwrite($handle, serialize($value));
            fclose($handle);

            $file = DIR_CACHE . 'cache.' . strlen($file) . '.' . strlen(serialize($filter)) . '.' . (time() + $this->expire);
            $handle = fopen($file, 'w');
            fwrite($handle, serialize($value));
            fclose($handle);

        } catch(Exception $e) {
            d($e, true);
        }
  	}
	
  	public function delete($table, $filter=array()) {
        if($filter) {
            $filter = serialize($filter);
        } else {
            $filter = '';
        }
		$files = glob(DIR_CACHE . 'cache.' . $table . ($filter?'.'.$filter:'') . '.*');
		
		if ($files) {
    		foreach ($files as $file) {
      			if (file_exists($file)) {
					unlink($file);
					clearstatcache();
				}
    		}
		}
  	}
}
?>