<?php
namespace EntityForge\EntityRepository;

interface RepositoryInterface
{
    public function find(int $id): ?array;
    public function findAll(): array;
    public function insert(array $data): int;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
}
