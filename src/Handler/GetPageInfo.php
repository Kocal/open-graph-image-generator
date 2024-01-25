<?php

namespace App\Handler;

use App\Dto\PageInfo;
use League\Uri\Uri;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class GetPageInfo implements GetPageInfoInterface
{
    /**
     * @param list<string> $allowedDomains
     */
    public function __construct(
        #[Autowire(env: 'csv:APP_OPENGRAPH_IMAGE_GENERATION_ALLOWED_DOMAINS')]
        private array $allowedDomains,
        private HttpClientInterface $httpClient,
    ) {
    }

    public function __invoke(string $pageUrl): PageInfo
    {
        $pageUrl = Uri::new($pageUrl);
        if (! in_array($pageUrl->getHost(), $this->allowedDomains)) {
            throw new \InvalidArgumentException('Invalid domain.');
        }

        $request = $this->httpClient->request('GET', $pageUrl->toString());

        $crawler = new Crawler($request->getContent());

        $datePublished = null;
        foreach ($crawler->filter('script[type="application/ld+json"]') as $node) {
            $jsonLd = json_decode($node->textContent, true, flags: JSON_THROW_ON_ERROR);
            if (is_array($jsonLd) && is_string($jsonLd['datePublished'] ?? null)) {
                $datePublished = new \DateTimeImmutable($jsonLd['datePublished']);
                break;
            }
        }

        return new PageInfo(
            url: $pageUrl->toString(),
            title: $crawler->filter('meta[property="og:title"]')->attr('content'),
            description: $crawler->filter('meta[property="og:description"]')->attr('content'),
            publishedAt: $datePublished,
            siteIconUrl: Uri::fromBaseUri(
                uri: $crawler->filter('link[rel="apple-touch-icon"]')->attr('href'),
                baseUri: $pageUrl,
            ),
            siteName: Uri::new($pageUrl)->getHost(),
        );
    }
}
