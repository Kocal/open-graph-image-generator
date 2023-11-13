<?php

namespace App;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Panther\Client;

final readonly class Panther
{
    private Client $client;

    public function __construct(
        #[Autowire(param: 'kernel.debug')]
        private bool $debug,
    ) {
    }

    public function getClient(): Client
    {
        return Client::createChromeClient(null, null, [
            'port' => 9516,
            'capabilities' => ['acceptInsecureCerts' => $this->debug]
        ]);
    }
}