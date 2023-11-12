<?php

namespace App\Controller;

use App\Form\Type\OpenGraphImageFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GenerateController extends AbstractController
{
    #[Route('/generate', name: 'app_generate')]
    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(OpenGraphImageFormType::class);
        $form->handleRequest($request);

        return $this->render('generate/image.html.twig', [
            'data' => $form->getData()->toArray(),
        ]);
    }
}
