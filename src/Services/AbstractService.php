<?php

namespace NowPayments\Services;

use NowPayments\Exception\ValidationException;
use NowPayments\NowPaymentsClient;
use NowPayments\Exception\ApiException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Abstract base class for all API services
 */
abstract class AbstractService
{
    protected NowPaymentsClient $client;

    public function __construct(NowPaymentsClient $client)
    {
        $this->client = $client;
    }

    /**
     * Make a GET request to the API
     *
     * @param string $endpoint The API endpoint
     * @param array $query Query parameters
     * @return array
     * @throws ApiException
     */
    protected function get(string $endpoint, array $query = []): array
    {
        return $this->request('GET', $endpoint, ['query' => $query]);
    }

    /**
     * Make a POST request to the API
     *
     * @param string $endpoint The API endpoint
     * @param array $data Request body data
     * @return array
     * @throws ApiException
     */
    protected function post(string $endpoint, array $data = []): array
    {
        return $this->request('POST', $endpoint, ['json' => $data]);
    }

    /**
     * Make a PATCH request to the API
     *
     * @param string $endpoint The API endpoint
     * @param array $data Request body data
     * @return array
     * @throws ApiException
     */
    protected function patch(string $endpoint, array $data = []): array
    {
        return $this->request('PATCH', $endpoint, ['json' => $data]);
    }

    /**
     * Make a DELETE request to the API
     *
     * @param string $endpoint The API endpoint
     * @return array
     * @throws ApiException
     */
    protected function delete(string $endpoint): array
    {
        return $this->request('DELETE', $endpoint);
    }

    /**
     * Make a request to the API
     *
     * @param string $method HTTP method
     * @param string $endpoint The API endpoint
     * @param array $options Request options
     * @return array
     * @throws ApiException
     */
    protected function request(string $method, string $endpoint, array $options = []): array
    {
        $url = $this->client->getBaseUrl() . '/' . ltrim($endpoint, '/');
        
        try {
            $response = $this->client->getHttpClient()->request($method, $url, $options);
            $body = $response->getBody()->getContents();
            
            return json_decode($body, true) ?? [];
        } catch (RequestException $e) {
            $this->handleRequestException($e);
        } catch (GuzzleException $e) {
            throw new ApiException('HTTP request failed: ' . $e->getMessage(), 0, []);
        }
    }

    /**
     * Handle request exceptions
     *
     * @param RequestException $e
     * @throws ApiException
     */
    protected function handleRequestException(RequestException $e): void
    {
        $response = $e->getResponse();
        $statusCode = $response ? $response->getStatusCode() : 0;
        $body = $response ? $response->getBody()->getContents() : '';
        
        $responseData = [];
        if ($body) {
            $responseData = json_decode($body, true) ?? [];
        }

        $message = $responseData['message'] ?? $e->getMessage();
        
        throw new ApiException($message, $statusCode, $responseData);
    }

    /**
     * Validate required fields in an array
     *
     * @param array $data The data to validate
     * @param array $requiredFields List of required field names
     * @throws ValidationException
     */
    protected function validateRequiredFields(array $data, array $requiredFields): void
    {
        $missing = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || ($data[$field] !== 0 && empty($data[$field]))) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            throw new ValidationException('Missing required fields: ' . implode(', ', $missing));
        }
    }
}