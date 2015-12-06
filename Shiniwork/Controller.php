<?php

    namespace Shiniwork;


    use Slim\Slim;

    class Controller
    {
        protected $app;

        public function __construct ()
        {
            $this->app = Slim::getInstance();
        }

        public function render ($template, $data = [])
        {
            $this->app->render($template, $data);
        }

        public function renderJson ($array, $status_code = 200)
        {
            $array = !is_array($array) ? [$array] : $array;
            $this->app->response->headers->set('Content-Type', 'application/json');
            $this->app->response->setStatus($status_code);
            $this->app->response->setBody(json_encode($array));
        }
    }