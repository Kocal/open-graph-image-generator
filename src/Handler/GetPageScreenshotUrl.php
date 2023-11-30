<?php

namespace App\Handler;

use App\Panther;
use Facebook\WebDriver\WebDriverDimension;
use Imagine\Image\Box;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point;
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
        string $backgroundColor,
    ): string {
        $imageFormat = 'webp';
        $imageName = sprintf('%s_%s_%s.%s', hash('xxh128', $pageUrl), $width, $height, $imageFormat);

        if (! $this->filesystemOperator->fileExists($imageName)) {
            $this->filesystemOperator->write($imageName, $this->doInvoke($pageUrl, $imageFormat, $width, $height, $backgroundColor));
        }

        return $this->filesystemOperator->publicUrl($imageName);
    }

    private function doInvoke(string $pageUrl, string $imageFormat, int $width, int $height, string $backgroundColor): string
    {
        // FIXME: Firefox  does not respect the window dimensions :shrug:
        $client = $this->panther->getClient(arguments: ['--width=' . $width, '--height=' . $height]);
        $client->manage()->window()->setSize(new WebDriverDimension($width, $height));
        $client->request('GET', $pageUrl);

        try {
            $imagine = new Imagine();

            // FIXME: Since Firefox does not respect the window dimensions,
            // we need to create an image with the desired dimensions and background color
            $image = $imagine->create(
                new Box($width, $height),
                (new RGB())->color($backgroundColor)
            );

            // FIXME: Since Firefox does not respect the window dimensions,
            // we need to paste the screenshot in the center of the image
            $image->paste(
                $screenshot = $imagine->load($client->takeScreenshot()),
                new Point(
                    ($image->getSize()->getWidth() - $screenshot->getSize()->getWidth()) / 2,
                    ($image->getSize()->getHeight() - $screenshot->getSize()->getHeight()) / 2
                ),
            );

            return $image->get($imageFormat);
        } finally {
            $client->quit();
        }
    }
}
