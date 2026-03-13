<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Statistics;

use Predis\Client as RedisClient;

class RedisStatisticsStorage implements StatisticsStorageInterface
{
    private const PATTERN = 'football:stats:%s:%s';

    public function __construct(private RedisClient $redis)
    {
    }

    public function updateTeamStatistics(string $matchId, string $teamId, string $eventType): void
    {
        $key = $this->buildKey($matchId, $teamId);

        $this->redis->hincrby($key,  $eventType, 1);
    }

    public function getMatchStatistics(string $matchId): array
    {
        $key = $this->buildKey($matchId, '*');
        $cursor = 0;
        $allKeys = [];

        do {
            [$cursor, $batch] = $this->redis->scan($cursor, [
                'MATCH' => $key,
                'COUNT' => 100,
            ]);
            $allKeys = array_merge($allKeys, $batch);
        } while ($cursor !== '0');

        $stats = [];
        foreach ($allKeys as $key) {
            $parts = explode(':', $key);
            $teamId = end($parts);

            $stats[$teamId] = $this->castIntegers($this->redis->hgetall($key));
        }

        return $stats;
    }

    public function getTeamStatistics(string $matchId, string $teamId): array
    {
        $key = $this->buildKey($matchId, $teamId);
        $stats = $this->redis->hgetall($key);

        return $this->castIntegers($stats);
    }

    private function buildKey(string $matchId, string $teamId): string
    {
        return sprintf(self::PATTERN, $matchId, $teamId);
    }

    private function castIntegers(array $stats): array
    {
        return array_map(static fn ($v) => (int) $v, $stats);
    }
}
