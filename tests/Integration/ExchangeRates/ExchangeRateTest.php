<?php

namespace Integration\ExchangeRates;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\Response\MockResponse;
use App\Services\ExchangeRateService\NbpExchangeService;
use App\Services\ExchangeRateService\DTO\ExchangeRateWithPrices;
use App\Services\ExchangeRateService\ExchangeRateServiceInterface;
use App\Services\ExchangeRateService\BussinessExchangeRateServiceInterface;

class ExchangeRateTest extends WebTestCase
{
    protected $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->mockService();
    }

    // Info:  API calls can cost a lot, modify data etc., that's why we mock it
    // We might want to test the API itself, but that depends
    protected function mockService()
    {
        $mockResponseJson = file_get_contents(__DIR__ . '/Mocks/nbp-response.json');
        $mockResponse = new MockResponse($mockResponseJson);

        $httpClient = new MockHttpClient($mockResponse);
        $exchangeRateService = new NbpExchangeService($httpClient, $this->createMock(LoggerInterface::class));
        self::$container->set(ExchangeRateServiceInterface::class, $exchangeRateService);

    }

    public function testReturnsExchangeRates(): void
    {
        $url = self::$container->get('router')->generate('exchange_rates', ['date' => '2024-01-02']);
        $this->client->request('GET', $url);
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);

        // Info:  DTO should just have interface + method for this
        $object = json_decode(json_encode(
            new ExchangeRateWithPrices(
                "dolar amerykański",
                "USD",
                3.9422,
                4.0622
            )
        ), true);
        $this->assertEquals($object, $responseData[0]);
    }

    public function testReturnsOnlyWhitelistedCurrencies()
    {
        $url = self::$container->get('router')->generate('exchange_rates', ['date' => '2024-01-02']);
        $this->client->request('GET', $url);
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);
        $diff = array_diff(array_column($responseData, 'code'), BussinessExchangeRateServiceInterface::ALLOWED_CURRENCIES);
        $this->assertTrue(empty($diff));
    }

    public function testValidatesDateCorrectly()
    {
        $url = self::$container->get('router')->generate('exchange_rates', ['date' => '2022-01-02']);
        $this->client->request('GET', $url);
        $this->assertResponseStatusCodeSame(400);
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertStringContainsString('Date must be between', $responseData['error']);
    }

}
