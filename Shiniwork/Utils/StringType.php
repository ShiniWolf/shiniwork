<?php

    namespace Shiniwork\Utils;


    /**
     * Class StringType
     *
     * @package Shiniwork\Utils
     */
    class StringType
    {
        protected $string = '';

        /**
         * StringType constructor.
         *
         * @param string $string
         */
        public function __construct ($string = '')
        {
            $this->string = $string;
        }

        /**
         * Convert string to camel case
         *
         * @return string
         */
        public function toCamelCase ()
        {
            $this->string = str_replace(' ', '', ucwords(str_replace('_', ' ', $this->string)));

            return $this->string;
        }

        /**
         * Convert string to camel case with first letter in lower case
         *
         * @return string
         */
        public function toLowerCamelCase ()
        {
            $this->string = lcfirst($this->toCamelCase());

            return $this->string;
        }

        /**
         * Convert string to snake case
         *
         * @return string
         */
        public function toSnakeCase ()
        {
            $this->string = preg_replace_callback('/[A-Z]/', function ($matches) {
                return '_' . strtolower($matches[0]);
            }, str_replace(' ', '_', $this->string));
            $this->string = trim($this->string, '_');

            return $this->string;
        }

        /**
         * Return the converted string
         *
         * @return string
         */
        public function __toString ()
        {
            return $this->string;
        }
    }