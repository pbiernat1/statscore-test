<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Event;

use App\Domain\Event\Type\Event;

interface EventStorageInterface
{
    public function save(Event $event): void;

    public function getAll(): array;
}
