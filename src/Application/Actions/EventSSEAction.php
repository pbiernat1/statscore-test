<?php
declare(strict_types=1);

namespace App\Application\Actions;

use App\Infrastructure\Persistence\Event\RedisEventPublisher;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class EventSSEAction extends Action
{
    public function __construct(
        protected LoggerInterface $logger,
        private readonly RedisEventPublisher $publisher,
    ) {
    }

    protected function action(): Response
    {
        $matchId = $this->request->getQueryParams()['match_id'] ?? null;

        $response = $this->response
            ->withHeader('Content-Type', 'text/event-stream')
            ->withHeader('Cache-Control', 'no-cache')
            ->withHeader('X-Accel-Buffering', 'no')
            ->withHeader('Connection', 'keep-alive');

        $body = $response->getBody();

        $body->write($this->formatSseMessage('connected', json_encode([
            'message' => 'Connected to event stream',
            'match_id' => $matchId,
        ])));

        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();

        $channel = $matchId
            ? sprintf(RedisEventPublisher::CHANNEL_MATCH, $matchId)
            : RedisEventPublisher::CHANNEL_GLOBAL;

        $this->publisher->subscribe($channel, function (string $eventJson) use ($body): void {
            $body->write($this->formatSseMessage('football_event', $eventJson));

            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        });

        return $response;
    }

    private function formatSseMessage(string $event, string $data): string
    {
        return sprintf("event: %s\ndata: %s\n\n", $event, $data);
    }
}