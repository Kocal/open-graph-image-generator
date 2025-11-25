<?php

declare(strict_types=1);

namespace App\Exception;

use Throwable;

final class PageNotFoundException extends \InvalidArgumentException
{
    public function __construct(string $pageUrl, ?Throwable $previous = null)
    {
        parent::__construct(
            message: sprintf('The page "%s" was not found.', $pageUrl),
            previous: $previous
        );
    }
}
