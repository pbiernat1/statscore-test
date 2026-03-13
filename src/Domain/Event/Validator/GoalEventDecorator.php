<?php
declare(strict_types=1);

namespace App\Domain\Event\Validator;

class GoalEventDecorator implements ValidatorInterface
{
    public function __construct(
        protected ValidatorInterface $wrapped
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function validate(array $event): void
    {
        $this->wrapped->validate($event);

        if (!isset($event['assisting_player'])) {
            throw new \InvalidArgumentException('Missing required key: assisting_player');
        }
    }
}
