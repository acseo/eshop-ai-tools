# ACSEO Eshop AI Tools

## Description

This project is a PHP command-line tool based on Symfony Console. It allows you to analyze text or images to generate tags or descriptions using a large language model (LLM). The features are as follows:

- **text-to-tags**: Analyze a text to generate a list of tags.
- **pictures-to-tags**: Analyze one or more pictures to generate a list of tags.
- **text-to-description**: Analyze a text to generate a complete description.
- **pictures-to-description**: Analyze one or more pictures to generate a complete description.

## Prerequisites

- PHP 7.4 or higher
- Composer
- An account and API key for a large language model (LLM) service such as OpenAI or Perplexity.

## Installation

1. Clone the project repository:

    ```bash
    git clone https://github.com/acseo/eshop-ai-tools
    cd eshop-ai-tools
    ```

2. Install the dependencies using Composer:

    ```bash
    composer install
    ```

## Usage

### Available Commands

1. **text-to-tags**

    Analyze a text to generate tags.

    ```bash
    php bin/eshop-ai-tools text-to-tags "Your text here" --apiKey="your_api_key"
    ```

    Options:
    - `--baseUri`: Specify the LLM API endpoint (default is `api.openai.com/v1`).
    - `--apiKey`: API key for authentication.
    - `--existingTags`: List of existing tags that can be used (ex: `--existingTags="tag1" --existingTags="tag2"`).
    - `--locale`: Language to use (default is `en_US`).
    - `--model`: Model to use (default is `gpt-4o-mini`).

2. **pictures-to-tags**

    Analyze one or more pictures to generate tags.

    ```bash
    php bin/eshop-ai-tools pictures-to-tags image1.jpg image2.png --apiKey="your_api_key"
    ```

    Similar options as `text-to-tags`.

3. **text-to-description**

    Analyze a text to generate a complete description.

    ```bash
    php bin/eshop-ai-tools text-to-description "Your text here" --apiKey="your_api_key"
    ```

    Similar options as `text-to-tags`.

4. **pictures-to-description**

    Analyze one or more pictures to generate a complete description.

    ```bash
    php bin/eshop-ai-tools pictures-to-description image1.jpg image2.png --apiKey="your_api_key"
    ```

    Similar options as `text-to-tags`.

5. **pictures-to-product**

    Analyze a picture and tranform it to a product

    ```bash
    php bin/eshop-ai-tools pictures-to-product product.jpg --apiKey="your_api_key"
    ```

    Similar options as `text-to-tags`.

## Configuration

You can configure default options such as `baseUri` and `locale` directly in the code or via environment variables.

## Current Limitation

The Perplexity API only accepts plain text messages in the `content` field. Sending multimodal data (such as images, audio, or objects like `image_url`) is not supported at this time. Any attempt to send non-text content will result in an error such as “Message content was empty.”
See the [official documentation for supported formats](https://docs.perplexity.ai/api-reference/chat-completions).

## Authors

This project was developed by the ACSEO team.

## License

This project is licensed under the [MIT License](LICENSE).
