# Open Graph Image Generator

## Requirements

- PHP
- Symfony CLI
- Docker

## Installation

1. Clone the repository
```shell
$ git clone https://github.com/Kocal/open-graph-image-generator.git
```

2. Edit the `.env.local` and configure `APP_OPENGRAPH_IMAGE_GENERATION_ALLOWED_DOMAINS` to allow your domain(s) to generate Open Graph images.

3. Run the following commands:
```shell
$ docker compose up -d
$ symfony composer install
```

## Usage

Run the project:

```shell
$ symfony serve
```

## Programmatic usage

You can use this project programmatically by sending a `GET` request to the `/generate` endpoint with the following parameters:
- `format` (required): The image format, can be `html` or `image`
- `url` (required): The URL to generate the Open Graph image

Example: https://127.0.0.1:8000/generate?url=https://hugo.alliau.me/posts/2023-10-21-blackfire-and-symfony-cli.html&format=image