<?php
declare(strict_types=1);

namespace App\Domain\Response;

class Statistics
{
    public function __construct(
        public ?string $match_id = null,
        public ?string $team_id = null,
        public ?array $statistics = []
    ) {
    }
}
