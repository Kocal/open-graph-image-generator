# Open Graph Image Generator

This project is a simple Open Graph image generator, it allows you to generate Open Graph images for a given URL.

## Requirements

- [Symfony CLI](https://symfony.com/download)
- Docker and Docker Compose
- PHP 8.3

## Installation

1. Clone the repository and configure the environment:
```shell
git clone https://github.com/Kocal/open-graph-image-generator.git
cd open-graph-image-generator
symfony local:server:ca:install
symfony local:proxy:domain:attach og-image-generator
```

2. Edit the `.env.local` and configure `APP_OPENGRAPH_IMAGE_GENERATION_ALLOWED_DOMAINS` to allow your domain(s) to generate Open Graph images.

3. Install the dependencies with Composer:
```shell
$ symfony composer install
```

## Usage

Run the project:

```shell
$ docker compose up -d
$ symfony serve
```

## Programmatic usage

You can use this project programmatically by sending a `GET` request to the `/generate` endpoint with the following parameters:
- `format` (required): The image format, can be `html` or `image`
- `url` (required): The URL to generate the Open Graph image

Example: https://127.0.0.1:8000/generate?url=https://hugo.alliau.me/posts/2023-10-21-blackfire-and-symfony-cli.html&format=image