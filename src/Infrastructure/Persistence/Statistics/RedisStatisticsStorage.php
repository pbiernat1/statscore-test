<?php

namespace App\Infrastructure\Persistence\Statistics;

use Predis\Client as RedisClient;

class RedisStatisticsStorage implements StatisticsStorageInterface
{
    private const PATTERN = 'football:stats:%s:%s';

    public function __construct(private RedisClient $redis)
    {
    }

    public function updateTeamStatistics(string $matchId, string $teamId, string $eventType, int $value = 1): void
    {
        $key = $this->buildKey($matchId, $teamId);

        $this->redis->hincrby($key,  $eventType, 1);
    }

    public function getMatchStatistics(string $matchId): array
    {
        $key = $this->buildKey($matchId, '*');
        $keys = $this->redis->keys($key);
        $stats = [];

        foreach ($keys as $key) {
            $parts = explode(':', $key);
            $teamId = end($parts);
            $data = $this->redis->hgetall($key);

            $stats[$teamId] = $this->castIntegers($data);
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
