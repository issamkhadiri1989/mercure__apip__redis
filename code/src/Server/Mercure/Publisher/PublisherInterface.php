<?php

namespace App\Server\Mercure\Publisher;

interface PublisherInterface
{
    public function publish(string|array $topics, string $payload): void;
}