<?php


    namespace Shiniwork;

    use Interop\Container\ContainerInterface;
    use Slim\Http\Response;


    /**
     * Class Controller
     * @package Shiniwork
     */
    class Controller
    {
        protected $container;
        protected $global_data = [];

        /**
         * Controller constructor.
         *
         * @param ContainerInterface $container
         */
        public function __construct (ContainerInterface $container)
        {
            $this->container = $container;

            if (method_exists($this, 'registerGlobalData')) {
                $this->registerGlobalData();
            }
        }

        /**
         * Return json response if xhr else redirect to $route_name
         *
         * @param array $data
         * @param string $route_name
         * @param int $status
         * @param array $query_params
         * @return Response
         */
        public function apiResponse (array $data, $route_name, $status = 200, array $query_params = [])
        {
            $request      = $this->container->get('request');
            $response     = $this->container->get('response');
            $router       = $this->container->get('router');
            $redirect_url = $router->pathFor($route_name, $query_params, $data);

            if ($request->isXhr()) {
                return $response->withJson($data, $status);
            }

            return $response->withRedirect($redirect_url, $status);
        }

        /**
         * Render view with global data
         *
         * @param Response $response
         * @param string $filename
         * @param array $data
         * @return mixed
         */
        public function render (Response $response, $filename, array $data = [])
        {
            $data = array_replace_recursive($this->global_data, $data);

            return $this->container->get('view')->render($response, $filename, $data);
        }
    }