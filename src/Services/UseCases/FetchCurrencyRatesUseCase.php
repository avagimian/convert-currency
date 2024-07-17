<?php

namespace App\Services\UseCases;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FetchCurrencyRatesUseCase
{
    public function __construct(
        public HttpClientInterface $client,
        public string $coinpaprikaApiUrl,
        public string $floatratesApiUrl,
        #[Autowire('%kernel.project_dir%/')] private $projectDir
    ) {
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function run($currency): void
    {
        $currency = strtoupper($currency);
        $coinpaprikaResponse = $this->client->request('GET', $this->coinpaprikaApiUrl, [
            'query' => [
                'quotes' => $currency,
            ],
        ]);

        $coinbaseData = $coinpaprikaResponse->toArray();
        $coinpaprikaRates = [];

        foreach ($coinbaseData as $market) {
            $baseCurrencyCode = explode("-", $market['base_currency_id'])[0];
            $quoteCurrency = $market['quotes'][$currency] ?? null;

            if ($quoteCurrency) {
                $coinpaprikaRates[] = [
                    'code' => strtoupper($baseCurrencyCode),
                    'rate' => $quoteCurrency['price']
                ];
            }
        }

        $floatratesResponse = $this->client->request('GET', $this->floatratesApiUrl . $currency . '.json');
        $formattedData = [];

        if ($floatratesResponse->getStatusCode() == 200) {
            $floatRatesData = $floatratesResponse->toArray();

            foreach ($floatRatesData as $code => $rateData) {
                $formattedData[] = [
                    'rate' => $rateData['rate'],
                    'code' => strtoupper($code),
                ];
            }

            $formattedData[] = [
                'rate' => 1,
                'code' => $currency,
            ];
        }

        $result = array_merge($formattedData, $coinpaprikaRates);

        $dataToSave = [
            'base_currency' => $currency,
            'rates' => $result,
        ];

        $filePath = $this->projectDir . "currency_$currency.json";

        file_put_contents($filePath, json_encode($dataToSave));
    }
}