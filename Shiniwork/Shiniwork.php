<?php

    namespace Shiniwork;


    use Doctrine\DBAL\Configuration;
    use Doctrine\DBAL\DriverManager;
    use Slim\App;
    use Slim\Views\Twig;
    use Slim\Views\TwigExtension;

    class Shiniwork extends App
    {
        public function __construct ()
        {
            session_start();

            $container = new Settings();
            parent::__construct($container);

            $this->registerView();
            $this->registerDatabase();
        }

        protected function registerView ()
        {
            $container = $this->getContainer();
            $settings  = $container->settings;

            if (!empty($settings['view']) && !empty($settings['view']['twig'])) {
                $container['view'] = function ($c) {
                    $view = new Twig($c->settings['view']['template_path'], [
                        'cache' => $c->settings['view']['twig']['cache']
                    ]);

                    $view->addExtension(new TwigExtension($c['router'], $c['request']->getUri()));

                    return $view;
                };
            }
            else {
                throw new \Exception('Key "view" and "view[twig]" not found in config file');
            }

            return $this;
        }

        protected function registerDatabase ()
        {
            $container = $this->getContainer();
            $settings  = $container->settings;

            if (!empty($settings['database'])) {
                $container['database'] = function ($c) {
                    return DriverManager::getConnection($c->settings['database'], new Configuration());
                };
            }
            else {
                throw new \Exception('Key "database" not found in config file');
            }

            return $this;
        }
    }