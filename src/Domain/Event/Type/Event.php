<?php
declare(strict_types=1);

namespace App\Domain\Event\Type;

interface Event
{
    public function getPlayer(): string;

    public function getTeamId(): string;

    public function getMatchId(): string;

    public function getMinute(): int;

    public function getSecond(): int;

    public function toArray(): array;
}
