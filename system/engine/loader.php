<?php

final class Loader {

    protected $registry;

    public function __construct($registry) {
        $this->registry = $registry;
    }

    public function __get($key) {
        return $this->registry->get($key);
    }

    public function __set($key, $value) {
        $this->registry->set($key, $value);
    }

    public function library($library) {
        $file = DIR_SYSTEM . 'library/' . $library . '.php';

        if (file_exists($file)) {
            include_once($file);
        } else {
            exit('Error: Could not load library ' . $library . '!');
        }
    }

    public function controller($route, $data = array()) {
        // Sanitize the call
        $route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route);

        $action = new Action($route);
        $output = $action->execute($this->registry, array(&$data));

        if (!($output instanceof Exception)) {
            return $output;
        } else {
            return false;
        }
    }

    public function model($model) {
        $file = DIR_APPLICATION . 'model/' . $model . '.php';
        $class = 'Model' . preg_replace('/[^a-zA-Z0-9]/', '', $model);

        if (file_exists($file)) {
            include_once($file);

            $modelClass = new $class($this->registry);
            $this->registry->set('model_' . str_replace('/', '_', $model), $modelClass);
            $this->registry->set('model[' . $model .']', $modelClass);

            return $modelClass;
        } else {
            d('Error: Could not load model ' . $model . '!');
        }
    }

    public function database($driver, $hostname, $username, $password, $database, $prefix = NULL, $charset = 'UTF8') {
        $file = DIR_SYSTEM . 'database/' . $driver . '.php';
        $class = 'Database' . preg_replace('/[^a-zA-Z0-9]/', '', $driver);

        if (file_exists($file)) {
            include_once($file);

            $this->registry->set(str_replace('/', '_', $driver), new $class());
        } else {
            exit('Error: Could not load database ' . $driver . '!');
        }
    }

    public function config($config) {
        $this->config->load($config);
    }

    public function language($language) {
        return $this->language->load($language);
    }

}

?>