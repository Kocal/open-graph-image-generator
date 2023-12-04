<?php

namespace App\Controller;

use App\Dto\OpenGraphImage;
use App\Handler\GetPageScreenshotUrl;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ImageController extends AbstractController
{
    #[Route(
        '/image.{_format}',
        name: 'app_image',
        defaults: [
            '_format' => 'html|image',
        ],
        format: 'image'
    )]
    public function __invoke(
        #[MapQueryString]
        OpenGraphImage $openGraphImage,
        GetPageScreenshotUrl             $getPageScreenshotUrl,
        Request $request,
    ): Response {
        $isHtml = $request->getRequestFormat() === 'html';

        $renderingUrl = $this->generateUrl(
            'app_render',
            [
                'enableProfiler' => $isHtml,
            ] + $openGraphImage->toArray(),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        if ($isHtml) {
            return $this->redirect($renderingUrl);
        }

        $pageScreenshotUrl = ($getPageScreenshotUrl)(
            $renderingUrl,
            $openGraphImage->width,
            $openGraphImage->height,
        );

        return $this->redirect($pageScreenshotUrl, 301)
            ->setPublic()
            ->setMaxAge(3600);
    }
}
