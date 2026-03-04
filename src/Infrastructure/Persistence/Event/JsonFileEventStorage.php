<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Event;

use App\Domain\Event\EventFactory;
use App\Domain\Event\Type\Event;

class JsonFileEventStorage implements EventStorageInterface
{
    public function __construct(
        private string $filePath
    ) {
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
    }

    public function save(Event $event): void
    {
        $line = json_encode($event->toArray()) . PHP_EOL;
        file_put_contents($this->filePath, $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * @return array[Event]
     */
    public function getAll(): array
    {
        if (!file_exists($this->filePath)) {
            return [];
        }

        $content = file_get_contents($this->filePath);
        $items = explode(PHP_EOL, trim($content));

        return array_map(function($item) {
            $data = json_decode($item, true);

            return EventFactory::fromArray($data);
        }, array_filter($items));
    }
}