# Mercure

in the `compose.yaml` file we have the following configuration

```
    mercure:
      image: dunglas/mercure
      restart: unless-stopped
      environment:
        SERVER_NAME: ':80'
        MERCURE_PUBLISHER_JWT_KEY: '!ChangeThisMercureHubJWTSecretKey!'
        MERCURE_SUBSCRIBER_JWT_KEY: '!ChangeThisMercureHubJWTSecretKey!'
        MERCURE_EXTRA_DIRECTIVES: 'cors_origins http://localhost'
      networks:
        - symfony
      command: /usr/bin/caddy run --config /etc/caddy/Caddyfile
      ports:
        - "9999:80"
      volumes:
        - caddy_data:/data
        - caddy_config:/config
        - ./infra/caddy/:/etc/caddy/

```

in the `code/config/packages/mercure.yaml` file we are configuring the Mercure Bundle:

```
mercure:
    hubs:
        default:
            url: '%env(MERCURE_URL)%'
            public_url: '%env(MERCURE_PUBLIC_URL)%'
            jwt:
                secret: '%env(MERCURE_JWT_SECRET)%'
                publish: [ '*' ]
                subscribe: [ '*' ]

```

for testing purposes, in the `.env` file we have

```
MERCURE_URL=http://mercure/.well-known/mercure
MERCURE_PUBLIC_URL=http://localhost:9999/.well-known/mercure
MERCURE_JWT_SECRET="!ChangeThisMercureHubJWTSecretKey!"
```

to test this configuration :

in the **code/src/Controller/MercureController.php** we have 2 routes:

```
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
```

the `http://localhost/send-request` is used here to publish the event (suppose we got there some logic to send the
notification)

the page `http://localhost/` the page where the user will receive the notification in real time

the JS code is written in the `code/templates/mercure/index.html.twig`.

the endpoint `POST http://localhost/api/articles` is used to create new article.

when the article is added to the database, a new notification is sent to `http://localhost`. the home page show the
latest article added in real time.

# Redis

to test Redis, you need to check the endpoint `GET http://localhost/api/articles/{id}`. the idea is that at the first
attempt, ApiPlatform gets the article from the database using the ORM state provider. next time we try to get the
article, it will be retrieved from the Redis cache.

check out the custom provider `src/State/Provider/ArticleStateProvider.php` which hooks the built-in ORM state provider.

redis configuration is 

```yaml 
# config/packages/cache.yaml
framework:
    cache:
        pools:
            redis.cache:
                adapter: cache.adapter.redis
                provider: app.redis.provider

# config/services.yaml
services:
    app.redis.provider:
        class: \Redis
        factory: ['Symfony\Component\Cache\Adapter\RedisAdapter', 'createConnection']
        arguments:
            - 'redis://redis'  # this must be set in an env var
```

use this provider as follows

```php
// code/src/Entity/Article.php
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(),
        new Get(provider: ArticleStateProvider::class)
    ]
)]
class Article
{
  ...
}
```

