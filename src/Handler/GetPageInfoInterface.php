<?php

namespace App\Handler;

use App\Dto\PageInfo;

interface GetPageInfoInterface
{
    public function __invoke(string $pageUrl): PageInfo;
}
