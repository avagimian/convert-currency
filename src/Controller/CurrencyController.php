<?php

namespace App\Controller;

use App\Services\ConvertCurrencyRates\ConvertCurrencyRatesService;
use App\Services\ShowCurrencyRates\ShowCurrencyRatesService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class CurrencyController extends AbstractController
{
    public function __construct(public string $baseCurrency) {}

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/currency')]
    public function getCurrencyRates(
        Request $request,
        ShowCurrencyRatesService $showCurrencyRatesService,
    ): JsonResponse {
        $baseCurrency = strtoupper($request->query->get('currency', $this->baseCurrency));

        $response = $showCurrencyRatesService->index($baseCurrency);

        return new JsonResponse($response);
    }

    #[Route('/convert-currency')]
    public function convertCurrency(
        Request $request,
        ConvertCurrencyRatesService $convertCurrencyRatesService
    ): JsonResponse {
        $currencyFrom = $request->query->get('currency_from');
        $currencyTo = $request->query->get('currency_to');
        $amount = (float)$request->query->get('amount', 1);

        $response = $convertCurrencyRatesService->run($currencyFrom, $currencyTo, $amount);

        return new JsonResponse($response);
    }
}
