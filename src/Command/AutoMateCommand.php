<?php

namespace App\Command;

use App\Service\ScraperService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'auto-mate',
    description: 'Add a short description for your command',
)]
class AutoMateCommand extends Command
{
    /**
     * @var ScraperService
     */
    private $scraperService;

    public function __construct(string $name = null, ScraperService $scraperService)
    {
        parent::__construct($name);
        $this->scraperService = $scraperService;
    }

    protected function configure(): void
    {
        $this->addArgument('licensePlate', InputArgument::REQUIRED, 'License Plate');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $licensePlate = $input->getArgument('licensePlate');

        $response = $this->scraperService->getLicensePlateDetails($licensePlate);
        $output->writeln($response);

        return Command::SUCCESS;
    }
}
