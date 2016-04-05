<?php


    namespace Shiniwork\Validator;

    use Slim\Http\Request;


    /**
     * Class Validator
     * @package Shiniwork\Validator
     */
    class Validator
    {
        protected $request;
        protected $params = [];

        protected $fields = [];
        protected $errors = [];

        /**
         * Validator constructor.
         *
         * @param Request $request
         * @param array $fields
         */
        public function __construct (Request $request, array $fields = [])
        {
            $this->request = $request;
            $this->params  = $this->request->getParams();

            if (!empty($fields) && is_array($fields)) {
                $this->addFields($fields);
            }
        }

        /**
         * Add one field to validate
         *
         * @param string $name
         * @param array $options
         *
         * @return Validator $this
         * @throws \Exception
         */
        public function addField ($name, array $options)
        {
            if (array_key_exists($name, $this->params)) {
                if (array_key_exists('rules', $options) && array_key_exists('messages', $options)) {
                    $this->fields[$name] = $options;
                }
                else {
                    if (!array_key_exists('rules', $options)) {
                        throw new \Exception('No rules detected for "' . $name . '" key');
                    }

                    if (!array_key_exists('messages', $options)) {
                        throw new \Exception('No messages detected for "' . $name . '" key');
                    }
                }
            }

            return $this;
        }

        /**
         * Add several fields to validate
         *
         * @param array $fields
         *
         * @return Validator $this
         * @throws \Exception
         */
        public function addFields (array $fields)
        {
            foreach ($fields as $name => $options) {
                $this->addField($name, $options);
            }

            return $this;
        }

        /**
         * Check all registered fields
         *
         * @return bool
         * @throws \Exception
         */
        public function check ()
        {
            if (!empty($this->fields)) {
                foreach ($this->fields as $name => $options) {
                    if (isset($this->params[$name])) {
                        $field_validator = new FieldValidator($name, $this->params[$name], $options);

                        if ($errors = $field_validator->validate()) {
                            $this->errors[$name] = $errors;
                        }
                    }
                    else {
                        throw new \Exception('Parameter "' . $name . '" not found');
                    }

                }
            }

            return count($this->errors) === 0;
        }

        /**
         * Validate all registered fields and return errors
         *
         * @return array
         */
        public function validate ()
        {
            $this->check();

            return $this->errors;
        }
    }
