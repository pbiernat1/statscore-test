<?php
declare(strict_types=1);

namespace App\Domain\Event\Type;

abstract class Event
{
    protected string $player = '';

    protected string $teamId = '';

    protected string $matchId = '';

    protected int $minute = 0;

    protected int $second = 0;

    public function getPlayer(): string
    {
        return $this->player;
    }

    public function getTeamId(): string
    {
        return $this->teamId;
    }

    public function getMatchId(): string
    {
        return $this->matchId;
    }

    public function getMinute(): int
    {
        return $this->minute;
    }

    public function getSecond(): int
    {
        return $this->second;
    }

    abstract public function toArray(): array;
}
