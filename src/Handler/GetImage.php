<?php

namespace App\Handler;

use App\Dto\Image;
use App\Dto\ImageMetadata;
use App\Dto\PageInfo;
use League\Flysystem\FilesystemOperator;
use League\Uri\Uri;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\String\Slugger\SluggerInterface;

final readonly class GetImage
{
    public function __construct(
        #[Target('storage.og_image')]
        private FilesystemOperator $imagesStorage,
        private GenerateImage $generateImage,
        private SluggerInterface $slugger,
    ) {
    }

    public function __invoke(ImageMetadata $imageMetadata, PageInfo $pageInfo): Image
    {
        $image = new Image(
            filename: sprintf(
                '%s_%s_%s.webp',
                $this->slugger->slug(Uri::new($pageInfo->url)->withScheme(null)->toString()),
                $imageMetadata->width,
                $imageMetadata->height
            ),
            metadata: $imageMetadata,
        );

        //if (!$this->imagesStorage->fileExists($image->filename)) {
        $this->imagesStorage->write($image->filename, ($this->generateImage)($image, $pageInfo));
        //}

        return $image;
    }
}
