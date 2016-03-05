<?php

    namespace Shiniwork;


    use Slim\App;

    class Shiniwork extends App
    {
        public function __construct ()
        {
            session_start();

            $container = new Settings();
            parent::__construct($container);
        }
    }