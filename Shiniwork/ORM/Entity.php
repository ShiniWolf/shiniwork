<?php

    namespace Shiniwork\ORM;


    class Entity
    {
        protected $container;

        protected $_read_only      = ['_is_new', '_primary_key', '_updated_fields', '_hidden_fields', 'id'];
        protected $_is_new         = true;
        protected $_updated_fields = [];
        protected $_hidden_fields  = [];

        public static $_primary_key = 'id';

        public function __construct ($_is_new = true)
        {
            $this->_is_new = (bool) $_is_new;
        }

        public function __set ($name, $value)
        {
            if ($name !== '_read_only' && !in_array($name, $this->_read_only)) {
                $this->$name = $value;

                if (!in_array($name, $this->_updated_fields)) {
                    $this->_updated_fields[] = $name;
                }
            }
        }

        public function __get ($name)
        {
            if (isset($this->$name)) {
                return $this->$name;
            }

            throw new \InvalidArgumentException('Property ' . $name . ' doesn\'t exists');
        }
    }