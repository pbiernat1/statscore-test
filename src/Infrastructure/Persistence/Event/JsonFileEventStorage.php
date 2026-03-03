<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Event;

use App\Domain\DTO\Event\EventDTO;

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

    public function save(EventDTO $eventDTO): void
    {
        $line = json_encode($eventDTO) . PHP_EOL;
        file_put_contents($this->filePath, $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * @return array[EventDTO]
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

            return EventDTO::fromArray($data);
        }, array_filter($items));
    }
}