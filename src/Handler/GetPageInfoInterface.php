<?php

namespace App\Handler;

use App\Dto\PageInfo;
use App\Exception\PageNotFoundException;
use App\Exception\UnallowedHttpDomainException;

interface GetPageInfoInterface
{
    /**
     * @throws PageNotFoundException
     * @throws UnallowedHttpDomainException
     */
    public function __invoke(string $pageUrl): PageInfo;
}
