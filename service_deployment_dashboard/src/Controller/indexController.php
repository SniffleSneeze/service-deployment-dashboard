<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class indexController extends AbstractController
{
    #[Route('/index', name: 'index')]
    public function __invoke(): Response
    {
        return $this->render('index/index.html.twig');
    }
}
