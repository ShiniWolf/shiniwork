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
                $entity_name = new StringType(end($entity_name));
                $entity_name = $entity_name->toSnakeCase();

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
                        /*'container' => $this->container,*/
                        '_is_new' => false
                    ]);
            }

            if ($limit === 1) {
                $result = $stmt->fetch();
            }
            else {
                foreach ($stmt->fetchAll() as $entity) {
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