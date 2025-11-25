<?php

namespace App\Controller;

use App\Dto\ImageMetadata;
use App\Exception\PageNotFoundException;
use App\Exception\UnallowedHttpDomainException;
use App\Handler\GetImage;
use App\Handler\GetImagePublicUrl;
use App\Handler\GetPageInfoInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class ImageController extends AbstractController
{
    #[Route(
        '/image.{_format}',
        name: 'app_image',
        defaults: [
            '_format' => 'image',
        ],
        format: 'image'
    )]
    public function __invoke(
        #[MapQueryParameter(name: 'url')]
        string $pageUrl,
        #[MapQueryString]
        ImageMetadata $imageMetadata,
        GetPageInfoInterface $getPageInfo,
        GetImage $getImage,
        GetImagePublicUrl $getImagePublicUrl,
    ): Response {
        try {
            $pageInfo = $getPageInfo($pageUrl);
        } catch (UnallowedHttpDomainException $e) {
            throw new BadRequestException($e->getMessage());
        } catch (PageNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        $image = $getImage($imageMetadata, $pageInfo);

        return $this->redirect($getImagePublicUrl($image), 301)
            ->setPublic()
            ->setMaxAge(3600);
    }
}
