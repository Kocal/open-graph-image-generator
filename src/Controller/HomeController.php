<?php

namespace App\Controller;

use App\Form\Request\PlaygroundRequest;
use App\Form\Type\PlaygroundType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function __invoke(
        Request $request,
    ): Response {
        $form = $this->createForm(PlaygroundType::class, $data = new PlaygroundRequest());
        $form->handleRequest($request);

        return $this->render('home/index.html.twig', [
            'form' => $form,
            'image_url' => $this->generateUrl(
                'app_image',
                $data->toArray(),
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ]);
    }
}
