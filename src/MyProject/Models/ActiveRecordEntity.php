<?php

namespace MyProject\Models;

use MyProject\Services\Db;

abstract class ActiveRecordEntity 
{
    /**@var int */
    protected $id;

    /**
     * @return int
    */
    public function getId():int 
    {
        return $this->id;
    }

    public function __set($name, $value)
    {
        $camelCaseName = $this->underscoreToCamelCase($name);
        $this->$camelCaseName = $value;
    }

    private function underscoreToCamelCase(string $source): string 
    {
        return lcfirst(str_replace('_', '', ucwords($source, '_')));
    }

    public static function findAll(): array
    {
        $db = Db::getInstance();
        return $db->query('SELECT * FROM `'. static::getTableName() . '`;', [], static::class);
    }

    /**
     * @param int $id
     * return static|null
    */
    public static function getById(int $id): ?self
    {
        $db = Db::getInstance();
        $entities = $db->query(
            'SELECT * FROM `' . static::getTableName() . '`WHERE id=:id;',
            [':id' => $id],
            static::class
        );
        return $entities ? $entities[0] : null;
    }

    /**
     *сохраняем текущее состояние объекта в базе 
     */
    public function save(): void 
    {
        $mappedProperties = $this->mapPropertiesToDbFormat();
        if ($this->id !== null) {
            $this->update($mappedProperties);
        } else {
            $this->insert($mappedProperties);
        }
    }

    public function delete(): void 
    {
        $db = Db::getInstance();
        $db->query(
            'DELETE FROM `' . static::getTableName() . '` WHERE id = :id', 
            [':id' => $this->id]
        );

        $this->id = null;
    }

    //поиск дубликатов, позволяет убедиться что в БД нет пользователя с такими почтой и никнеймом
    public static function findOneByColumn(string $columnName, $value): ?self 
    {
        $db = Db::getInstance();
        $result = $db->query(
            'SELECT * FROM `' . static::getTableName() . '` WHERE `' . $columnName . '` = :value LIMIT 1;',
            [':value' => $value],
            static::class       
        );
        if ($result === []) {
            return null;
        }
        return $result[0];
    }

    abstract protected static function getTableName(): string;

    private function update(array $mappedProperties): void
    {
        //здесь мы обновляем существующую запись в базе
        $columns2params = [];
        $params2values = [];
        $index = 1;

        foreach ($mappedProperties as $column => $value) {
            $param = ':param' . $index; //:param1
            $columns2params[] = $column . ' = ' . $param; //column1 = :param1
            $params2values[$param] = $value; //[:param1 => value1]
            $index++;
        }

        $sql = 'UPDATE ' . static::getTableName() . ' SET ' . implode(', ', $columns2params) . ' WHERE id = ' . $this->id;
        $db = Db::getInstance();
        $db->query($sql, $params2values, static::class);
    }

    private function insert(array $mappedProperties): void 
    {
         //здесь мы создаём новую запись в базе
         $mappedPropertiesNotNull = array_filter($mappedProperties); 
         //отфильтруем элементы в массиве от тех, значение которых = NULL, Это поля id и created_at

         //формируем массив, содержащий названия столбцов в таблице
         $columns = [];
         $paramsNames = [];
         $params2values = [];

        foreach ($mappedPropertiesNotNull as $columnName => $value) {
            $columns[] = '`' . $columnName . '`';
            $paramName = ':' . $columnName;
            $paramsNames[] = $paramName;
            $params2values[$paramName] = $value;
        }
        
        $columnsViaSemicolon = implode(', ', $columns);
        $paramsNamesViaSemicolon = implode(', ', $paramsNames);

        $sql = 'INSERT INTO ' . static::getTableName() . ' (' . $columnsViaSemicolon . ') VALUES (' . $paramsNamesViaSemicolon . ');';
        
        $db = Db::getInstance();
        $db->query($sql, $params2values, static::class);
        $this->id = $db->getLastInsertId();
        $this->refresh();
    }

    private function refresh(): void 
    {
        $objectFromDB = static::getById($this->id);
        $reflector = new \ReflectionObject($objectFromDB);
        $properties = $reflector->getProperties();

        foreach ($properties as $property) {
            $property->setAccessible(true);
            $propertyName = $property->getName();
            $this->$propertyName = $property->getValue($objectFromDB);
        }
    }

    private function mapPropertiesToDbFormat(): array 
    {
        $reflector = new \ReflectionObject($this);
        $properties = $reflector->getProperties();

        $mappedProperties = [];
        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $propertyNameAsUnderscore = $this->camelCaseToUnderscore($propertyName);
            $mappedProperties[$propertyNameAsUnderscore] = $this->$propertyName;
        }

        return $mappedProperties;
    }

    private function camelCaseToUnderscore(string $source): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $source));
    }
}