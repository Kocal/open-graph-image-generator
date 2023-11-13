<?php

namespace App\Handler;

use App\Panther;
use Facebook\WebDriver\WebDriverDimension;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\DependencyInjection\Attribute\Target;

final readonly class GetPageScreenshotUrl
{
    public function __construct(
        #[Target('og_image.storage')]
        private FilesystemOperator $filesystemOperator,
        private Panther $panther,
    ) {
    }

    public function __invoke(
        string $pageUrl,
        string $width,
        string $height,
    ): string
    {
        $imageName = sprintf('%s_%s_%s.png', hash('xxh128', $pageUrl), $width, $height);

        if (!$this->filesystemOperator->fileExists($imageName)) {
            $this->filesystemOperator->write($imageName, $this->doInvoke($pageUrl, $width, $height));
        }

        return $this->filesystemOperator->publicUrl($imageName);
    }

    private function doInvoke(string $pageUrl, int $width, int $height): string
    {
        $client = $this->panther->getClient();
        $client->manage()->window()->setSize(new WebDriverDimension($width, $height));
        $client->request('GET', $pageUrl);

        try {
            return $client->takeScreenshot();
        } finally {
            $client->quit();
        }
    }
}