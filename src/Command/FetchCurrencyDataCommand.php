<?php

namespace App\Command;

use App\Services\UseCases\FetchCurrencyRatesUseCase;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsCommand(
    name: 'app:fetch-currency',
    description: 'Add a short description for your command',
)]
class FetchCurrencyDataCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('currency', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    public function __construct(
        public FetchCurrencyRatesUseCase $fetchCurrencyRatesUseCase,
        public string $baseCurrency
    ) {
        parent::__construct();
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $currency = $input->getArgument('currency') ?? $this->baseCurrency;

        $this->fetchCurrencyRatesUseCase->run($currency);

        $io->success('Currency data has been fetched and saved.');

        return Command::SUCCESS;
    }
}
