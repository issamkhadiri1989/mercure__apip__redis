<?php

namespace App\Controller;

use App\Server\Mercure\Publisher\PublisherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MercureController extends AbstractController
{
    #[Route('/send-request', name: 'app_send_mercure_notification')]
    public function index(PublisherInterface $publisher): Response
    {
        $publisher->publish('http://localhost/books/1', \json_encode(['status' => 'OutOfStock']));

        return new Response();
    }

    #[Route('/', name: 'app_mercure_receiver')]
    public function subscriber(): Response
    {
        return $this->render('mercure/index.html.twig');
    }
}
