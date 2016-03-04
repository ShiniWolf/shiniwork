<?php

    namespace Shiniwork;


    use Slim\App;

    class Shiniwork extends App
    {
        public function __construct ($container = [])
        {
            session_start();

            parent::__construct($container);
        }
    }