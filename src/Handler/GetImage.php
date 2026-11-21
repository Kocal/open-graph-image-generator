<?php

namespace App\Handler;

use App\Dto\Image;
use App\Dto\ImageMetadata;
use App\Dto\PageInfo;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\DependencyInjection\Attribute\Target;

final readonly class GetImage
{
    public function __construct(
        #[Target('storage.og_image')]
        private FilesystemOperator $imagesStorage,
        private GenerateImage $generateImage,
        private ImageFilename $imageFilename,
    ) {
    }

    public function __invoke(ImageMetadata $imageMetadata, PageInfo $pageInfo, ?string $version = null): Image
    {
        $image = new Image(
            filename: ($this->imageFilename)($pageInfo->url, $imageMetadata, $version),
            metadata: $imageMetadata,
        );

        if (! $this->imagesStorage->fileExists($image->filename)) {
            $this->imagesStorage->write($image->filename, ($this->generateImage)($image, $pageInfo));
        }

        return $image;
    }
}
