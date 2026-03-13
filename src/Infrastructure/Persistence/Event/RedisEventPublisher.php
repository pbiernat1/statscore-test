<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Event;

use App\Domain\Event\Type\Event;
use Predis\Client as RedisClient;

class RedisEventPublisher
{
    public function __construct(
        private readonly RedisClient $publishClient,
        private readonly RedisClient $subscribeClient
    ) {
    }

    public function publish(Event $event): void
    {
        $json = json_encode($event->toArray());

        $this->publishClient->publish(
            sprintf(RedisEventStorage::PATTERN_STREAM_MATCH, $event->getMatchId()),
            $json
        );

        $this->publishClient->publish(RedisEventStorage::PATTERN_STREAM_GLOBAL, $json);
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