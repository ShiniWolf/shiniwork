<?php

    namespace Shiniwork;


    use Shiniwork\Utils\StringType;
    use Slim\Slim;

    class Model implements \JsonSerializable
    {
        protected $_read_only      = ['_is_new', '_primary_key', '_updated_fields', '_hidden_fields', 'id'];
        protected $_is_new         = true;
        protected $_updated_fields = [];
        protected $_hidden_fields  = [];
        protected $id;

        protected static $_primary_key = 'id';

        public function save ()
        {
            $app          = Slim::getInstance();
            $model        = new \ReflectionClass(get_called_class());
            $table        = new StringType($model->getShortName());
            $table        = $table->toSnakeCase();
            $qb           = $app->database->createQueryBuilder();
            $queryBuilder = function ($type) use ($app, $qb, $model, $table) {
                if (in_array($type, ['insert', 'update']) && ($this->_is_new === true || !empty($this->_updated_fields))) {
                    $values_exists = false;

                    if ($model->hasMethod('beforeSave')) {
                        $this->beforeSave($type);
                    }

                    $qb->$type($table);
                    foreach ($model->getProperties() as $property) {
                        $name = $property->getName();
                        if ($name !== 'read_only' && !in_array($name, $this->_read_only) && ($this->_is_new === true || $this->_is_new === false && in_array($name, $this->_updated_fields))) {
                            $value = $this->$name;

                            if ($this->_is_new) {
                                $qb->setValue($name, ':' . $name);

                                if ($name === 'created' && empty($value)) {
                                    $this->$name = new \DateTime();
                                    $value       = $this->$name->format(\DateTime::W3C);
                                }
                            }
                            else {
                                $qb->set($name, ':' . $name);
                            }

                            $qb->setParameter($name, $value);
                            $values_exists = true;
                        }

                        if ($this->_is_new === false && $name === 'updated') {
                            $this->$name = new \DateTime();

                            $qb->set($name, ':' . $name)
                               ->setParameter($name, $this->$name->format(\DateTime::W3C));
                        }
                    }

                    if ($this->_is_new === false) {
                        $qb->where(self::$_primary_key . ' = :id')
                           ->setParameter('id', $this->id);
                    }

                    if ($values_exists) {
                        $stmt = $qb->execute();

                        if ($stmt) {
                            $this->id              = $this->_is_new ? $app->database->lastInsertId() : $this->id;
                            $this->_is_new         = false;
                            $this->_updated_fields = [];
                        }

                        return $stmt;
                    }
                }

                return false;
            };

            return $this->_is_new ? $queryBuilder('insert') : $queryBuilder('update');
        }

        public static function find ($criterias = [], $options = [])
        {
            $options = array_merge([
                'limit' => 0
            ], $options);

            return self::createQuery($criterias, $options);
        }

        public static function findOne ($id, $options = [])
        {
            $criterias = [self::$_primary_key => $id];
            $options   = array_merge($options, [
                'limit' => 1
            ]);

            return self::createQuery($criterias, $options);
        }

        protected static function findBy ($fields, $values, $options = [])
        {
            array_walk($fields, function (&$value) {
                $value = strtolower($value);
            });
            $criterias = array_combine($fields, $values);

            return self::createQuery($criterias, $options);
        }

        protected static function createQuery ($criterias = [], $options = [])
        {
            $app   = Slim::getInstance();
            $qb    = $app->database->createQueryBuilder();
            $model = new \ReflectionClass(get_called_class());
            $table = new StringType($model->getShortName());
            $table = $table->toSnakeCase();
            $limit = isset($options['limit']) && is_int($options['limit']) ? $options['limit'] : 0;

            $query = $qb->select('*')
                        ->from($table);

            foreach ($criterias as $field => $value) {
                $query->andWhere($field . ' = :' . $field)
                      ->setParameter($field, $value);
            }

            if ($limit !== 0) {
                $query->setMaxResults($limit);
            }

            if (isset($options['offset']) && is_int($options['offset'])) {
                $query->setFirstResult($options['offset']);
            }

            if (isset($options['orderBy'])) {
                $orderBy = !is_array($options['orderBy']) ? [$options['orderBy']] : $options['orderBy'];
                foreach ($orderBy as $key => $value) {
                    $field = is_int($key) ? $value : $key;
                    $order = is_int($key) ? 'ASC' : $value;
                    $query->addOrderBy($field, $order);
                }
            }

            $stmt = $query->execute();
            $stmt->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $model->getName());

            if ($limit === 1) {
                $result = $stmt->fetch();
                if ($result !== false) {
                    $result->_is_new = false;
                }
            }
            else {
                $result = [];
                foreach ($stmt->fetchAll() as $entity) {
                    $entity->_is_new = false;
                    $result[]       = $entity;
                }
            }

            return $result;
        }

        public static function getDb ()
        {
            $app = Slim::getInstance();

            return $app->database;
        }

        public static function __callStatic ($name, $arguments)
        {
            if (preg_match("/findOneBy(.*)/", $name, $field)) {
                if (!empty($field[1])) {
                    $fields  = explode('And', $field[1]);
                    $options = count($fields) !== count($arguments) ? array_pop($arguments) : [];
                    $options = array_merge($options, [
                        'limit' => 1
                    ]);

                    return self::findBy($fields, $arguments, $options);
                }
            }
            else if (preg_match("/findBy(.*)/", $name, $field)) {
                if (!empty($field[1])) {
                    $fields  = explode('And', $field[1]);
                    $options = count($fields) !== count($arguments) ? array_pop($arguments) : [];

                    return self::findBy($fields, $arguments, $options);
                }
            }

            throw new \BadMethodCallException('Method ' . $name . ' not found');
        }

        public function __call ($name, $arguments)
        {
            $app   = Slim::getInstance();
            $model = new \ReflectionClass(get_called_class());

            if ($model->hasProperty($name) && $name !== '_read_only') {
                return $this->$name;
            }

            throw new \BadMethodCallException('Method ' . $name . ' not found');
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

        public function jsonSerialize ()
        {
            $properties = [];
            foreach ($this as $key => $value) {
                if ($key !== '_read_only' && !in_array($key, $this->_read_only) && !in_array($key, $this->_hidden_fields) || $key === 'id') {
                    $properties[$key] = $value;
                }
            }

            return $properties;
        }
    }