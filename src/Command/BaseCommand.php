<?php

namespace ACSEO\EshopAiTools\Command;

use OpenAI\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class BaseCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption(
            'baseUri',
            'uri',
            InputOption::VALUE_OPTIONAL,
            'LLM API Endpoint (eg: "openai.example.com/v1")',
            'api.openai.com/v1'
        );
        $this->addOption(
            'apiKey',
            'key',
            InputOption::VALUE_REQUIRED,
            'LLM API KEY (eg: "sk-xxx")',
            false
        );
        $this->addOption(
            'existingTags',
            'tags',
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            'Existing tags that may be used',
            []
        );
        $this->addOption(
            'locale',
            'lang',
            InputOption::VALUE_OPTIONAL,
            'Lang to use',
            'en_US'
        );
        $this->addOption(
            'model',
            null,
            InputOption::VALUE_OPTIONAL,
            'Model to use',
            'gpt-4o-mini'
        );
    }

    protected function getClient(InputInterface $input): Client
    {
        $apiKey = $input->getOption('apiKey');
        if (!$apiKey) {
            throw new \RuntimeException('You must provide an apiKey (--apiKey=xxx)');
        }

        return \OpenAI::factory()
            ->withApiKey($apiKey)
            ->withBaseUri($input->getOption('baseUri'))
            ->make();
    }

    protected function renderTable(InputInterface $input, OutputInterface $output, array $data, array $headers): void
    {
        $table = new SymfonyStyle($input, $output);
        $table->table($headers, $data);
    }
}
