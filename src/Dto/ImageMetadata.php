<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ImageMetadata
{
    public function __construct(
        #[Assert\Positive]
        #[Assert\LessThanOrEqual(1200)]
        public int    $width = 1200,
        #[Assert\Positive]
        #[Assert\LessThanOrEqual(630)]
        public int    $height = 630,
        #[Assert\NotBlank]
        #[Assert\CssColor]
        public string $backgroundColor = '#0a5293',
    ) {
    }
}
