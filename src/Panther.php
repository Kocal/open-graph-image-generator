<?php

namespace App;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Panther\Client;

final readonly class Panther
{
    public function __construct(
        #[Autowire(env: 'resolve:PANTHER_CHROME_DRIVER')]
        private string $chromeDriverBinary,
        #[Autowire(param: 'kernel.debug')]
        private bool $debug,
    ) {
    }

    public function getClient(): Client
    {
        return Client::createChromeClient($this->chromeDriverBinary, null, [
            'capabilities' => ['acceptInsecureCerts' => $this->debug]
        ]);
    }
}