<?php

namespace App\Services\ExchangeRateService;

use App\Services\ExchangeRateService\DTO\ExchangeRate;
use App\Services\ExchangeRateService\Exceptions\InvalidDate;
use App\Services\ExchangeRateService\DTO\ExchangeRateWithPrices;
use App\Services\ExchangeRateService\ExchangeRateServiceInterface;
use App\Services\ExchangeRateService\BussinessExchangeRateServiceInterface;

/**
 * Uses exchange rate service to get raw exchange rates and implement bussiness logic
 * KK: This could use a better name + different namespace (directory)
 */
class BussinessExchangeRateService implements BussinessExchangeRateServiceInterface
{
    public const PRICE_MARGIN = [
        'USD' => [
            'buy' => -0.05,
            'sell' => 0.07
        ],
        'EUR' => [
            'buy' => 0.05,
            'sell' => 0.07
        ],
        'CZK' => [
            'buy' => null,
            'sell' => 0.15
        ],
        'IDR' => [
            'buy' => null,
            'sell' => 0.15
        ],
        'BRL' => [
            'buy' => null,
            'sell' => 0.15
        ],
    ];

    protected $minDate;

    protected $maxDate;

    protected $exchangeRateService;

    public function __construct(ExchangeRateServiceInterface $exchangeRateService)
    {
        $this->exchangeRateService = $exchangeRateService;
        $this->minDate = new \DateTime('2023-01-01');
        $this->maxDate = new \DateTime();
    }

    /**
     * Get exchange rates for given date with calculated buy and sell prices
     * @param \DateTimeInterface $date
     * @throws \InvalidArgumentException
     * @return ExchangeRateWithPrices[]
     */
    public function getExchangeRates(\DateTimeInterface $date): array
    {
        if($this->isValidDate($date) === false) {
            throw new InvalidDate(
                'Date must be between '.
                $this->minDate->format('Y-m-d') .' and ' . $this->maxDate->format('Y-m-d')
            );
        }
        $rawRates = $this->exchangeRateService->getExchangeRates($date);
        $enrichedRates = [];

        /** @var ExchangeRate $rate */
        foreach ($rawRates as $rate) {
            if ($this->isSupportedCurrency($rate->getCode()) === false) {
                continue;
            }
            $enrichedRates[] = new ExchangeRateWithPrices(
                $rate->getCurrency(),
                $rate->getCode(),
                $this->calculateBuyPriceForCurrency($rate),
                $this->calculateSellPriceForCurrency($rate)
            );
        }

        return $enrichedRates;
    }

    public function calculateBuyPriceForCurrency(ExchangeRate $rate): ?float
    {
        return $this->calculatePrice($rate, 'buy');
    }

    public function calculateSellPriceForCurrency(ExchangeRate $rate): ?float
    {
        return $this->calculatePrice($rate, 'sell');
    }

    private function calculatePrice(ExchangeRate $rate, string $type): ?float
    {
        if($this->isSupportedCurrency($rate->getCode()) === false) {
            return null;
        }
        $margin = self::PRICE_MARGIN[$rate->getCode()][$type];
        return $margin ? $rate->getMid() + $margin : null;
    }

    public function isSupportedCurrency(string $currency)
    {
        return in_array(strtoupper($currency), static::ALLOWED_CURRENCIES);
    }

    public function isValidDate(\DateTimeInterface $date): bool
    {
        return $date <= new \DateTime(static::MAX_DATE) && $date >= new \DateTime(static::MIN_DATE);
    }
}
