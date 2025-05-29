<?php

namespace ACSEO\EshopAiTools\Command;

use ACSEO\EshopAiTools\Service\GenerateTags;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TextToTagsCommand extends BaseCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->setName('text-to-tags')
            ->setDescription('Analyze a text to generate a list of tags')
            ->addArgument(
                'text',
                InputArgument::REQUIRED,
                'Text you want to analyze in order to generate tags'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $client = $this->getClient($input);
        } catch (\RuntimeException $e) {
            $output->writeln('<fg=yellow>'.$e->getMessage().'</>');

            return Command::INVALID;
        }

        $text = $input->getArgument('text');
        $lang = $input->getOption('locale');
        $existingTags = $input->getOption('existingTags');
        $model = $input->getOption('model');

        $generateTagsService = new GenerateTags($client, $model);
        $tags = $generateTagsService->fromText($text, $lang, $existingTags);

        $data = array_map(function ($tag) {
            return [$tag['name']];
        }, $tags['tags']);

        $this->renderTable($input, $output, $data, ['Tag']);

        return Command::SUCCESS;
    }
}
