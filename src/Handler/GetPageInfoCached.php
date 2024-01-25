<?php

namespace App\Handler;

use App\Dto\PageInfo;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Contracts\Cache\CacheInterface;

#[AsDecorator(decorates: GetPageInfo::class)]
final readonly class GetPageInfoCached implements GetPageInfoInterface
{
    public function __construct(
        #[AutowireDecorated]
        private GetPageInfoInterface $inner,
        private CacheInterface $cache,
    ) {
    }

    public function __invoke(string $pageUrl): PageInfo
    {
        return $this->cache->get(
            key: hash('xxh128', $pageUrl),
            callback: function (CacheItemInterface $cacheItem) use ($pageUrl) {
                $cacheItem->expiresAfter(60 * 60 * 24 * 7);

                return ($this->inner)($pageUrl);
            },
        );
    }
}
