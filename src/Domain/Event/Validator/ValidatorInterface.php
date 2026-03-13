<?php
declare(strict_types=1);

namespace App\Domain\Event\Validator;

interface ValidatorInterface
{
    /**
     * @throws \InvalidArgumentException
     */
    public function validate(array $event): void;
}
