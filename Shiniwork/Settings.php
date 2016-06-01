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

        protected $document_root    = '';
        protected $root_path        = '';
        protected $config_directory = '';

        /**
         * Settings constructor.
         *
         * @param array $settings
         */
        public function __construct (array $settings = [])
        {
            $settings            = array_merge($this->default_settings, $settings);
            $DS                  = DIRECTORY_SEPARATOR;
            $this->document_root = !empty($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] . $DS : '';
            $this->root_path     = !empty($this->document_root) ? dirname($this->document_root) . $DS : '';

            $config_directory       = $this->root_path . $DS . trim($settings['config_directory'], '/');
            $this->config_directory = !empty($this->root_path) && is_dir($config_directory) ? $config_directory . $DS : '';

            $this->configureMode()
                 ->parseConfig();

            parent::__construct(['settings' => $this->settings]);
        }

        /**
         * Configure website environment with $_SERVER['HTTP_HOST'] (development, preprod, prod)
         *
         * @return Settings $this
         */
        protected function configureMode ()
        {
            $host      = $_SERVER['HTTP_HOST'];
            $host_part = explode('.', $host);
            $mode      = 'production';

            if (preg_match('/dev(:[0-9])?/', end($host_part))) {
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
         * @return Settings $this
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

        /**
         * Get document root
         *
         * @return string
         */
        public function getDocumentRoot ()
        {
            return $this->document_root;
        }

        /**
         * Get root path
         *
         * @return string
         */
        public function getRootPath ()
        {
            return $this->root_path;
        }

        /**
         * Get production mode
         *
         * @return string
         */
        public function getMode ()
        {
            return $this->mode;
        }
    }