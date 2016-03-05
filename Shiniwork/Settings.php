<?php


    namespace Shiniwork;


    use Slim\Container;

    class Settings extends Container
    {
        protected $_settings = [];

        public function __construct ()
        {
            $this->configureMode()
                 ->parseConfig();

            parent::__construct(['settings' => $this->_settings]);
        }

        protected function configureMode ()
        {
            $host      = $_SERVER['HTTP_HOST'];
            $host_part = explode('.', $host);
            $mode      = 'production';

            if (end($host_part) === 'dev') {
                $mode = 'development';
            }

            if (!empty($host_part[0]) && $host_part[0] === 'preprod') {
                $mode = 'preprod';
            }

            $this->mode = $mode;

            return $this;
        }

        protected function parseConfig ()
        {
            $DS               = DIRECTORY_SEPARATOR;
            $webroot_path     = !empty($_SERVER['SCRIPT_FILENAME']) ? dirname($_SERVER['SCRIPT_FILENAME']) . $DS : '';
            $root_path        = !empty($webroot_path) ? $webroot_path . '..' . $DS : '';
            $config_directory = !empty($root_path) && is_dir($root_path . 'app' . $DS . 'config') ? $root_path . 'app' . $DS . 'config' . $DS : '';
            $config_files     = [];

            if (is_file($config_directory . 'config.json')) {
                $config_files[] = $config_directory . 'config.json';
            }

            if (is_file($config_directory . $this->mode . '.json')) {
                $config_files[] = $config_directory . $this->mode . '.json';
            }

            foreach ($config_files as $file) {
                $this->_settings = array_replace_recursive($this->_settings, json_decode(file_get_contents($file), true));
            }

            return $this;
        }
    }