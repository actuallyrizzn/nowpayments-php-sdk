<?php

namespace NowPayments;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\ClientInterface;
use NowPayments\Services\PaymentsService;
use NowPayments\Services\SubscriptionsService;
use NowPayments\Services\PayoutsService;
use NowPayments\Services\CustodyService;
use NowPayments\Services\ConversionsService;
use NowPayments\Services\GeneralService;
use NowPayments\Services\IpnService;
use NowPayments\Exception\ConfigurationException;

/**
 * Main client for the NowPayments API
 */
class NowPaymentsClient
{
    private const API_BASE_URL = 'https://api.nowpayments.io/v1';
    private const SANDBOX_BASE_URL = 'https://api-sandbox.nowpayments.io/v1';

    private string $apiKey;
    private string $baseUrl;
    private ?string $ipnSecret;
    private ClientInterface $httpClient;
    private array $services = [];

    /**
     * Create a new NowPayments client
     *
     * @param string $apiKey Your NowPayments API key
     * @param array $config Configuration options
     * @throws ConfigurationException
     */
    public function __construct(string $apiKey, array $config = [])
    {
        if (empty($apiKey)) {
            throw new ConfigurationException('API key is required');
        }

        $this->apiKey = $apiKey;
        $this->baseUrl = $config['sandbox'] ?? false ? self::SANDBOX_BASE_URL : self::API_BASE_URL;
        $this->ipnSecret = $config['ipn_secret'] ?? null;
        
        // Initialize HTTP client
        $this->httpClient = $config['http_client'] ?? new HttpClient([
            'timeout' => 30,
            'connect_timeout' => 10,
            'headers' => [
                'User-Agent' => 'NowPayments-PHP-SDK/1.0',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'x-api-key' => $this->apiKey
            ]
        ]);
    }

    /**
     * Get the API key
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * Get the base URL
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Get the IPN secret
     */
    public function getIpnSecret(): ?string
    {
        return $this->ipnSecret;
    }

    /**
     * Get the HTTP client
     */
    public function getHttpClient(): ClientInterface
    {
        return $this->httpClient;
    }

    /**
     * Access the Payments API
     */
    public function payments(): PaymentsService
    {
        if (!isset($this->services['payments'])) {
            $this->services['payments'] = new PaymentsService($this);
        }
        return $this->services['payments'];
    }

    /**
     * Access the Subscriptions API
     */
    public function subscriptions(): SubscriptionsService
    {
        if (!isset($this->services['subscriptions'])) {
            $this->services['subscriptions'] = new SubscriptionsService($this);
        }
        return $this->services['subscriptions'];
    }

    /**
     * Access the Payouts API
     */
    public function payouts(): PayoutsService
    {
        if (!isset($this->services['payouts'])) {
            $this->services['payouts'] = new PayoutsService($this);
        }
        return $this->services['payouts'];
    }

    /**
     * Access the Custody API
     */
    public function custody(): CustodyService
    {
        if (!isset($this->services['custody'])) {
            $this->services['custody'] = new CustodyService($this);
        }
        return $this->services['custody'];
    }

    /**
     * Access the Conversions API
     */
    public function conversions(): ConversionsService
    {
        if (!isset($this->services['conversions'])) {
            $this->services['conversions'] = new ConversionsService($this);
        }
        return $this->services['conversions'];
    }

    /**
     * Access the General API (status, currencies, etc.)
     */
    public function general(): GeneralService
    {
        if (!isset($this->services['general'])) {
            $this->services['general'] = new GeneralService($this);
        }
        return $this->services['general'];
    }

    /**
     * Access the IPN service
     */
    public function ipn(): IpnService
    {
        if (!isset($this->services['ipn'])) {
            $this->services['ipn'] = new IpnService($this);
        }
        return $this->services['ipn'];
    }

    /**
     * Check API status
     */
    public function getStatus(): array
    {
        return $this->general()->getStatus();
    }

    /**
     * Get available currencies
     */
    public function getCurrencies(): array
    {
        return $this->general()->getCurrencies();
    }

    /**
     * Get merchant active currencies
     */
    public function getMerchantCurrencies(): array
    {
        return $this->general()->getMerchantCurrencies();
    }
} 