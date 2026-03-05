<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Event;

use App\Domain\Event\Type\Event;
use Predis\Client as RedisClient;

class RedisEventPublisher
{
    public const CHANNEL_GLOBAL = 'football:stream:all';

    public const CHANNEL_MATCH  = 'football:stream:%s';

    public function __construct(
        private readonly RedisClient $publishClient,
        private readonly RedisClient $subscribeClient
    ) {
    }

    public function publish(Event $event): void
    {
        $json = json_encode($event->toArray());

        $this->publishClient->publish(
            sprintf(self::CHANNEL_MATCH, $event->getMatchId()),
            $json
        );

        $this->publishClient->publish(self::CHANNEL_GLOBAL, $json);
    }

    public function subscribe(string $channel, callable $callback): void
    {
        $subscriber = $this->subscribeClient->pubSubLoop();
        $subscriber->subscribe($channel);

        foreach ($subscriber as $message) {
            if ($message->kind === 'message') {
                $callback($message->payload);
            }

            if (connection_aborted()) {
                break;
            }
        }

        unset($subscriber);
    }
}