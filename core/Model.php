<?php

namespace core;
use PDO;
use PDOException;

class Model
{
    private static $connection = null;

    /**
     * Connect to database using PDO
     * Settings from dotenv are used to connect to database
     */
    private static function connect():PDO
    {
        if(empty(self::$connection)){
            try {
                $options = [];
                self::$connection = new PDO("mysql:host=".$_ENV['DB_HOST'].";dbname=".$_ENV['DB_NAME'].";charset=utf8;port=".$_ENV['DB_PORT'].";", $_ENV['DB_USER'], $_ENV['DB_PASSWORD'],$options);
                // set the PDO error mode to exception
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                return self::$connection;
            } catch(PDOException $e) {
                echo "Connection failed";
            }
        }

        return self::$connection;
    }

    /**
     * execute a database query
     */
    public function dbQuery(string $pdo_prepared_query, array $pdo_field_variables):array
    {
        $pdo = $this::connect();
        $stmt = $pdo->prepare($pdo_prepared_query);
        $stmt->execute($pdo_field_variables);

        //return query result when a select query is executed
        if($this->getQueryType($pdo_prepared_query) == 'select'){
            return $stmt->fetchAll();
        }else{
            return [];
        }
    }

    /**
     * Get query type
     */
    public function getQueryType(string $query):string
    {
        $parts = explode(' ', $query);
        return strtolower($parts[0]);
    }
}


