<?php

namespace EntityORM\EntityConnector;

use PDO;
use PDOException;
use PDOStatement;

define('INI_PATH', '../config.ini');
class EntityDriver extends PDO
{
    private string $connectionString;
    private object $params;
    private string $joins;

    //todo: This class should be able to generate all CRUD ops on a database
    public function __construct($configOverride = null)
    {
        if (empty($configOverride)) {
            //todo: convert result array into object
            $iniData = (parse_ini_file(INI_PATH, true))['mysql'];
            $this->params = new \stdClass();
            $this->params->host = $iniData['host'];
            $this->params->username = $iniData['username'];
            $this->params->password = $iniData['password'];
            $this->params->database = $iniData['database'];
            $this->params->driver = $iniData['driver'];
        }
        $this->connectionString = $this->prepareConnectionStringByDriver($this->params);
        parent::__construct($this->connectionString, $this->params->username, $this->params->password);
    }

    private function prepareConnectionStringByDriver(): string
    {
        $connString = '';
        if ($this->params->driver == "mysql") {
            $connString =  "mysql:host=" . $this->params->host . ";dbname=" . $this->params->database;
        }
        return $connString;
    }

    public function select($table, $values = []): object
    {
        $this->queryType="S";
        $values = "*";
        if (!empty($values) && is_array($values)) {
            $values = "'" . implode("', '", $values) . "'";
        }

        $this->selectString = "SELECT $values FROM $table";
        return $this;
    }

    //todo: Ideally condition must be in array
    //todo: Array has 2 keys, [[and] => [], [or] => []]
    public function where(string $condition): object {
        if (!empty($condition)) {
            $clause = "WHERE";
            $this->whereString = str_pad($clause, strlen($clause) + 2, ' ', STR_PAD_BOTH) . $condition;
            return $this;
        }
        
        throw new \Exception("Where clause requires a condition");
    }

    public function joins(array $tables, array $joinConditions): self {
        if (empty($table) || empty($condition)) {
            throw new \Exception("Join clause requires a condition");
        }

       return $this;
    }

    private function prepareCustomQuery(): object
    {
        try {
            if (!empty($this->queryType)) {
                if ($this->queryType == 'S' && (!empty($this->selectString))) {
                    $query = $this->selectString;
                }

                if (!empty($this->whereString)) {
                    $query .= $this->whereString;
                }
            }
            $this->returnPrepare = $this->prepare($query);
            return $this;
        } catch (PDOException $ex) {
            return $ex;
        }
    }

    private function executeQuery(): object
    {
        try {
            $this->prepareCustomQuery();
            $this->returnPrepare->execute();
        } catch (PDOException $ex) {
            return $ex;
        }
        return $this;
    }

    public function returnResult(): object
    {
        $this->executeQuery();
        $this->returnResult = $this->returnPrepare->fetchall();
        return $this;
    }

    /**
     * create - Creates table using meta
     */
    public function create($meta): bool|\PDOException
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS $meta->table ($meta->columns)";
            $sqlPrepd = $this->prepare($sql);
            return $sqlPrepd->execute();
        } catch (PDOException $pe) {
            var_dump($pe->getMessage());
            return false;
        }
    }
}
/**
 * END OF EntityDriver
 */
// $dc = new EntityDriver(null);
// $data = $dc->select("*", "abrs_travel_partner")->where("Traveler_Key = 493")->returnResult();
// var_dump($data->returnResult);
