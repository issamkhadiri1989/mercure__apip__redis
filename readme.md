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

the `http://localhost/send-request` is used here to publish the event (suppose we got there some logic to send the notification)

the page `http://localhost/` the page where the user will receive the notification in real time

the JS code is written in the `code/templates/mercure/index.html.twig`.


