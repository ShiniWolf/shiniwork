<?php

    namespace Shiniwork;


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
        }
    }