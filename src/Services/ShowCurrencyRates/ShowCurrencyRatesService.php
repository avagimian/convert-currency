<?php

namespace App\Services\ShowCurrencyRates;

use App\Services\UseCases\FetchCurrencyRatesUseCase;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ShowCurrencyRatesService
{
    public function __construct(
        public HttpClientInterface $client,
        public FetchCurrencyRatesUseCase $fetchCurrencyRatesUseCase,
        #[Autowire('%kernel.project_dir%/')] private $projectDir
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function index(string $baseCurrency)
    {
        $filePath = $this->projectDir . "currency_$baseCurrency.json";

        if (!file_exists($filePath)) {
            $this->fetchCurrencyRatesUseCase->run($baseCurrency);
        }

        return json_decode(file_get_contents($filePath), true);
    }
}