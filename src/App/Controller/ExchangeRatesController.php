<?php

declare(strict_types=1);

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Services\ExchangeRateService\BussinessExchangeRateServiceInterface;

class ExchangeRatesController extends AbstractController
{
    protected $exchangeRatesService;

    protected $logger = null;

    public function __construct(
        BussinessExchangeRateServiceInterface $exchangeRatesService,
        LoggerInterface $logger
    ) {
        $this->exchangeRatesService = $exchangeRatesService;
        $this->logger = $logger;
    }

    public function getExchangeRates(Request $request)
    {
        $date = new \DateTime($request->query->get('date', date('Y-m-d')));
        try {
            $exchangeRates = $this->exchangeRatesService->getExchangeRates($date);
        } catch (\Exception $e) {
            // Info:  This should be handled better, we should diffenteriate between handled exceptions and unhandled
            // Right now we don't display proper error on frontend anyway
            return $this->json(['error' => $e->getMessage()], $e->getCode());
        }
        // Info:  we intentionally omit "mid" value
        // According to specification we don't need it on frontend, and it could be considered "sensitive"
        return $this->json($exchangeRates);
    }
}
