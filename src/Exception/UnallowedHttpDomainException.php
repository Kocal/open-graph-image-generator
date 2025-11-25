<?php

declare(strict_types=1);

namespace App\Exception;

use Throwable;

final class UnallowedHttpDomainException extends \InvalidArgumentException
{
    public function __construct(?string $httpDomain, ?Throwable $previous = null)
    {
        parent::__construct(
            message: $httpDomain
                ? sprintf('The HTTP domain "%s" is not allowed.', $httpDomain)
                : 'An empty HTTP domain is not allowed.',
            previous: $previous
        );
    }
}
