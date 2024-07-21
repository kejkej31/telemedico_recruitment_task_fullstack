<?php

namespace App\Services\ExchangeRateService;

interface ExchangeRateServiceInterface
{
    public function getExchangeRates(\DateTimeInterface $date): array;
}
