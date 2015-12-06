<?php

    namespace Shiniwork;


    use CorsSlim\CorsSlim;
    use Doctrine\DBAL\Configuration;
    use Doctrine\DBAL\DriverManager;
    use Slim\Slim;
    use Slim\Views\Twig;
    use Slim\Views\TwigExtension;

    class App
    {
        protected $app;
        protected $config;

        public function __construct ()
        {
            session_start();

            $this->app    = new Slim();
            $this->config = new Config();

            if ($this->app->config('database') !== null) {
                $this->app->database = DriverManager::getConnection($this->app->config('database'), new Configuration());
            }

            if ($this->app->config('render') === 'Twig') {
                $this->app->config('view', new Twig());

                $view = $this->app->view();

                $view->parserOptions    = [
                    'debug' => $this->app->config('debug'),
                    'cache' => $this->config->webroot_path . 'cache'
                ];
                $view->parserExtensions = [
                    new TwigExtension(),
                ];
            }
        }

        public function __call ($name, $arguments)
        {
            $reflection_class = new \ReflectionClass($this->app);

            if ($reflection_class->hasMethod($name)) {
                $reflection_method = $reflection_class->getMethod($name);

                return $reflection_method->invokeArgs($this->app, $arguments);
            }
            else {
                throw new \BadMethodCallException('Method ' . $name . ' not found');
            }
        }

        public function addCors ($origin = '*', $exposeHeaders = ['Content-Type', 'X-Requested-With', 'X-authentication', 'X-client'], $allowMethods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'])
        {
            $cors = new CorsSlim([
                'origin'        => $origin,
                'exposeHeaders' => $exposeHeaders,
                'allowMethods'  => $allowMethods
            ]);

            $this->app->add($cors);
        }
    }