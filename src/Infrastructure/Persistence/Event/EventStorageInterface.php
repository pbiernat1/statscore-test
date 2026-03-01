<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Event;

use App\Domain\DTO\Event\EventDTO;

interface EventStorageInterface
{
    public function save(EventDTO $event): void;

    public function getAll(): array;
}
