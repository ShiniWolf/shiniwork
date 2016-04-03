<?php


    namespace Shiniwork;


    use Slim\Container;

    /**
     * Class Settings
     * @package Shiniwork
     */
    class Settings extends Container
    {
        protected $default_settings = [
            'config_directory' => 'app/config'
        ];
        protected $settings         = [];
        protected $mode             = 'production';

        protected $webroot_path     = '';
        protected $root_path        = '';
        protected $config_directory = '';

        /**
         * Settings constructor.
         *
         * @param array $settings
         */
        public function __construct (array $settings = [])
        {
            $settings           = array_merge($this->default_settings, $settings);
            $DS                 = DIRECTORY_SEPARATOR;
            $this->webroot_path = !empty($_SERVER['SCRIPT_FILENAME']) ? dirname($_SERVER['SCRIPT_FILENAME']) . $DS : '';
            $this->root_path    = !empty($this->webroot_path) ? $this->webroot_path . '..' . $DS : '';

            $config_directory       = $this->root_path . $DS . trim($settings['config_directory'], '/');
            $this->config_directory = !empty($this->root_path) && is_dir($config_directory) ? $config_directory . $DS : '';

            $this->configureMode()
                 ->parseConfig();

            parent::__construct(['settings' => $this->settings]);
        }

        /**
         * Configure website environment with $_SERVER['HTTP_HOST'] (development, preprod, prod)
         *
         * @return $this
         */
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

        /**
         * Parse config files in $this->config_directory
         * 
         * @return $this
         */
        protected function parseConfig ()
        {
            $config_files = [];

            if (is_file($this->config_directory . 'config.json')) {
                $config_files[] = $this->config_directory . 'config.json';
            }

            if (is_file($this->config_directory . $this->mode . '.json')) {
                $config_files[] = $this->config_directory . $this->mode . '.json';
            }

            foreach ($config_files as $file) {
                $this->settings = array_replace_recursive($this->settings, json_decode(file_get_contents($file), true));
            }

            return $this;
        }
    }