<?php

    namespace Shiniwork;


    use Slim\Slim;

    class Config
    {
        protected $config_directory = '';
        protected $config_files     = [];

        protected $webroot_path = '';
        protected $root_path    = '';

        protected $mode = 'production';

        public function __construct ()
        {
            $this->webroot_path     = !empty($_SERVER['SCRIPT_FILENAME']) ? dirname($_SERVER['SCRIPT_FILENAME']) . DIRECTORY_SEPARATOR : '';
            $this->root_path        = !empty($this->webroot_path) ? $this->webroot_path . '..' . DIRECTORY_SEPARATOR : '';
            $this->config_directory = !empty($this->root_path) && is_dir($this->root_path . 'config') ? $this->root_path . 'config' . DIRECTORY_SEPARATOR : '';

            $this->configureMode()
                 ->parseConfig();
        }

        public function __get ($name)
        {
            if (isset($this->$name)) {
                return $this->$name;
            }

            throw new \InvalidArgumentException('Property ' . $name . ' doesn\'t exists');
        }

        protected function configureMode ()
        {
            $app     = Slim::getInstance();
            $host    = explode('.', $app->request->getHost());
            $devMode = function () use ($app) {
                $app->config([
                    'log.enable' => false,
                    'debug'      => true
                ]);
            };

            if (end($host) === 'dev') {
                $this->mode = 'development';
            }
            else if (!empty($host[0]) && $host[0] === 'preprod') {
                $this->mode = 'preprod';
            }

            $app->config('mode', $this->mode);
            $app->configureMode('development', $devMode);
            $app->configureMode('preprod', $devMode);
            $app->configureMode('production', function () use ($app) {
                $app->config([
                    'log.enable' => true,
                    'debug'      => false
                ]);
            });

            return $this;
        }

        protected function getConfigFiles ()
        {
            if (is_file($this->config_directory . 'config.ini')) {
                $this->config_files[] = $this->config_directory . 'config.ini';
            }

            if (is_file($this->config_directory . $this->mode . '.ini')) {
                $this->config_files[] = $this->config_directory . $this->mode . '.ini';
            }

            return $this;
        }

        protected function parseConfig ()
        {
            $app    = Slim::getInstance();
            $config = [];

            $this->getConfigFiles();

            foreach ($this->config_files as $file) {
                $config = array_replace_recursive($config, parse_ini_file($file));
            }

            $app->config($config);

            return $this;
        }
    }