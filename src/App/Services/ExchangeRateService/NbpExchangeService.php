<?php

namespace App\Services\ExchangeRateService;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Services\ExchangeRateService\DTO\ExchangeRate;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Services\ExchangeRateService\ExchangeRateServiceInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

class NbpExchangeService implements ExchangeRateServiceInterface
{
    protected $httpClient;

    protected $cache;

    protected $logger;

    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $logger
    ) {
        $this->httpClient = $httpClient;
        $this->cache = [];
        $this->logger = $logger;
    }

    /**
     * Get exchange rates for given date
     * @param \DateTimeInterface $date
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @return ExchangeRate[]
     */
    public function getExchangeRates(\DateTimeInterface $date): array
    {
        $date = $date->format('Y-m-d');
        if(isset($this->cache[$date])) {
            return $this->cache[$date];
        }
        try {
            $response = $this->httpClient->request('GET', "https://api.nbp.pl/api/exchangerates/tables/A/{$date}?format=json");
            $this->cache[$date] = $response->toArray();
            return array_map(function ($rate) {
                return new ExchangeRate(
                    $rate['currency'],
                    $rate['code'],
                    $rate['mid']
                );
            }, $this->cache[$date][0]['rates']);
        } catch (ClientExceptionInterface $e) {
            return [];
        } catch (\Exception $e) {
            $this->logger->error('Error fetching rates: ' . $e->getMessage());
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'Error fetching rates');
        }
    }
}
