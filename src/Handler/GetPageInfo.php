<?php

namespace App\Handler;

use App\Dto\PageInfo;
use App\Panther;
use Facebook\WebDriver\WebDriverBy;
use League\Uri\Uri;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class GetPageInfo
{
    /**
     * @param list<string> $allowedDomains
     */
    public function __construct(
        #[Autowire(env: 'csv:APP_OPENGRAPH_IMAGE_GENERATION_ALLOWED_DOMAINS')]
        private array $allowedDomains,
        private CacheInterface $cache,
        private Panther $panther,
    ) {
    }

    public function __invoke(string $pageUrl): PageInfo
    {
        $domain = Uri::new($pageUrl)->getHost();
        if (! in_array($domain, $this->allowedDomains)) {
            throw new \InvalidArgumentException('Invalid domain.');
        }

        $pageInfo = $this->cache->get(
            key: hash('xxh128', $pageUrl),
            callback: function (CacheItemInterface $cacheItem) use ($pageUrl) {
                $cacheItem->expiresAfter(60 * 60 * 24 * 7);
                return $this->doInvoke($pageUrl);
            },
        );

        return new PageInfo(
            url: $pageUrl,
            title: $pageInfo['title'],
            description: $pageInfo['description'],
            publishedAt: $pageInfo['datePublished'],
            siteIconUrl: $pageInfo['siteIconUrl'],
            siteName: $pageInfo['siteName'],
        );
    }

    /**
     * @return array{url: string, title: string, description: string, siteIconUrl: string, siteName: string, datePublished: \DateTimeImmutable|null}
     */
    private function doInvoke(string $pageUrl): array
    {
        $client = $this->panther->getClient();
        $client->request('GET', $pageUrl);

        $title = $client
            ->findElement(WebDriverBy::cssSelector('meta[property="og:title"]'))
            ->getAttribute('content')
        ?? throw new \RuntimeException('Title not found.');

        $description = $client
            ->findElement(WebDriverBy::cssSelector('meta[property="og:description"]'))
            ->getAttribute('content')
        ?? throw new \RuntimeException('Description not found.');

        $siteIconUrl = Uri::fromBaseUri(
            uri: $client
                ->findElement(WebDriverBy::cssSelector('link[rel="apple-touch-icon"]'))
                ->getAttribute('href')
            ?? throw new \RuntimeException('Site icon not found.'),
            baseUri: $pageUrl,
        );

        $datePublished = null;
        foreach ($client->findElements(WebDriverBy::cssSelector('script[type="application/ld+json"]')) as $element) {
            $jsonLd = json_decode($element->getDomProperty('textContent'), true, flags: JSON_THROW_ON_ERROR);
            if (is_array($jsonLd) && is_string($jsonLd['datePublished'] ?? null)) {
                $datePublished = new \DateTimeImmutable($jsonLd['datePublished']);
                break;
            }
        }

        try {
            return [
                'url' => $pageUrl,
                'title' => $title,
                'description' => $description,
                'siteIconUrl' => $siteIconUrl->toString(),
                'siteName' => Uri::new($pageUrl)->getHost() ?? throw new \RuntimeException('Site name not found.'),
                'datePublished' => $datePublished,
            ];
        } finally {
            $client->quit();
        }
    }
}
