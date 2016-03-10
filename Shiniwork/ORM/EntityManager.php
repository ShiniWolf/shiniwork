<?php

    namespace Shiniwork\ORM;


    use Shiniwork\Utils\StringType;

    class EntityManager
    {
        protected $container;

        protected $entity                 = null;
        protected $entity_name            = '';
        protected $entity_options         = [];
        protected $default_entity_options = [];

        public function __construct ($container)
        {
            $this->container = $container;
        }

        public function get ($entity, $entity_options = [])
        {
            if (class_exists($entity)) {
                $entity_name = explode('\\', $entity);
                $entity_name = (new StringType(end($entity_name)))->toSnakeCase();

                $this->entity      = $entity;
                $this->entity_name = $entity_name;
            }
            else {
                $this->entity      = __NAMESPACE__ . '\Entity';
                $this->entity_name = $entity;
            }

            $this->entity_options = array_merge($this->default_entity_options, $entity_options);

            return $this;
        }

        public function save ($entity)
        {
            $execute = function ($type, $entity) {
                $updatedFields = $entity->_updated_fields;

                if ($entity->_is_new === true || !empty($updatedFields)) {
                    $model   = new \ReflectionClass($entity);
                    $qb      = $this->container->database->createQueryBuilder();
                    $table   = (new StringType($model->getShortName()))->toSnakeCase();
                    $updated = false;

                    // Before save hook
                    if (method_exists($entity, 'beforeSave')) {
                        $entity->beforeSave($type);
                    }

                    // Set request type INSERT or UPDATE
                    $qb->$type($table);

                    // Set value to all accessible properties
                    foreach ($model->getProperties() as $property) {
                        $name = $property->getName();
                        if ($entity->isWritable($name) && (!!$entity->_is_new || !$entity->_is_new && $entity->isUpdated($name))) {
                            $value = $entity->$name;

                            // Set created date for new entity
                            if (!!$entity->_is_new) {
                                $qb->setValue($name, ':' . $name);

                                if ($name === 'created' && empty($value)) {
                                    $entity->$name = new \DateTime();
                                    $value         = $entity->$name->format(\DateTime::W3C);
                                }
                            }
                            else {
                                $qb->set($name, ':' . $name);
                            }

                            $qb->setParameter($name, $value);
                            $updated = true;
                        }

                        // Set updated date for existing entity
                        if (!$entity->_is_new && $name === 'updated') {
                            $entity->$name = new \DateTime();

                            $qb->set($name, ':' . $name)
                                ->setParameter($name, $entity->$name->format(\DateTime::W3C));
                        }
                    }

                    // Set id clause for existing entity
                    if ($entity->_is_new === false) {
                        $qb->where($entity::$_primary_key . ' = :id')
                            ->setParameter('id', $entity->{$entity::$_primary_key});
                    }

                    // Execute only if fields updated
                    if (!!$updated) {
                        $stmt = $qb->execute();

                        if ($stmt) {
                            $entity->initializeFields($entity->_is_new ? $this->container->database->lastInsertId() : $entity->id);
                        }

                        return $stmt;
                    }
                }

                return false;
            };

            return $entity->_is_new ? $execute('insert', $entity) : $execute('update', $entity);
        }

        public function find ($criterias = [], $options = [])
        {
            $options = array_merge([
                'limit' => 0
            ], $options);

            return $this->createQuery($criterias, $options);
        }

        public function findOne ($id, $options = [])
        {
            $entity    = $this->entity;
            $criterias = [$entity::$_primary_key => $id];
            $options   = array_merge($options, [
                'limit' => 1
            ]);

            return $this->createQuery($criterias, $options);
        }

        protected function findBy ($fields, $values, $options = [])
        {
            array_walk($fields, function (&$value) {
                $value = strtolower($value);
            });
            $criterias = array_combine($fields, $values);

            return $this->createQuery($criterias, $options);
        }

        protected function createQuery ($criterias = [], $options = [])
        {
            $qb     = $this->container->database->createQueryBuilder();
            $limit  = isset($options['limit']) && is_int($options['limit']) ? $options['limit'] : 0;
            $result = [];

            $qb->select('*')->from($this->entity_name);

            // Parse where clause
            foreach ($criterias as $field => $value) {
                $qb->andWhere($field . ' = :' . $field)
                    ->setParameter($field, $value);
            }

            // Set limit
            if ($limit !== 0) {
                $qb->setMaxResults($limit);
            }

            // Set offset
            if (isset($options['offset']) && is_int($options['offset'])) {
                $qb->setFirstResult($options['offset']);
            }

            // Set order by
            if (isset($options['orderBy'])) {
                $orderBy = !is_array($options['orderBy']) ? [$options['orderBy']] : $options['orderBy'];
                foreach ($orderBy as $key => $value) {
                    $field = is_int($key) ? $value : $key;
                    $order = is_int($key) ? 'ASC' : $value;
                    $qb->addOrderBy($field, $order);
                }
            }

            $stmt = $qb->execute();

            if (!empty($this->entity)) {
                $stmt->setFetchMode(
                    \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE,
                    $this->entity,
                    [
                        '_is_new' => false
                    ]);
            }

            if ($limit === 1) {
                $result = $stmt->fetch();
                if ($result) {
                    $result->initializeFields();
                }
            }
            else {
                foreach ($stmt->fetchAll() as $entity) {
                    $entity->initializeFields();
                    $result[] = $entity;
                }
            }

            return $result;
        }

        public function __call ($name, $arguments)
        {
            if (preg_match("/findOneBy(.*)/", $name, $field)) {
                if (!empty($field[1])) {
                    $fields  = explode('And', $field[1]);
                    $options = count($fields) !== count($arguments) ? array_pop($arguments) : [];
                    $options = array_merge($options, [
                        'limit' => 1
                    ]);

                    return $this->findBy($fields, $arguments, $options);
                }
            }
            else if (preg_match("/findBy(.*)/", $name, $field)) {
                if (!empty($field[1])) {
                    $fields  = explode('And', $field[1]);
                    $options = count($fields) !== count($arguments) ? array_pop($arguments) : [];

                    return $this->findBy($fields, $arguments, $options);
                }
            }

            throw new \BadMethodCallException('Method ' . $name . ' not found');
        }
    }