<?php

namespace App\Tests\Handler;

use App\Handler\GenerateImage;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class GenerateImageTest extends KernelTestCase
{
    public function testGenerationFromMyBlog(): void
    {
        $generateImage = new GenerateImage(
            new MockHttpClient(function (string $method, string $url, array $options) {
                if ($method === 'GET' && $url === 'https://hugo.alliau.me/apple-touch-icon.png') {
                    return new MockResponse(file_get_contents(__DIR__ . '/../fixtures/hugo.alliau.me-site-icon.png'));
                }
            }),
            self::getContainer()->getParameter('kernel.project_dir'),
        );

        $image = $generateImage(
            new \App\Dto\Image(
                filename: 'hugo-alliau-me-posts-2023-11-12-listen-to-doctrine-events-on-entities-given-a-php-attribute-html_1200_630.webp',
                metadata: new \App\Dto\ImageMetadata(
                    width: 1200,
                    height: 630,
                    backgroundColor: '#0a5293'
                )
            ),
            new \App\Dto\PageInfo(
                url: 'https://hugo.alliau.me/posts/2023-11-12-listen-to-doctrine-events-on-entities-given-a-php-attribute.html',
                title: 'Listen to Doctrine Events on Entities Using a PHP Attribute',
                description: 'Learn how to listen to Doctrine events thanks to a PHP attribute.',
                publishedAt: new \DateTimeImmutable('2023-11-12 00:00:00.0 +00:00'),
                siteIconUrl: 'https://hugo.alliau.me/apple-touch-icon.png',
                siteName: 'hugo.alliau.me',
            )
        );

        file_put_contents(__DIR__ . '/../../var/storage/tmp/test.webp', $image);
    }
}
