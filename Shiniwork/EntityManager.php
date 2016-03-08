<?php

    namespace Shiniwork;


    class EntityManager
    {
        protected $container;

        protected $entity_name = '';
        protected $entity_options = [];
        protected $default_entity_options = [
            'primary_key' => 'id'
        ];

        public function __construct ($container)
        {
            $this->container = $container;
        }

        public function get ($entity_name, $entity_options = [])
        {
            $this->entity_name    = $entity_name;
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

        protected function createQuery ($criterias = [], $options = [])
        {
            $qb     = $this->container->database->createQueryBuilder();
            $limit  = isset($options['limit']) && is_int($options['limit']) ? $options['limit'] : 0;
//            var_dump(new \ReflectionClass(ucfirst($this->entity_name)));
            //            $model = new \ReflectionClass(get_called_class());
            //            $table = new StringType($model->getShortName());
            //            $table = $table->toSnakeCase();
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
            // $stmt->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $model->getName());

            if ($limit === 1) {
                $result = $stmt->fetch();
                if ($result !== false) {
                    // $result->_is_new = false;
                }
            }
            else {
                foreach ($stmt->fetchAll() as $entity) {
                    // $entity->_is_new = false;
                    $result[] = $entity;
                }
            }

            return $result;
        }

        //        protected static function createQuery ($criterias = [], $options = [])
        //        {
        //            $app   = Slim::getInstance();
        //            $qb    = $app->database->createQueryBuilder();
        //            $model = new \ReflectionClass(get_called_class());
        //            $table = new StringType($model->getShortName());
        //            $table = $table->toSnakeCase();
        //            $limit = isset($options['limit']) && is_int($options['limit']) ? $options['limit'] : 0;
        //
        //            $query = $qb->select('*')
        //                ->from($table);
        //
        //            foreach ($criterias as $field => $value) {
        //                $query->andWhere($field . ' = :' . $field)
        //                    ->setParameter($field, $value);
        //            }
        //
        //            if ($limit !== 0) {
        //                $query->setMaxResults($limit);
        //            }
        //
        //            if (isset($options['offset']) && is_int($options['offset'])) {
        //                $query->setFirstResult($options['offset']);
        //            }
        //
        //            if (isset($options['orderBy'])) {
        //                $orderBy = !is_array($options['orderBy']) ? [$options['orderBy']] : $options['orderBy'];
        //                foreach ($orderBy as $key => $value) {
        //                    $field = is_int($key) ? $value : $key;
        //                    $order = is_int($key) ? 'ASC' : $value;
        //                    $query->addOrderBy($field, $order);
        //                }
        //            }
        //
        //            $stmt = $query->execute();
        //            $stmt->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $model->getName());
        //
        //            if ($limit === 1) {
        //                $result = $stmt->fetch();
        //                if ($result !== false) {
        //                    $result->_is_new = false;
        //                }
        //            }
        //            else {
        //                $result = [];
        //                foreach ($stmt->fetchAll() as $entity) {
        //                    $entity->_is_new = false;
        //                    $result[]        = $entity;
        //                }
        //            }
        //
        //            return $result;
        //        }
    }