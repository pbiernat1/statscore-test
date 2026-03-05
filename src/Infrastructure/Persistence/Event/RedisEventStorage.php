<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Event;

use App\Domain\Event\Type\Event;
use App\Domain\Event\EventFactory;
use Predis\Client as RedisClient;

class RedisEventStorage implements EventStorageInterface
{
    private const PATTERN_LIST_MATCH  = 'football:events:%s';

    private const PATTERN_LIST_GLOBAL = 'football:events:all';

    private const PATTERN_STREAM_MATCH  = 'football:stream:%s';

    private const PATTERN_STREAM_GLOBAL = 'football:stream:all';

    public function __construct(private readonly RedisClient $redis)
    {
    }

    public function save(Event $event): void
    {
        $json = json_encode($event->toArray());

        $this->redis->rpush(sprintf(self::PATTERN_LIST_MATCH, $event->getMatchId()), [$json]);
        $this->redis->rpush(self::PATTERN_LIST_GLOBAL, [$json]);

        $this->redis->publish(sprintf(self::PATTERN_STREAM_MATCH, $event->getMatchId()), $json);
        $this->redis->publish(self::PATTERN_STREAM_GLOBAL, $json);
    }

    /**
     * @return array[Event]
     */
    public function getAll(): array
    {
        $items  = $this->redis->lrange(self::PATTERN_LIST_GLOBAL, 0, -1);

        return array_map(function (string $item) {
            $data = json_decode($item, true);

            return EventFactory::fromArray($data);
        }, $items);
    }
}
