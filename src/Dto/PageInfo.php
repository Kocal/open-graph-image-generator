<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PageInfo
{
    public function __construct(
        #[Assert\Url]
        public string $url,
        #[Assert\NotBlank]
        public string $title,
        #[Assert\NotBlank]
        public string $description,
        #[Assert\Date]
        public \DateTimeInterface|null $publishedAt,
        #[Assert\Url]
        public string $siteIconUrl,
        #[Assert\NotBlank]
        public string $siteName,
    ) {
    }
}
