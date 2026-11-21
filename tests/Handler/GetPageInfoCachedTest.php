<?php

namespace App\Tests\Handler;

use App\Dto\PageInfo;
use App\Handler\GetPageInfoCached;
use App\Handler\GetPageInfoInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class GetPageInfoCachedTest extends TestCase
{
    public function testTheSameVersionIsFetchedOnce(): void
    {
        $inner = $this->countingPageInfo();
        $cached = new GetPageInfoCached($inner, new ArrayAdapter());

        $cached('https://hugo.alliau.me/blog/posts/hello', 'v1');
        $cached('https://hugo.alliau.me/blog/posts/hello', 'v1');

        self::assertSame(1, $inner->calls);
    }

    public function testANewVersionFetchesThePageAgain(): void
    {
        $inner = $this->countingPageInfo();
        $cached = new GetPageInfoCached($inner, new ArrayAdapter());

        $cached('https://hugo.alliau.me/blog/posts/hello', 'v1');
        $cached('https://hugo.alliau.me/blog/posts/hello', 'v2');
        $cached('https://hugo.alliau.me/blog/posts/hello');

        self::assertSame(3, $inner->calls);
    }

    private function countingPageInfo(): GetPageInfoInterface
    {
        return new class() implements GetPageInfoInterface {
            public int $calls = 0;

            public function __invoke(string $pageUrl, ?string $version = null): PageInfo
            {
                ++$this->calls;

                return new PageInfo($pageUrl, 'Title', 'Description', null, 'https://hugo.alliau.me/icon.png', 'hugo.alliau.me');
            }
        };
    }
}
