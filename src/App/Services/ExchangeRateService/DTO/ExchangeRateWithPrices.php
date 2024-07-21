<?php

namespace App\Services\ExchangeRateService\DTO;

class ExchangeRateWithPrices implements \JsonSerializable
{
    private $currency;

    private $code;

    private $buyPrice;

    private $sellPrice;

    public function __construct(
        string $currency,
        string $code,
        ?float $buyPrice = null,
        ?float $sellPrice = null
    ) {
        $this->currency = $currency;
        $this->code = $code;
        $this->buyPrice = $buyPrice;
        $this->sellPrice = $sellPrice;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getBuyPrice()
    {
        return $this->buyPrice;
    }

    public function getSellPrice()
    {
        return $this->sellPrice;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
