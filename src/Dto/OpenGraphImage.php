<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class OpenGraphImage
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Url]
        public string $url,
        #[Assert\Positive]
        #[Assert\LessThanOrEqual(1200)]
        public int $width = 1200,
        #[Assert\Positive]
        #[Assert\LessThanOrEqual(630)]
        public int $height = 630,
        #[Assert\NotBlank]
        #[Assert\CssColor]
        public string $backgroundColor = '#0a5293',
    ) {
    }

    /**
     * @return array{
     *     url: string,
     *     width: int,
     *     height: int,
     *     backgroundColor: string,
     * }
     */
    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'width' => $this->width,
            'height' => $this->height,
            'backgroundColor' => $this->backgroundColor,
        ];
    }
}
