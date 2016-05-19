<?php


    namespace Shiniwork;


    use Slim\App;
    use Slim\Http\Request;
    use Slim\Http\Response;
    use Slim\Middleware\JwtAuthentication;
    use Slim\Views\Twig;
    use Slim\Views\TwigExtension;
    use Illuminate\Database\Capsule\Manager;

    /**
     * Class Shiniwork
     * @package Shiniwork
     */
    class Shiniwork extends App
    {
        /**
         * Shiniwork constructor.
         *
         * @param array $settings
         */
        public function __construct (array $settings = [])
        {
            session_start();

            $container = new Settings($settings);
            parent::__construct($container);

            $this->registerView()
                 ->registerDatabase()
                 ->registerMailer()
                 ->registerJWT()
                 ->addJWTMiddleware();
        }

        /**
         * Register twig view in Slim container
         *
         * @return Shiniwork $this
         */
        protected function registerView ()
        {
            $container = $this->getContainer();
            $settings  = $container->get('settings');

            if (!empty($settings['view']) && !empty($settings['view']['twig'])) {
                $container['view'] = function ($c) {
                    $view = new Twig($c->getRootPath() . trim($c->settings['view']['template_path'], '/'), $c->settings['view']['twig']);

                    $view->addExtension(new TwigExtension($c['router'], $c['request']->getUri()));
                    $view->addExtension(new \Twig_Extension_Debug());

                    return $view;
                };
            }

            return $this;
        }

        /**
         * Register Eloquent database in Slim app (use Model now)
         *
         * @return Shiniwork $this
         */
        protected function registerDatabase ()
        {
            $container = $this->getContainer();
            $settings  = $container->get('settings');

            if (!empty($settings['database'])) {
                $capsule = new Manager();
                $capsule->addConnection($settings['database']);
                $capsule->setAsGlobal();
                $capsule->bootEloquent();
            }

            return $this;
        }

        /**
         * Register Swiftmailer in Slim container
         *
         * @return Shiniwork $this
         */
        protected function registerMailer ()
        {
            $container = $this->getContainer();
            $settings  = $container->get('settings');

            if (!empty($settings['mailer'])) {
                $container['mailer'] = function ($c) {
                    $settings = $c->settings['mailer'];

                    $transport = \Swift_SmtpTransport::newInstance($settings['host'], $settings['port'], $settings['security']);
                    $transport
                        ->setUsername($settings['username'])
                        ->setPassword($settings['password']);

                    $mailer = \Swift_Mailer::newInstance($transport);

                    return $mailer;
                };
            }

            return $this;
        }

        /**
         * Register JWT Authenticate in Slim container
         *
         * @return Shiniwork $this
         */
        protected function registerJWT ()
        {
            $container = $this->getContainer();
            $settings  = $container->get('settings');

            if (!empty($settings['jwt'])) {
                $container['jwt'] = function () {
                    return new \stdClass();
                };
            }

            return $this;
        }

        /**
         * Add JWT Middleware with jwt config
         *
         * @return Shiniwork $this
         */
        protected function addJWTMiddleware ()
        {
            $container      = $this->getContainer();
            $settings       = $container->get('settings');
            $extra_settings = [
                'callback' => function (Request $request, Response $response, $args) use ($container) {
                    $container['jwt'] = $args['decoded'];
                },
                'error'    => function (Request $request, Response $response) use ($container) {
                    $settings_jwt = $container->get('settings')['jwt'];
                    if (!empty($settings_jwt['login_page'])) {
                        return $response->withRedirect($container->get('router')->pathFor($settings_jwt['login_page']), 401);
                    }
                    else {
                        return $response->withStatus(401)
                                        ->withJson(['message' => 'Authorization required']);
                    }
                }
            ];

            if (!empty($settings['jwt']['accept_methods'])) {
                $extra_settings['rules'] = [
                    new JwtAuthentication\RequestMethodRule([
                        'passthrough' => $settings['jwt']['accept_methods']
                    ])
                ];
            }

            if (!empty($settings['jwt'])) {
                $jwt = array_merge($settings['jwt'], $extra_settings);

                $this->add(new JwtAuthentication($jwt));
            }

            return $this;
        }
    }