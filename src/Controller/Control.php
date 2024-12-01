<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Control extends AbstractController
{
    #[Route('/ma-page', name: 'ma_page')]
    public function index(): Response
    {
        return $this->render('test.html.twig');
    }
}
