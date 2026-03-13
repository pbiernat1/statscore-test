<?php
declare(strict_types=1);

namespace App\Application\Actions;

use App\Infrastructure\Persistence\Event\RedisEventStorage;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Predis\Client as RedisClient;

class EventSSEAction extends Action
{
    public function __construct(
        protected LoggerInterface $logger,
        private readonly RedisClient $redis,
    ) {
    }

    protected function action(): Response
    {
        $matchId = $this->request->getQueryParams()['match_id'] ?? null;
        $lastId = (int) ($this->request->getQueryParams()['last_id'] ?? 0);

        $response = $this->response
            ->withHeader('Content-Type', 'text/event-stream')
            ->withHeader('Cache-Control', 'no-cache')
            ->withHeader('X-Accel-Buffering', 'no')
            ->withHeader('Connection', 'keep-alive');

        $body = $response->getBody();

        $body->write($this->formatSseMessage('connected', json_encode([
            'match_id' => $matchId,
            'last_id'  => $lastId,
        ])));

        $this->flush();

        $listKey = $matchId
            ? sprintf(RedisEventStorage::PATTERN_LIST_MATCH, $matchId)
            : RedisEventStorage::PATTERN_LIST_GLOBAL;

        $cursor = $lastId;
        $timeout = 30;
        $start = time();

        while (!connection_aborted() && (time() - $start) < $timeout) {
            $items = $this->redis->lrange($listKey, $cursor, -1);

            foreach ($items as $item) {
                $body->write(sprintf("id: %d\n", $cursor));
                $body->write($this->formatSseMessage('football_event', $item));
                $this->flush();
                $cursor++;
            }

            if (empty($items)) {
                usleep(500000);
            }
        }

        $body->write($this->formatSseMessage('reconnect', json_encode(['last_id' => $cursor])));

        return $response;
    }

    private function formatSseMessage(string $event, string $data): string
    {
        return sprintf("event: %s\ndata: %s\n\n", $event, $data);
    }

    private function flush(): void
    {
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }
}