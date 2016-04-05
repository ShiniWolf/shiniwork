<?php

    namespace Shiniwork\Validator;


    /**
     * Class Rule
     * @package Shiniwork\Validator
     */
    class Rule
    {
        protected $rule  = '';
        protected $value = '';

        protected $error         = '';
        protected $default_rules = [
            'required' => 'This field is required',
            'alpha'    => 'This field must contain only letters',
            'alphanum' => 'This field must contain only letters and digits',
            'digit'    => 'This field must contain only digits',
            'numeric'  => 'This field must contain only numbers',
            'email'    => 'This field must contain an email',
            'url'      => 'This field must contain an url',
            'phone'    => 'This field must contain a phone number'
        ];

        /**
         * Rule constructor.
         *
         * @param string $rule
         * @param mixed $value
         */
        public function __construct ($rule, $value)
        {
            $this->rule  = $rule;
            $this->value = trim($value);
        }

        /**
         * Return error message
         *
         * @return string
         */
        public function getError ()
        {
            return $this->error;
        }

        /**
         * Validate the rule and return error
         *
         * @return string
         */
        public function validate ()
        {
            $this->dispatchCheck();

            return $this->error;
        }

        /**
         * Check the rule
         *
         * @return bool
         */
        public function check ()
        {
            $this->dispatchCheck();

            return empty($this->error);
        }

        /**
         * Check if value is empty
         *
         * @return bool
         */
        public function checkRequired ()
        {
            if (empty($this->value)) {
                $this->error = $this->default_rules[$this->rule];
            }

            return empty($this->error);
        }

        /**
         * Check if value is alpha
         *
         * @return bool
         */
        public function checkAlpha ()
        {
            if (!ctype_alpha($this->value)) {
                $this->error = $this->default_rules[$this->rule];
            }

            return empty($this->error);
        }

        /**
         * Check if value is alphanumeric
         *
         * @return bool
         */
        public function checkAlphanum ()
        {
            if (!ctype_alnum($this->value)) {
                $this->error = $this->default_rules[$this->rule];
            }

            return empty($this->error);
        }

        /**
         * Check if value is digit
         *
         * @return bool
         */
        public function checkDigit ()
        {
            if (!ctype_digit($this->value)) {
                $this->error = $this->default_rules[$this->rule];
            }

            return empty($this->error);
        }

        /**
         * Check if value is numeric
         *
         * @return bool
         */
        public function checkNumeric ()
        {
            if (!is_numeric($this->value)) {
                $this->error = $this->default_rules[$this->rule];
            }

            return empty($this->error);
        }

        /**
         * Check if value is an email
         *
         * @return bool
         */
        public function checkEmail ()
        {
            if (!filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
                $this->error = $this->default_rules[$this->rule];
            }

            return empty($this->error);
        }

        /**
         * Check if value is an url
         *
         * @return bool
         */
        public function checkUrl ()
        {
            if (!filter_var($this->value, FILTER_VALIDATE_URL)) {
                $this->error = $this->default_rules[$this->rule];
            }

            return empty($this->error);
        }

        /**
         * Check if value is a phone number
         *
         * @return bool
         */
        public function checkPhone ()
        {
            if (!preg_match('/^[+]?([\d]{0,3})?[\(\.\-\s]?(([\d]{1,3})[\)\.\-\s]*)?(([\d]{3,5})[\.\-\s]?([\d]{4})|([\d]{2}[\.\-\s]?){4})$/', $this->value)) {
                $this->error = $this->default_rules[$this->rule];
            }

            return empty($this->error);
        }

        /**
         * Run check function with rule type
         *
         * @return Rule $this
         */
        protected function dispatchCheck ()
        {
            switch ($this->rule) {
                case 'required':
                    $this->checkRequired();
                    break;
                case 'alpha':
                    $this->checkAlpha();
                    break;
                case 'alphanum':
                    $this->checkAlphanum();
                    break;
                case 'digit':
                    $this->checkDigit();
                    break;
                case 'numeric':
                    $this->checkNumeric();
                    break;
                case 'email':
                    $this->checkEmail();
                    break;
                case 'url':
                    $this->checkUrl();
                    break;
                case 'phone':
                    $this->checkPhone();
                    break;
            }

            return $this;
        }
    }