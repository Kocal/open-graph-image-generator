<?php

namespace App\Controller;

use App\Form\Request\GenerateOpenGraphImageRequest;
use App\Form\Type\GenerateOpenGraphImageType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(GenerateOpenGraphImageType::class, $data = new GenerateOpenGraphImageRequest());

        $form->handleRequest($request);

        return $this->render('home/index.html.twig', [
            'form' => $form->createView(),
            'open_graph_image_request' => $data->toArray(),
        ]);
    }
}
