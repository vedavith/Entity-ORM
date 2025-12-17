<?php
namespace EntityForge\EntityRepository;

use EntityForge\EntityConnector\EntityDriver;
use PDOException;

abstract class BaseRepository implements RepositoryInterface
{
    protected EntityDriver $driver;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct(EntityDriver $driver, ?string $table = null, ?string $primaryKey = null)
    {
        $this->driver = $driver;
        if ($table) {
            $this->table = $table;
        }
        if ($primaryKey) {
            $this->primaryKey = $primaryKey;
        }
    }

    public function find(int $id): ?array
    {
        $res = $this->driver->findById($this->table, $id, $this->primaryKey);
        if ($res instanceof PDOException) {
            return null;
        }
        return $res ?: null;
    }

    public function findAll(): array
    {
        $res = $this->driver->findAll($this->table);
        if ($res instanceof PDOException) {
            return [];
        }
        return $res ?: [];
    }

    public function insert(array $data): int
    {
        $res = $this->driver->insert($this->table, $data);
        if ($res instanceof PDOException) {
            return 0;
        }
        return (int)$res;
    }

    public function update(int $id, array $data): bool
    {
        $where = "`{$this->primaryKey}` = :id";
        $params = ['id' => $id];
        $res = $this->driver->update($this->table, $data, $where, $params);
        if ($res instanceof PDOException) {
            return false;
        }
        return (bool)$res;
    }

    public function delete(int $id): bool
    {
        $where = "`{$this->primaryKey}` = :id";
        $params = ['id' => $id];
        $res = $this->driver->delete($this->table, $where, $params);
        if ($res instanceof PDOException) {
            return false;
        }
        return (bool)$res;
    }
}
