<?php

namespace App\Controller;

use App\Form\Request\GenerateOpenGraphImageRequest;
use App\Form\Type\GenerateOpenGraphImageType;
use App\Handler\GetPageInfo;
use App\Handler\GetPageScreenshotUrl;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GenerateController extends AbstractController
{
    #[Route('/generate', name: 'app_generate')]
    public function __invoke(
        Request $request,
        GetPageInfo $getPageInfo,
        GetPageScreenshotUrl $getPageScreenshotUrl,
        #[Autowire(param: 'kernel.debug')]
        bool $debug,
        #[Autowire(param: 'app.open_graph_image.width')]
        int $openGraphImageWidth,
        #[Autowire(param: 'app.open_graph_image.height')]
        int $openGraphImageHeight,
    ): Response
    {
        $form = $this->createForm(GenerateOpenGraphImageType::class, $data = new GenerateOpenGraphImageRequest());
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            return new Response('Invalid form', Response::HTTP_BAD_REQUEST);
        }

        try {
            $pageInfo = ($getPageInfo)($data->url);
        } catch (\Throwable $e) {
            if ($debug) {
                throw $e;
            }

            return new Response($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        if ($data->format === 'html') {
            return $this->render('generate/image.html.twig', [
                'page_info' => $pageInfo,
                'width' => $openGraphImageWidth,
                'height' => $openGraphImageHeight,
            ]);
        }

        $pageScreenshotUrl = ($getPageScreenshotUrl)(
            $this->generateUrl(
                $request->attributes->get('_route'),
                ['format' => 'html'] + $request->query->all(),
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            $openGraphImageWidth,
            $openGraphImageHeight
        );

        return (new RedirectResponse($pageScreenshotUrl))
            ->setPublic()
            ->setMaxAge(3600);
    }
}
