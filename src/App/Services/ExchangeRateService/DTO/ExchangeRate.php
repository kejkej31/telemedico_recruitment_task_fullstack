<?php

namespace App\Services\ExchangeRateService\DTO;

class ExchangeRate implements \JsonSerializable
{
    private $currency;

    private $code;

    private $mid;

    private $buyPrice;

    private $sellPrice;

    public function __construct(
        string $currency,
        string $code,
        float $mid
    ) {
        $this->currency = $currency;
        $this->code = $code;
        $this->mid = $mid;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getMid()
    {
        return $this->mid;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
