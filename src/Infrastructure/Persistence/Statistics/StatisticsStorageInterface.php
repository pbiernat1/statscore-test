<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Statistics;

interface StatisticsStorageInterface
{
    public function updateTeamStatistics(string $matchId, string $teamId, string $statType, int $value = 1): void;

    public function getTeamStatistics(string $matchId, string $teamId): array;

    public function getMatchStatistics(string $matchId): array;
}
