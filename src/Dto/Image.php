<?php

namespace App\Dto;

final readonly class Image
{
    public function __construct(
        public string $filename,
        public ImageMetadata $metadata,
    ) {
    }
}
