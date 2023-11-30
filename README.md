# Open Graph Image Generator

This project is a simple Open Graph image generator, it allows you to generate Open Graph images for a given URL.

## Requirements

- [DDEV](https://ddev.com/)

## Installation

1. Clone the repository
```shell
$ git clone https://github.com/Kocal/open-graph-image-generator.git
```

2. Edit the `.env.local` and configure `APP_OPENGRAPH_IMAGE_GENERATION_ALLOWED_DOMAINS` to allow your domain(s) to generate Open Graph images.

3. Run the following commands:
```shell
$ ddev start
$ ddev composer install
```

## Usage

Run the project:

```shell
$ ddev start
```

## Programmatic usage

You can use this project programmatically by sending a `GET` request to the `/generate` endpoint with the following parameters:
- `format` (required): The image format, can be `html` or `image`
- `url` (required): The URL to generate the Open Graph image

Example: https://og-image-generator.ddev.site/generate?url=https://hugo.alliau.me/posts/2023-10-21-blackfire-and-symfony-cli.html&format=image