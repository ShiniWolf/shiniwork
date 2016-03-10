<?php

    namespace Shiniwork\ORM;


    class Entity
    {
        protected $_read_only      = ['_is_new', '_primary_key', '_updated_fields', '_hidden_fields'];
        protected $_is_new         = true;
        protected $_updated_fields = [];
        protected $_hidden_fields  = [];

        public static $_primary_key = 'id';

        public function __construct ($_is_new = true)
        {
            $this->_is_new               = !!$_is_new;
            $this->{self::$_primary_key} = null;
            $this->_read_only[]          = self::$_primary_key;
        }

        final public function initializeFields ($id = null)
        {
            $this->{self::$_primary_key} = !empty($id) ? $id : $this->{self::$_primary_key};
            $this->_is_new               = false;
            $this->_updated_fields       = [];

            return $this;
        }

        final public function isWritable ($name)
        {
            return $name !== '_read_only' && !in_array($name, $this->_read_only);
        }

        final public function isUpdated ($name)
        {
            return in_array($name, $this->_updated_fields);
        }

        final public function __set ($name, $value)
        {
            if ($this->isWritable($name)) {
                $this->$name = $value;

                if (!in_array($name, $this->_updated_fields)) {
                    $this->_updated_fields[] = $name;
                }
            }
        }

        final public function __get ($name)
        {
            if (isset($this->$name)) {
                return $this->$name;
            }

            throw new \InvalidArgumentException('Property ' . $name . ' doesn\'t exists');
        }
    }