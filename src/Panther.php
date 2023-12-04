<?php

namespace App;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Panther\Client;

final readonly class Panther
{
    public function __construct(
        #[Autowire(env: 'resolve:PANTHER_SELENIUM_URL')]
        private string $seleniumUrl,
        #[Autowire(param: 'kernel.debug')]
        private bool   $debug,
    ) {
    }

    public function getClient(): Client
    {
        return Client::createSeleniumClient(
            $this->seleniumUrl,
            DesiredCapabilities::chrome()
                ->setCapability('acceptInsecureCerts', $this->debug),
        );
    }
}
