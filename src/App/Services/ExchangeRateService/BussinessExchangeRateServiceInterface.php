<?php

namespace App\Services\ExchangeRateService;

interface BussinessExchangeRateServiceInterface extends ExchangeRateServiceInterface
{
    // Info:  Normally I'd use an enum for this (in php 7.2 we'd have to implement it first)
    public const ALLOWED_CURRENCIES = [ 'USD', 'EUR', 'CZK', 'IDR', 'BRL' ];

    public const MIN_DATE = '2023-01-01';
    public const MAX_DATE = 'today';
}
