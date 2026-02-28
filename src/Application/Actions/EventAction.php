<?php
declare(strict_types=1);

namespace App\Application\Actions;

use App\Application\Actions\Action;
use App\Domain\Event\EventHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class EventAction extends Action
{
    public function __construct(
        protected LoggerInterface $logger,
        protected EventHandler $handler,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->respondWithData(['error' => 'Invalid JSON'], 400);
        }

        try {
            $result = $this->handler->handleEvent($data);

            return $this->respondWithData($result, 201);
        } catch (\Exception $e) {
            return $this->respondWithData(['error' => $e->getMessage()], 400);
        }
    }
}
