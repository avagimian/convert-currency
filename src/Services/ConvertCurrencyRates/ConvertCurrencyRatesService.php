<?php

namespace App\Services\ConvertCurrencyRates;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ConvertCurrencyRatesService
{
    public function __construct(#[Autowire('%kernel.project_dir%/')] private $projectDir)
    {
    }

    public function run($currencyFrom, $currencyTo, $amount): array
    {
        if (!$currencyFrom || !$currencyTo) {
            return ['error' => 'Both currency_from and currency_to parameters are required', 'status' => 400];
        }

        $filePath = $this->projectDir . '/currency_' . 'USD' . '.json';

        if (!file_exists($filePath)) {
            return ['error' => 'Currency rates file not found', 'status' => 404];
        }

        $data = json_decode(file_get_contents($filePath), true);

        $rateFrom = null;
        $rateTo = null;

        foreach ($data['rates'] as $rateData) {
            if ($rateData['code'] === $currencyFrom) {
                $rateFrom = $rateData['rate'];
            }
            if ($rateData['code'] === $currencyTo) {
                $rateTo = $rateData['rate'];
            }
        }

        if ($rateFrom === null || $rateTo === null) {
            return ['error' => 'Currency rate not found', 'status' => 404];
        }

        $convertedAmount = $amount * ($rateTo / $rateFrom);

        return [
            'amount' => $convertedAmount,
            'currency_from' => [
                'rate' => $rateFrom,
                'code' => $currencyFrom,
            ],
            'currency_to' => [
                'rate' => $rateTo,
                'code' => $currencyTo,
            ]
        ];
    }
}