<?php

namespace ACSEO\EshopAiTools\Command;

use ACSEO\EshopAiTools\Service\GenerateDescription;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TextToDescriptionCommand extends BaseCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->setName('text-to-description')
            ->setDescription('Analyze a text to generate description of it')
            ->addArgument(
                'text',
                InputArgument::REQUIRED,
                'Text you want to analyze in order to generate description'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $client = $this->getClient($input);

        $text = $input->getArgument('text');
        $lang = $input->getOption('locale');
        $existingTags = $input->getOption('existingTags');

        $textToDescription = new GenerateDescription($client);
        $description = $textToDescription->fromText($text, $lang, $existingTags);
        $desc = $description['content'][0];

        $table = new Table($output);
        $table
            ->setHeaders([
                str_pad('title', 20),
                str_pad('short_description', 20),
                str_pad('description', 20),
                str_pad('meta_description', 20),
                str_pad('meta_keywords', 20),
            ])
            ->setRows([[
                $desc['title'],
                $desc['short_description'],
                $desc['description'],
                $desc['meta_description'],
                $desc['meta_keywords'],
            ]]);
        $table->setVertical();
        $table->setStyle('compact');
        $table->render();

        return Command::SUCCESS;
    }
}
