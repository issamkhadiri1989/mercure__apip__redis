<?php

declare(strict_types=1);

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Article;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ArticleStateProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private readonly ProviderInterface $provider,
        #[Autowire(service: 'redis.cache')]
        private CacheItemPoolInterface $cache,
    ) {

    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $item = $this->cache->getItem('__article__' . $uriVariables['id']);

        if ($item->isHit()) {
            return $item->get();
        }

        $article = $this->provider->provide($operation, $uriVariables, $context);

        $this->doSaveToCache($item, $article);

        return $article;
    }

    private function doSaveToCache(CacheItemInterface $item, Article $article): void
    {
        $item->set($article);

        $this->cache->save($item);
    }
}