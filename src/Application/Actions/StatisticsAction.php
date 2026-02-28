<?php
declare(strict_types=1);

namespace App\Application\Actions;

use App\Application\Actions\Action;
use App\Infrastructure\Persistence\Statistics\StatisticsStorageInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class StatisticsAction extends Action
{
    public function __construct(
        protected LoggerInterface $logger,
        protected StatisticsStorageInterface $statsStorage
    ) {
    }

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $matchId = $_GET['match_id'] ?? null;
        $teamId = $_GET['team_id'] ?? null;

        try {
            if ($matchId && $teamId) {
                // Get team statistics for specific match
                $stats = $this->statsStorage->getTeamStatistics($matchId, $teamId);

                return $this->respondWithData([
                    'match_id' => $matchId,
                    'team_id' => $teamId,
                    'statistics' => $stats
                ]);
            } elseif ($matchId) {
                // Get all team statistics for specific match
                $stats = $this->statsStorage->getMatchStatistics($matchId);

                return $this->respondWithData([
                    'match_id' => $matchId,
                    'statistics' => $stats
                ]);
            } else {
                return $this->respondWithData(['error' => 'match_id is required'], 400);
            }
        } catch (\Exception $e) {
            return $this->respondWithData(['error' => $e->getMessage()], 500);
        }
    }
}
