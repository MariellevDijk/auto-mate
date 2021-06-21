<?php

namespace App\Controller;

use App\Service\ScraperService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class KentekenController extends AbstractController
{
    #[Route('/kenteken/{licensePlate}', name: 'kenteken')]
    public function __invoke(string $licensePlate, ScraperService $scraperService): Response
    {
        $response = $scraperService->getLicensePlateDetails($licensePlate);
        return new Response($response, Response::HTTP_OK, ['Content-type' => 'text/plain']);
    }
}
