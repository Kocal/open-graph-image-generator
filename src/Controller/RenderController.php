<?php

namespace App\Controller;

use App\Dto\OpenGraphImage;
use App\Handler\GetPageInfo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Routing\Attribute\Route;

final class RenderController extends AbstractController
{
    #[Route('/render', name: 'app_render')]
    public function __invoke(
        #[MapQueryString]
        OpenGraphImage $openGraphImage,
        GetPageInfo $getPageInfo,
        #[Autowire(service: 'profiler')]
        ?Profiler $profiler,
        #[MapQueryParameter]
        bool $enableProfiler = true,
    ): Response {
        if ($profiler && ! $enableProfiler) {
            $profiler->disable();
        }

        $pageInfo = ($getPageInfo)($openGraphImage->url);

        return $this->render('render/image.html.twig', [
            'page_info' => $pageInfo,
            'width' => $openGraphImage->width,
            'height' => $openGraphImage->height,
            'background_color' => $openGraphImage->backgroundColor,
        ]);
    }
}
