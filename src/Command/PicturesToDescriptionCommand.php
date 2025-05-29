<?php

namespace ACSEO\EshopAiTools\Command;

use ACSEO\EshopAiTools\Service\GenerateDescription;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PicturesToDescriptionCommand extends BaseCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->setName('pictures-to-description')
            ->setDescription('Analyze one or more pictures to generate description of them')
            ->addArgument(
                'pictures',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'Pictures you want to analyze in order to generate descriptions'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $client = $this->getClient($input);

        $pictures = $input->getArgument('pictures');
        $lang = $input->getOption('locale');
        $existingTags = $input->getOption('existingTags');
        $model = $input->getOption('model');

        $picturesToDescription = new GenerateDescription($client, $model);
        $description = $picturesToDescription->fromPictures($pictures, $lang, $existingTags);
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
