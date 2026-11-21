<?php

namespace App\Handler;

use App\Dto\PageInfo;
use App\Exception\PageNotFoundException;
use App\Exception\UnallowedHttpDomainException;

interface GetPageInfoInterface
{
    /**
     * @param string|null $version an opaque value chosen by the page; a new value means the page's card changed
     *
     * @throws PageNotFoundException
     * @throws UnallowedHttpDomainException
     */
    public function __invoke(string $pageUrl, ?string $version = null): PageInfo;
}
