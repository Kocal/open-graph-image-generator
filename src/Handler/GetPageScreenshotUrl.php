<?php

namespace App\Handler;

use App\Panther;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Imagick\Imagine;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\DependencyInjection\Attribute\Target;

final readonly class GetPageScreenshotUrl
{
    public function __construct(
        #[Target('og_image.storage')]
        private FilesystemOperator $filesystemOperator,
        private Panther            $panther,
    ) {
    }

    public function __invoke(
        string $pageUrl,
        int    $width,
        int    $height,
    ): string {
        $imageFormat = 'webp';
        $imageName = sprintf('%s_%s_%s.%s', hash('xxh128', $pageUrl), $width, $height, $imageFormat);

        if (! $this->filesystemOperator->fileExists($imageName)) {
            $this->filesystemOperator->write($imageName, $this->doInvoke($pageUrl, $imageFormat, $width, $height));
        }

        return $this->filesystemOperator->publicUrl($imageName);
    }

    private function doInvoke(string $pageUrl, string $imageFormat, int $width, int $height): string
    {
        $client = $this->panther->getClient();
        //$client->manage()->window()->setSize(new WebDriverDimension($width, $height));
        $client->manage()->window()->maximize();
        $client->request('GET', $pageUrl);

        try {
            $imagine = new Imagine();

            $image = $imagine->load($client->takeScreenshot());
            $image->crop(new Point(0, 0), new Box($width, $height));

            return $image->get($imageFormat, [
                'quality' => 100,
            ]);
        } finally {
            $client->quit();
        }
    }
}
