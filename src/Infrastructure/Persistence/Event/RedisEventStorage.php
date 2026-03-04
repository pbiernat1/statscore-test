<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Event;

use App\Domain\Event\Type\Event;
use App\Domain\Event\EventFactory;
use Predis\Client as RedisClient;

class RedisEventStorage implements EventStorageInterface
{
    private const PATTERN_MATCH  = 'football:events:%s';

    private const PATTERN_GLOBAL = 'football:events:all';

    public function __construct(private readonly RedisClient $redis)
    {
    }

    public function save(Event $event): void
    {
        $json = json_encode($event->toArray());

        $this->redis->rpush(sprintf(self::PATTERN_MATCH, $event->getMatchId()), [$json]);
        $this->redis->rpush(self::PATTERN_GLOBAL, [$json]);
    }

    /**
     * @return array[Event]
     */
    public function getAll(): array
    {
        $items  = $this->redis->lrange(self::PATTERN_GLOBAL, 0, -1);

        return array_map(function (string $item) {
            $data = json_decode($item, true);

            return EventFactory::fromArray($data);
        }, $items);
    }
}
