<?php

    namespace Shiniwork\Validator;


    /**
     * Class FieldValidator
     * @package Shiniwork\Validator
     */
    class FieldValidator
    {
        protected $error   = '';
        protected $name    = '';
        protected $value   = null;
        protected $options = [];

        /**
         * FieldValidator constructor.
         *
         * @param string $name
         * @param mixed $value
         * @param array $options
         * @throws \Exception
         */
        public function __construct ($name, $value, array $options)
        {
            $this->name    = $name;
            $this->value   = $value;
            $this->options = $options;

            $this->parseOptions();
        }

        /**
         * Parse fields options (rules, messages)
         *
         * @return FieldValidator $this
         * @throws \Exception
         */
        protected function parseOptions ()
        {
            if (!array_key_exists('rules', $this->options)) {
                throw new \Exception('No rules detected for "' . $this->name . '" key');
            }

            if (!array_key_exists('messages', $this->options)) {
                throw new \Exception('No messages detected for "' . $this->name . '" key');
            }

            $rules    = $this->options['rules'];
            $messages = $this->options['messages'];

            if (!is_array($rules)) {
                $rules = [$rules];
            }

            if (!is_array($messages)) {
                $messages = [$rules[0] => $messages];
            }

            $this->options['rules']    = $rules;
            $this->options['messages'] = $messages;

            return $this;
        }

        /**
         * Check all registered rules
         *
         * @return bool
         */
        public function check ()
        {
            if (!empty($this->options['rules'])) {
                foreach ($this->options['rules'] as $name) {
                    $rule = new Rule($name, $this->value);
                    if (!$rule->check()) {
                        $this->error = !empty($this->options['messages'][$name]) ? $this->options['messages'][$name] : $rule->getError();
                    }
                }
            }

            return empty($this->error);
        }

        /**
         * Validate all registered rules and return last error
         *
         * @return string
         */
        public function validate ()
        {
            $this->check();

            return $this->error;
        }
    }