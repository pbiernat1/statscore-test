<?php
declare(strict_types=1);

namespace App\Domain\Event\Type;

class FoulEvent extends Event
{
    public function __construct(
        protected string $player,
        protected string $affectedPlayer,
        protected string $teamId,
        protected string $matchId,
        protected int $minute,
        protected int $second
    ) {
    }

    public function toArray(): array
    {
        return [
            'type' => 'foul',
            'player' => $this->player,
            'affected_player' => $this->affectedPlayer,
            'team_id' => $this->teamId,
            'match_id' => $this->matchId,
            'minute' => $this->minute,
            'second' => $this->second,
        ];
    }
}
