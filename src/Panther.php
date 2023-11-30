<?php

namespace App;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Panther\Client;

final readonly class Panther
{
    public function __construct(
        #[Autowire(env: 'resolve:PANTHER_FIREFOX_DRIVER')]
        private string $firefoxDriverBinary,
        #[Autowire(param: 'kernel.debug')]
        private bool $debug,
    ) {
    }

    public function getClient(array|null $arguments = null, array $options = []): Client
    {
        return Client::createFirefoxClient(
            $this->firefoxDriverBinary,
            $arguments === null ? null : ['--headless', ...$arguments],
            array_merge_recursive(
                ['capabilities' => ['acceptInsecureCerts' => $this->debug]],
                $options
            )
        );
    }
}