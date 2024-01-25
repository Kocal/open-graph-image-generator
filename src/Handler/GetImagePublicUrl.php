<?php

namespace App\Handler;

use App\Dto\Image;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\DependencyInjection\Attribute\Target;

final readonly class GetImagePublicUrl
{
    public function __construct(
        #[Target('storage.og_image')]
        private FilesystemOperator $filesystemOperator
    ) {
    }

    public function __invoke(Image $image): string
    {
        return $this->filesystemOperator->publicUrl($image->filename);
    }
}
