<?php

namespace nikserg\CRMIssueAPI;


use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use nikserg\CRMIssueAPI\exceptions\InvalidRequestException;
use nikserg\CRMIssueAPI\exceptions\NotFoundException;
use nikserg\CRMIssueAPI\exceptions\ServerException;
use nikserg\CRMIssueAPI\exceptions\TransportException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Client
 *
 * @package nikserg\CRMIssueAPI
 */
class Client
{
    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $guzzle;

    /**
     * @param string $apiKey
     * @param string $url
     */
    public function __construct($apiKey, $url = 'https://crm.uc-itcom.ru/index.php')
    {
        $this->apiKey = $apiKey;
        $this->url = trim($url, " /");
        $this->guzzle = new \GuzzleHttp\Client([
            RequestOptions::VERIFY      => false,
            RequestOptions::HTTP_ERRORS => false,
        ]);
    }

    /**
     * @param       $method
     * @param       $endpoint
     * @param array $options
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws ServerException
     * @throws TransportException
     * @throws InvalidRequestException
     */
    protected function request($method, $endpoint, $options = [])
    {
        $options[RequestOptions::QUERY]['key'] = $this->apiKey;
        try {
            $response = $this->guzzle->request($method, "$this->url/gateway/issue/$endpoint", $options);
        } catch (GuzzleException $e) {
            throw new TransportException("Ошибка запроса; {$e->getMessage()}");
        }
        switch ($response->getStatusCode()) {
            case 200:
            case 204:
                return $response;
            case 400:
                throw new InvalidRequestException("$endpoint: Неверный формат запроса");
            case 404:
                throw new NotFoundException("$endpoint: Сущность или точка АПИ не найдены");
            case 500:
                throw new ServerException("$endpoint: Ошибка сервера \n" . $response->getBody()->getContents());
            default:
                throw new TransportException("$endpoint: Неожиданный код ответа {$response->getStatusCode()}");
        }
    }

    public function create($type, $customerFormId, $comment)
    {
        $response = $this->request('GET', 'addIssue', [
            RequestOptions::QUERY => [
                'type' => $type,
                'customerFormId' => $customerFormId,
                'comment' => $comment,
            ]
        ]);
        print_r($response);exit;
    }
}
