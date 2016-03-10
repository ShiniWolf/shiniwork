<?php

    namespace Shiniwork;


    use Shiniwork\ORM\EntityManager;
    use Slim\Slim;

    class Controller
    {
        protected $container;
        protected $em;

        public function __construct ($container)
        {
            $this->container = $container;
            $this->em = new EntityManager($this->container);
        }

//        public function render ($template, $data = [])
//        {
//            $this->app->render($template, $data);
//        }
//
//        public function renderJson ($array, $status_code = 200)
//        {
//            $array = !is_array($array) ? [$array] : $array;
//            $this->app->response->headers->set('Content-Type', 'application/json');
//            $this->app->response->setStatus($status_code);
//            $this->app->response->setBody(json_encode($array));
//        }
    }