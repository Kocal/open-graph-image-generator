<?php

namespace App\Handler;

use App\Dto\Image;
use App\Dto\PageInfo;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\FontInterface;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Typography\Font;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class GenerateImage
{
    private const int IMAGE_PADDING = 56;

    private const int SITE_ICON_SIZE = 92;

    public function __construct(
        private HttpClientInterface $httpClient,
        #[Autowire(param: 'kernel.project_dir')]
        private string              $projectDir,
    ) {
    }

    public function __invoke(Image $image, PageInfo $pageInfo): string
    {
        $imageManager = new ImageManager(new Driver());

        $interventionImage = $imageManager->create($image->metadata->width, $image->metadata->height);
        $interventionImage->fill($image->metadata->backgroundColor);

        $this->drawTitles($imageManager, $interventionImage, $pageInfo);
        $this->drawSiteIcon($imageManager, $interventionImage, $pageInfo);
        $this->drawSiteName($imageManager, $interventionImage, $image, $pageInfo);
        $this->drawDate($imageManager, $interventionImage, $pageInfo);

        return $interventionImage->encodeByPath($image->filename)->toString();
    }

    private function drawTitles(ImageManager $imageManager, ImageInterface $interventionImage, PageInfo $pageInfo): void
    {
        $fontTitle = (new Font($this->projectDir . '/assets/fonts/Inter/static/Inter-Bold.ttf'))
            ->setSize(56)
            ->setColor('rgba(255, 255, 255, 0.90)')
            ->setLineHeight(1.2)
            ->setValignment('top');

        $fontSubTitle = (new Font($this->projectDir . '/assets/fonts/Inter/static/Inter-Regular.ttf'))
            ->setSize(46)
            ->setColor('rgba(255, 255, 255, 0.85)')
            ->setLineHeight(1.2)
            ->setValignment('top');

        $titleHeight = 0;
        foreach (self::wrapText($pageInfo->title, $interventionImage->width() - (self::IMAGE_PADDING * 2), $fontTitle) as $i => $line) {
            $interventionImage
                ->text(
                    $line,
                    self::IMAGE_PADDING,
                    $titleHeight = (self::IMAGE_PADDING + $i * $fontTitle->size() * $fontTitle->lineHeight()),
                    $fontTitle,
                );
        }
        $titleHeight += ($fontTitle->size() + $fontTitle->lineHeight());

        foreach (self::wrapText($pageInfo->description, $interventionImage->width() - (self::IMAGE_PADDING * 2), $fontSubTitle) as $i => $line) {
            $interventionImage
                ->text(
                    $line,
                    self::IMAGE_PADDING,
                    $titleHeight + 32 + ($i * $fontSubTitle->size() * $fontSubTitle->lineHeight()),
                    $fontSubTitle,
                );
        }
    }

    private function drawSiteIcon(ImageManager $imageManager, ImageInterface $image, PageInfo $pageInfo): void
    {
        $response = $this->httpClient->request('GET', $pageInfo->siteIconUrl);

        $siteIconImage = $imageManager
            ->read($response->getContent())
            ->scaleDown(self::SITE_ICON_SIZE, self::SITE_ICON_SIZE);

        $siteIconImage
            ->core()
            ->native()
            ->roundCornersImage($siteIconImage->width(), $siteIconImage->height());

        $image->place(
            $siteIconImage,
            'bottom-left',
            self::IMAGE_PADDING,
            self::IMAGE_PADDING
        );
    }

    private function drawSiteName(ImageManager $imageManager, ImageInterface $interventionImage, Image $image, PageInfo $pageInfo): void
    {
        $interventionImage
            ->text(
                $pageInfo->siteName,
                self::IMAGE_PADDING + self::SITE_ICON_SIZE + 16,
                $interventionImage->height() - self::IMAGE_PADDING - (self::SITE_ICON_SIZE / 2),
                (new Font($this->projectDir . '/assets/fonts/Inter/static/Inter-Regular.ttf'))
                    ->setSize(32)
                    ->setColor('rgba(255, 255, 255, 0.85)')
                    ->setValignment('middle')
            );
    }

    private function drawDate(ImageManager $imageManager, ImageInterface $interventionImage, PageInfo $pageInfo): void
    {
        $interventionImage
            ->text(
                $pageInfo->publishedAt->format('M d, Y'),
                $interventionImage->width() - self::IMAGE_PADDING,
                $interventionImage->height() - self::IMAGE_PADDING - (self::SITE_ICON_SIZE / 2),
                (new Font($this->projectDir . '/assets/fonts/Inter/static/Inter-Bold.ttf'))
                    ->setSize(48)
                    ->setColor('rgba(255, 255, 255, 0.85)')
                    ->setValignment('middle')
                    ->setAlignment('right')
            );
    }

    /**
     * Wrap text to fit within a given width and font.
     *
     * @see https://github.com/Intervention/image/issues/143#issuecomment-492592752
     * @see https://github.com/Intervention/image/issues/143#issuecomment-1907689756
     *
     * @return list<string>
     */
    private static function wrapText(string $text, int $width, FontInterface $font): array
    {
        $imagick = new \Imagick();
        $imagickDraw = new \ImagickDraw();
        $imagickDraw->setFont($font->filename());
        $imagickDraw->setFontSize($font->size());

        $line = [];
        $lines = [];

        foreach (explode(' ', $text) as $word) {
            $line[] = $word;

            $fontMetrics = $imagick->queryFontMetrics($imagickDraw, implode(' ', $line));

            // If our line doesn't fit, remove the last word and place it on a new line
            if ($fontMetrics['textWidth'] >= $width) {
                array_pop($line);
                $lines[] = implode(' ', $line);
                $line = [$word];
            }
        }

        $lines[] = implode(' ', $line);

        return $lines;
    }
}
