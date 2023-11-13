<?php

namespace App\Handler;

use App\Dto\PageInfo;
use Facebook\WebDriver\WebDriverBy;
use League\Uri\Uri;
use League\Url\Url;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Panther\Client;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class GetPageInfo
{
    private HttpClientInterface $httpClient;

    public function __construct(
        #[Autowire(env: 'csv:APP_OPENGRAPH_IMAGE_GENERATION_ALLOWED_DOMAINS')]
        private array $allowedDomains,
        private CacheInterface $cache,
    ) {
    }

    public function __invoke(string $pageUrl): PageInfo
    {
        $domain = Uri::new($pageUrl)->getHost();
        if (!in_array($domain, $this->allowedDomains)) {
            throw new \InvalidArgumentException('Invalid domain.');
        }

        $pageInfo = $this->cache->get(
            key: hash('xxh128', $pageUrl),
            callback: function(CacheItemInterface $cacheItem) use ($pageUrl) {
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
        $client = Client::createFirefoxClient();
        $client->request('GET', $pageUrl);

        $title = $client
            ->findElement(WebDriverBy::cssSelector('meta[property="og:title"]'))
            ->getAttribute('content');

        $description = $client
            ->findElement(WebDriverBy::cssSelector('meta[property="og:description"]'))
            ->getAttribute('content');

        $siteIconUrl = Uri::fromBaseUri(
            uri: $client
                ->findElement(WebDriverBy::cssSelector('link[rel="apple-touch-icon"]'))
                ->getAttribute('href'),
            baseUri: $pageUrl,
        );

        $datePublished = null;
        foreach ($client->findElements(WebDriverBy::cssSelector('script[type="application/ld+json"]')) as $element) {
            $jsonLd = json_decode($element->getDomProperty('textContent'), true, flags: JSON_THROW_ON_ERROR);
            if (null !== ($jsonLd['datePublished'] ?? null)) {
                $datePublished = new \DateTimeImmutable($jsonLd['datePublished']);
                break;
            }
        }

        return [
            'url' => $pageUrl,
            'title' => $title,
            'description' => $description,
            'siteIconUrl' => $siteIconUrl->toString(),
            'siteName' => Uri::new($pageUrl)->getHost(),
            'datePublished' => $datePublished,
        ];
    }
}