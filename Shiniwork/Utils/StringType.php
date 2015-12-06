<?php

    namespace Shiniwork\Utils;


    class StringType
    {
        protected $string = '';

        public function __construct ($string = '')
        {
            $this->string = $string;
        }

        public function toCamelCase ()
        {
            $this->string = str_replace(' ', '', ucwords(str_replace('_', ' ', $this->string)));

            return $this->string;
        }

        public function toLowerCamelCase ()
        {
            $this->string = lcfirst($this->toCamelCase());

            return $this->string;
        }

        public function toSnakeCase ()
        {
            $this->string = preg_replace_callback('/[A-Z]/', function ($matches) {
                return '_' . strtolower($matches[0]);
            }, str_replace(' ', '_', $this->string));
            $this->string = trim($this->string, '_');

            return $this->string;
        }

        public function __toString ()
        {
            return $this->string;
        }
    }