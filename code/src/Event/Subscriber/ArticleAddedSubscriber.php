<?php

declare(strict_types=1);

namespace App\Event\Subscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Article;
use App\Server\Mercure\Publisher\PublisherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;

class ArticleAddedSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly PublisherInterface $publisher)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.view' => ['newArticleAdded', EventPriorities::POST_WRITE],
        ];
    }

    public function newArticleAdded(ViewEvent $event): void
    {
        if (!($article = $event->getControllerResult()) instanceof Article) {
            return;
        }

        if (!$event->getRequest()->isMethod(Request::METHOD_POST)) {
            return;
        }

        $this->publisher->publish(
            'http://localhost/books/1',
            \json_encode([
                'article_id' => $article->getId(),
                'title' => $article->getTitle(),
            ]),
        );
    }
}