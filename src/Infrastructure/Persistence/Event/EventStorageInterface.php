<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Event;

interface EventStorageInterface
{
    public function save(array $event): void;

    public function getAll(): array;
}
