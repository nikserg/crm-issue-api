<?php

namespace nikserg\CRMIssueAPI;


use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use nikserg\CRMIssueAPI\exceptions\InvalidRequestException;
use nikserg\CRMIssueAPI\exceptions\NotFoundException;
use nikserg\CRMIssueAPI\exceptions\ServerException;
use nikserg\CRMIssueAPI\exceptions\TransportException;
use nikserg\CRMIssueAPI\models\IssueInfo;
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
        $options[RequestOptions::QUERY]['apiKey'] = $this->apiKey;
        $fullUrl = "$this->url/gateway/issue/$endpoint";
        try {
            $response = $this->guzzle->request($method, $fullUrl, $options);
        } catch (GuzzleException $e) {
            throw new TransportException("Ошибка запроса; {$e->getMessage()}");
        }
        switch ($response->getStatusCode()) {
            case 200:
            case 204:
                return $response;
            case 400:
                throw new InvalidRequestException($method . ' ' . $fullUrl . ": Неверный формат запроса " . $response->getBody()->getContents() . ' options: ' . print_r($options, true));
            case 404:
                throw new NotFoundException($method . ' ' . $fullUrl . ": Сущность или точка АПИ не найдены " . $response->getBody()->getContents() . ' options: ' . print_r($options, true));
            case 500:
                throw new ServerException($method . ' ' . $fullUrl . ": Ошибка сервера \n" . $response->getBody()->getContents() . ' options: ' . print_r($options, true));
            default:
                throw new TransportException($method . ' ' . $fullUrl . ": Неожиданный код ответа {$response->getStatusCode()} " . $response->getBody()->getContents() . ' options: ' . print_r($options, true));
        }
    }

    /**
     * @param ResponseInterface $response
     * @return array
     * @throws ServerException
     * @throws TransportException
     */
    private function parseResponse($response)
    {
        $json = @json_decode($response->getBody()->getContents(), true);
        if (!$json || !isset($json['code'])) {
            throw new TransportException("В ответ получен не json или json имеет неправильный формат " . $response->getBody()->getContents());
        }
        if ($json['code'] != 'OK') {
            throw new ServerException('Не удалось выполнить действие: ' . $json['message']);
        }

        return $json['message'];
    }

    /**
     * Создать задачу
     *
     *
     * @param $type
     * @param $customerFormId
     * @param $comment
     * @return int ID созданной задачи
     * @throws InvalidRequestException
     * @throws NotFoundException
     * @throws ServerException
     * @throws TransportException
     */
    public function create($type, $customerFormId, $comment)
    {
        $json = $this->parseResponse($this->request('GET', 'addIssue', [
            RequestOptions::QUERY => [
                'type'           => $type,
                'customerFormId' => $customerFormId,
                'description'    => $comment,
            ],
        ]));

        return intval($json);
    }

    /**
     * Получить информацию о задаче
     *
     *
     * @param $id
     * @return IssueInfo
     * @throws InvalidRequestException
     * @throws NotFoundException
     * @throws ServerException
     * @throws TransportException
     */
    public function getInfo($id)
    {
        $json = $this->parseResponse($this->request('GET', 'issueInfo', [
            RequestOptions::QUERY => [
                'id' => $id,
            ],
        ]));
        $model = new IssueInfo();
        foreach ($json as $key => $value) {
            $model->{$key} = $value;
        }
        return $model;
    }
}
