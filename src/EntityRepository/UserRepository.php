<?php
namespace EntityForge\EntityRepository;

use EntityForge\EntityConnector\EntityDriver;

class UserRepository extends BaseRepository
{
    protected string $table = 'users';
    protected string $primaryKey = 'id';

    public function __construct(EntityDriver $driver)
    {
        parent::__construct($driver, $this->table, $this->primaryKey);
    }
}
