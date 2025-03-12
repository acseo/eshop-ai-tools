<?php

namespace ACSEO\EshopAiTools\Command;

use ACSEO\EshopAiTools\Service\GenerateTags;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PicturesToTagsCommand extends BaseCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->setName('pictures-to-tags')
            ->setDescription('Analyze one or more pictures to generate a list of tags')
            ->addArgument(
                'pictures',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'Pictures you want to analyze in order to generate tags'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $client = $this->getClient($input);

        $pictures = $input->getArgument('pictures');
        $lang = $input->getOption('locale');
        $existingTags = $input->getOption('existingTags');

        $pictureToTag = new GenerateTags($client);
        $tags = $pictureToTag->fromPictures($pictures, $lang, $existingTags);

        $this->renderTable($input, $output, array_map(function ($tag) {
            return [$tag['name']];
        }, $tags['tags']), ['Tag']);

        return Command::SUCCESS;
    }
}
