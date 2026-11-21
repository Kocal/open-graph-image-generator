<?php

namespace App\Tests\Handler;

use App\Dto\ImageMetadata;
use App\Handler\ImageFilename;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\Slugger\AsciiSlugger;

class ImageFilenameTest extends TestCase
{
    public function testWithoutVersionTheNameIsUnchanged(): void
    {
        $imageFilename = new ImageFilename(new AsciiSlugger());

        $filename = $imageFilename('https://hugo.alliau.me/blog/posts/hello', new ImageMetadata(1200, 630));

        self::assertSame('hugo-alliau-me-blog-posts-hello_1200_630.webp', $filename);
    }

    public function testTheVersionIsPartOfTheName(): void
    {
        $imageFilename = new ImageFilename(new AsciiSlugger());

        $filename = $imageFilename('https://hugo.alliau.me/blog/posts/hello', new ImageMetadata(1200, 630), 'a1b2c3d4');

        self::assertSame('hugo-alliau-me-blog-posts-hello_a1b2c3d4_1200_630.webp', $filename);
    }

    public function testTheVersionIsSluggedToo(): void
    {
        $imageFilename = new ImageFilename(new AsciiSlugger());

        $filename = $imageFilename('https://hugo.alliau.me/blog/posts/hello', new ImageMetadata(600, 315), '../v 2');

        self::assertSame('hugo-alliau-me-blog-posts-hello_v-2_600_315.webp', $filename);
    }
}
