<?php

namespace EntityORM\EntityConnector;

use PDO;
use PDOException;

define('INI_PATH', '../config.ini');
class EntityDriver extends PDO
{
    private string $connectionString;
    private object $params;
    private object $returnPrepare;
    public object|array $returnResult;
    private string $selectString;
    private string $whereString;
    private ?string $queryType = null; //S - Select, U - Update, D - Delete, I - Insert

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

    //todo:  add all available drivers
    private function prepareConnectionStringByDriver(): string
    {
        if ($this->params->driver == "mysql") {
            return "mysql:host=" . $this->params->host . ";dbname=" . $this->params->database;
        }
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
    public function where(string $condition): object
    {
        if (!empty($condition)) {
            $clause = "WHERE";
            $this->whereString = str_pad($clause, strlen($clause) + 2, ' ', STR_PAD_BOTH) . $condition;
            return $this;
        }
        
        throw new \Exception("Where clause requires a condition");
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
}
/**
 * END OF EntityDriver
 */
// $dc = new EntityDriver(null);
// $data = $dc->select("*", "abrs_travel_partner")->where("Traveler_Key = 493")->returnResult();
// var_dump($data->returnResult);
