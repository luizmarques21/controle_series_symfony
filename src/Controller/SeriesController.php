<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SeriesController extends AbstractController
{
    #[Route('/series', name: 'app_series', methods: ['GET'])]
    public function index(): Response
    {
        $seriesList = [
            'Lost',
            'Greys Anatomy',
            'Loki',
            'Suits'
        ];

        $html = '<ul>';
        foreach ($seriesList as $series) {
            $html .= '<li>' . $series . '</li>';
        }
        $html .= '</ul>';

        return new JsonResponse($seriesList);

        return new Response($html);

        return $this->render('series/index.html.twig', [
            'controller_name' => 'SeriesController',
        ]);
    }
}
