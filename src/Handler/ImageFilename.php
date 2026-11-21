<?php

namespace App\Handler;

use App\Dto\ImageMetadata;
use League\Uri\Uri;
use Symfony\Component\String\Slugger\SluggerInterface;

final readonly class ImageFilename
{
    public function __construct(
        private SluggerInterface $slugger,
    ) {
    }

    /**
     * The version, when given, is part of the name: a page whose card changed gets a new file and a new URL,
     * so no cache (ours, the CDN's or a social network's) has to be purged.
     */
    public function __invoke(string $pageUrl, ImageMetadata $metadata, ?string $version = null): string
    {
        $parts = [
            $this->slugger->slug(Uri::new($pageUrl)->withScheme(null)->toString())->toString(),
        ];
        if ($version !== null && $version !== '') {
            $parts[] = $this->slugger->slug($version)->toString();
        }
        $parts[] = (string) $metadata->width;
        $parts[] = (string) $metadata->height;

        return implode('_', $parts) . '.webp';
    }
}
