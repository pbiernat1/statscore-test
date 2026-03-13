<?php
declare(strict_types=1);

namespace App\Domain\Event\Validator;

class FoulEventDecorator implements ValidatorInterface
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

        if (!isset($event['affected_player'])) {
            throw new \InvalidArgumentException('Missing required key: affected_player');
        }
    }
}
