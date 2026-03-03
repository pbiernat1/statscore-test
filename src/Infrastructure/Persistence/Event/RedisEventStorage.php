<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Event;

use Predis\Client as RedisClient;
use App\Domain\DTO\Event\EventDTO;

class RedisEventStorage implements EventStorageInterface
{
    private const KEY_MATCH  = 'football:events:%s';

    private const KEY_GLOBAL = 'football:events:all';

    public function __construct(private readonly RedisClient $redis)
    {
    }

    public function save(EventDTO $eventDTO): void
    {
        $json = json_encode($eventDTO);

        $this->redis->rpush(sprintf(self::KEY_MATCH, $eventDTO->data->matchId), [$json]);
        $this->redis->rpush(self::KEY_GLOBAL, [$json]);
    }

    /**
     * @return array[EventDTO]
     */
    public function getAll(): array
    {
        $items  = $this->redis->lrange(self::KEY_GLOBAL, 0, -1);

        return array_map(function (string $item) {
            $data = json_decode($item, true);

            return EventDTO::fromArray($data);
        }, $items);
    }
}
