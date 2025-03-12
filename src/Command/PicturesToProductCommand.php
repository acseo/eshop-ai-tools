<?php

namespace ACSEO\EshopAiTools\Command;

use ACSEO\EshopAiTools\Service\PictureToProduct;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PicturesToProductCommand extends BaseCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->setName('pictures-to-product')
            ->setDescription('Analyze one or more pictures to generate product information')
            ->addArgument(
                'pictures',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'Pictures you want to analyze in order to generate product information'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $client = $this->getClient($input);

        $pictures = $input->getArgument('pictures');
        $lang = $input->getOption('locale');

        $pictureToProduct = new PictureToProduct($client);
        $description = $pictureToProduct->fromPictures($pictures, $lang);

        $desc = $description['content'];
        $rows = [];
        foreach ($desc as $row) {
            $rows[] = [
                $row['title'],
                $row['brand'],
                $row['type'],
                $row['keywords'],
                $row['quantity'],
            ];
        }

        $table = new Table($output);
        $table
            ->setHeaders([
                str_pad('title', 20),
                str_pad('brand', 20),
                str_pad('type', 20),
                str_pad('keywords', 20),
                str_pad('quantity', 20),
            ])
            ->setRows($rows);
        $table->render();

        return Command::SUCCESS;
    }
}
