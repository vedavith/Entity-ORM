<?php

namespace EntityForge\EntityConnector;

use PDO;
use PDOException;
use PDOStatement;

define('INI_PATH', __DIR__ . '/../../config.ini');
class EntityDriver extends PDO
{
    private string $connectionString;
    private object $params;
    private string $joins;
    private ?string $queryType = null;
    private ?string $selectString = null;
    private ?string $whereString = null;
    private ?string $joinString = null;
    private ?\PDOStatement $returnPrepare = null;
    private array $results = [];
    // Backward-compatible public properties expected by legacy callers
    public array $returnResult = [];
    public array $data = [];

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
        } else {
            $this->params = $configOverride;
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

    public function select(string $table, $values = []): object
    {
        $this->queryType = "S";
        // Determine columns
        if (empty($values)) {
            $colString = '*';
        } elseif (is_array($values)) {
            $colString = implode(', ', array_map(function($c){ return $c; }, $values));
        } else {
            $colString = (string)$values;
        }

        $this->selectString = "SELECT {$colString} FROM {$table}";
        return $this;
    }

    // Accept either a string condition or an array with 'and' and/or 'or' keys.
    // Example: ['and' => ['a = 1', 'b = 2'], 'or' => ['c = 3']]
    public function where($condition): object {
        if (empty($condition)) {
            throw new \Exception("Where clause requires a condition");
        }

        // String condition: use as-is
        if (is_string($condition)) {
            $this->whereString = ' WHERE ' . $condition;
            return $this;
        }

        // Array condition: build groups for 'and' and 'or'
        if (is_array($condition)) {
            $groups = [];

            if (isset($condition['and']) && is_array($condition['and']) && !empty($condition['and'])) {
                $andParts = array_map(function ($c) {
                    return '(' . $c . ')';
                }, $condition['and']);
                $groups[] = '(' . implode(' AND ', $andParts) . ')';
            }

            if (isset($condition['or']) && is_array($condition['or']) && !empty($condition['or'])) {
                $orParts = array_map(function ($c) {
                    return '(' . $c . ')';
                }, $condition['or']);
                $groups[] = '(' . implode(' OR ', $orParts) . ')';
            }

            // Support LIKE conditions: provide array of expressions (e.g. "col LIKE '%foo%'")
            if (isset($condition['like']) && is_array($condition['like']) && !empty($condition['like'])) {
                $likeParts = array_map(function ($c) {
                    return '(' . $c . ')';
                }, $condition['like']);
                $groups[] = '(' . implode(' OR ', $likeParts) . ')';
            }

            // Support BETWEEN conditions: provide array of expressions (e.g. "col BETWEEN 1 AND 10")
            if (isset($condition['between']) && is_array($condition['between']) && !empty($condition['between'])) {
                $betweenParts = array_map(function ($c) {
                    return '(' . $c . ')';
                }, $condition['between']);
                $groups[] = '(' . implode(' OR ', $betweenParts) . ')';
            }

            if (empty($groups)) {
                throw new \Exception("Where clause array must contain non-empty 'and' or 'or' keys");
            }

            // If both 'and' and 'or' groups are present, combine groups with AND
            // Result example: (a AND b) AND (c OR d)
            $whereBody = implode(' AND ', $groups);
            $this->whereString = ' WHERE ' . $whereBody;
            return $this;
        }

        throw new \Exception("Unsupported where clause type");
    }

    public function joins(array $tables, array $joinConditions): self {
        // Build simple joins from parallel arrays: tables => joinConditions
        if (empty($tables) || empty($joinConditions) || count($tables) !== count($joinConditions)) {
            throw new \Exception("Join clause requires matching tables and join conditions");
        }
        $parts = [];
        foreach ($tables as $i => $tbl) {
            $cond = $joinConditions[$i] ?? null;
            if (empty($cond)) {
                throw new \Exception("Join condition missing for table {$tbl}");
            }
            $parts[] = "JOIN {$tbl} ON {$cond}";
        }
        $this->joinString = ' ' . implode(' ', $parts);
        return $this;
    }

    private function prepareCustomQuery(): object
    {
        try {
            $query = '';
            if (!empty($this->queryType)) {
                if ($this->queryType == 'S' && (!empty($this->selectString))) {
                    $query = $this->selectString;
                }
                if (!empty($this->joinString)) {
                    $query .= $this->joinString;
                }
                if (!empty($this->whereString)) {
                    $query .= $this->whereString;
                }
            }
            if (empty($query)) {
                throw new \Exception('No query to prepare');
            }
            $this->returnPrepare = $this->prepare($query);
            return $this;
        } catch (PDOException | \Exception $ex) {
            return $ex;
        }
    }

    private function executeQuery(): object
    {
        try {
            $this->prepareCustomQuery();
            if ($this->returnPrepare instanceof \PDOStatement) {
                $this->returnPrepare->execute();
            } else {
                throw new \Exception('Prepare failed');
            }
        } catch (PDOException $ex) {
            return $ex;
        }
        return $this;
    }

    public function returnResult(): object
    {
        $this->executeQuery();
        $this->results = $this->returnPrepare instanceof \PDOStatement ? $this->returnPrepare->fetchAll(\PDO::FETCH_ASSOC) : [];
        // backward compatible property used in some places
        // Backward-compatible public property for callers expecting $obj->returnResult
        $this->returnResult = $this->results;
        // Also expose common alias
        $this->data = $this->results;
        return $this;
    }

    /**
     * findAll - fetch all rows from a table
     * Returns an array of associative rows or a PDOException on error
     */
    public function findAll(string $table)
    {
        try {
            $sql = "SELECT * FROM `{$table}`";
            $stmt = $this->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $rows ?: [];
        } catch (PDOException $ex) {
            return $ex;
        }
    }

    /**
     * findById - fetch single row by primary key
     * Returns associative row, null if not found, or PDOException on error
     */
    public function findById(string $table, int $id, string $primaryKey = 'id')
    {
        try {
            $sql = "SELECT * FROM `{$table}` WHERE `{$primaryKey}` = :id LIMIT 1";
            $stmt = $this->prepare($sql);
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $ex) {
            return $ex;
        }
    }

    /**
     * find - convenience alias that returns all rows (keeps backward compatibility if callers expect find to list)
     */
    public function find(string $table)
    {
        return $this->findAll($table);
    }

    /**
     * insert - insert a row and return last insert id
     */
    public function insert(string $table, array $data)
    {
        try {
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ':' . $c, $cols);
            $colList = implode(', ', array_map(fn($c) => "`$c`", $cols));
            $phList = implode(', ', $placeholders);
            $sql = sprintf('INSERT INTO `%s` (%s) VALUES (%s)', $table, $colList, $phList);
            $stmt = $this->prepare($sql);
            foreach ($data as $k => $v) {
                $stmt->bindValue(':' . $k, $v);
            }
            $ok = $stmt->execute();
            if (!$ok) {
                return 0;
            }
            return (int)$this->lastInsertId();
        } catch (PDOException $ex) {
            return $ex;
        }
    }

    /**
     * update - update rows matching a where clause. $where is a string (with placeholders) and $params are bound values.
     */
    public function update(string $table, array $data, string $where, array $params = []): bool|
    \PDOException
    {
        try {
            $sets = [];
            foreach (array_keys($data) as $col) {
                $sets[] = "`$col` = :$col";
            }
            $sql = sprintf('UPDATE `%s` SET %s WHERE %s', $table, implode(', ', $sets), $where);
            $stmt = $this->prepare($sql);
            foreach ($data as $k => $v) {
                $stmt->bindValue(':' . $k, $v);
            }
            foreach ($params as $k => $v) {
                // allow numeric keys or named keys
                if (is_int($k)) {
                    $stmt->bindValue($k + 1, $v);
                } else {
                    $stmt->bindValue(':' . ltrim($k, ':'), $v);
                }
            }
            return $stmt->execute();
        } catch (PDOException $ex) {
            return $ex;
        }
    }

    /**
     * delete - delete rows matching where clause. $where is a string (with placeholders) and $params are bound values.
     */
    public function delete(string $table, string $where, array $params = []): bool|\PDOException
    {
        try {
            $sql = sprintf('DELETE FROM `%s` WHERE %s', $table, $where);
            $stmt = $this->prepare($sql);
            if (!empty($params)) {
                foreach ($params as $k => $v) {
                    if (is_int($k)) {
                        $stmt->bindValue($k + 1, $v);
                    } else {
                        $stmt->bindValue(':' . ltrim($k, ':'), $v);
                    }
                }
            }
            return $stmt->execute();
        } catch (PDOException $ex) {
            return $ex;
        }
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