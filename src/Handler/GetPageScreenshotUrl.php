<?php

namespace App\Handler;

use App\Panther;
use Facebook\WebDriver\WebDriverDimension;
use Imagine\Image\Box;
use Imagine\Imagick\Imagine;
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
        $imageFormat = 'webp';
        $imageName = sprintf('%s_%s_%s.%s', hash('xxh128', $pageUrl), $width, $height, $imageFormat);

        if (!$this->filesystemOperator->fileExists($imageName)) {
            $this->filesystemOperator->write($imageName, $this->doInvoke($pageUrl, $imageFormat, $width, $height));
        }

        return $this->filesystemOperator->publicUrl($imageName);
    }

    private function doInvoke(string $pageUrl, string $imageFormat, int $width, int $height): string
    {
        $screenshot = $this->takeScreenshot($pageUrl, $width, $height);
        $screenshot = $this->optimizeScreenshot($screenshot,  $imageFormat,$width, $height);

        return $screenshot;
    }

    private function takeScreenshot(string $pageUrl, int $width, int $height): string
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

    private function optimizeScreenshot(string $imageContent, string $imageFormat, int $width, int $height): string
    {
        $imagine = new Imagine();
        $image = $imagine->load($imageContent);
        $image->resize(new Box($width, $height));

        return $image->get($imageFormat);
    }
}