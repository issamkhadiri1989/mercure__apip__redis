<?php

declare(strict_types=1);

namespace App\Server\Mercure\Publisher;

use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

final class DefaultPublisher implements PublisherInterface
{
    public function __construct(private readonly HubInterface $hub)
    {
    }

    public function publish(string|array $topics, string $payload): void
    {
        $update = new Update($topics, $payload, private: true);

        $this->hub->publish($update);
    }
}
