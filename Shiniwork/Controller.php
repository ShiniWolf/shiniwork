<?php


    namespace Shiniwork;


    /**
     * Class Controller
     * @package Shiniwork
     */
    class Controller
    {
        protected $container;

        /**
         * Controller constructor.
         *
         * @param $container
         */
        public function __construct ($container)
        {
            $this->container = $container;
        }
    }