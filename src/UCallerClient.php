<?php

namespace IPanov\UcallerClient;

use GuzzleHttp\Client as HttpClient;
use IPanov\UcallerClient\Models\Call;
use IPanov\UcallerClient\Models\Health;
use IPanov\UcallerClient\Models\Model;
use Psr\Http\Message\ResponseInterface;

/**
 * @link https://developer.ucaller.ru
 */
class UCallerClient
{
    const SERVER_IP = '62.113.106.210';
    const HOST_HTTPS = 'https://api.ucaller.ru';
    const HOST_HTTP = 'http://api.ucaller.net';
    const HOST_HTTP_USA = 'http://api.usa.ucaller.net';

    const ALWAYS_SUCCESS_TEST_PHONE_NUMBER = '79000000001';
    const ALWAYS_ERROR_TEST_PHONE_NUMBER = '79000000002';

    private string $secretKey;
    private string $appId;
    private string $baseUrl;
    private HttpClient $httpClient;

    public function __construct(string $secretKey, string $appId, string $baseUrl = self::HOST_HTTPS) {
        $this->secretKey = $secretKey;
        $this->appId = $appId;
        $this->baseUrl = $baseUrl;

        $this->httpClient = new HttpClient([
            'base_uri' => "$baseUrl/v1.0/",
            'timeout' => 10,
            'allow_redirects' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->secretKey . '.' . $this->appId,
            ],
        ]);
    }

    /** Возвращает текущую информацию о состоянии инфраструктуры. */
    public function health(): Health {
        $response = $this->httpClient->get($this->baseUrl . '/health');

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->processResponse($response, Health::class);
    }

    /** Позволяет инициализировать авторизацию для пользователя приложения. */
    public function initCall(
        string $phone,
        ?string $code = null,
        ?string $client = null,
        ?string $unique = null,
        ?bool $voice = null
    ): Call {
        $params = array_filter(compact('phone', 'code', 'client', 'unique', 'voice'));
        $response = $this->httpClient->post('initCall', ['form_params' => $params]);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->processResponse($response, Call::class);
    }

    /** В случае, если ваш пользователь не получает звонок инициализированный методом initCall,
     * вы можете два раза и совершенно бесплатно инициализировать повторную авторизацию по uCaller ID,
     * который вы получаете в ответе метода initCall. Повторную авторизацию можно запросить только
     * в течение пяти минут с момента выполнения основной авторизации методом initCall.
     */
    public function initRepeat(string $uid): Call {
        $params = array_filter(compact('uid'));
        $response = $this->httpClient->post('initRepeat', ['form_params' => $params]);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->processResponse($response, Call::class);
    }

    /** Возвращает развернутую информацию по уже осуществленному uCaller ID. */
    public function getInfo(string $uid): Model {
        throw new \LogicException('Unimplemented');
    }

    /** Возвращает информацию по сервису. */
    public function getService(): Model {
        throw new \LogicException('Unimplemented');
    }

    /** Возвращает текущую информацию о состоянии баланса аккаунта. */
    public function getBalance(): Model {
        throw new \LogicException('Unimplemented');
    }

    /** Возвращает информацию об аккаунте. */
    public function getAccount(): Model {
        throw new \LogicException('Unimplemented');
    }

    /** Проверяет телефон по справочнику DEF. */
    public function checkPhone(string $phone): Model {
        throw new \LogicException('Unimplemented');
    }

    private function processResponse(ResponseInterface $response, string $modelClass): Model {
        $responseData = json_decode($response->getBody()->getContents(), true, JSON_THROW_ON_ERROR);

        if (isset($responseData['error'])) {
            throw new UCallerException($responseData['error'], $responseData['code'] ?? 0);
        }

        $model = new $modelClass();

        if (!($model instanceof Model)) {
            throw new \InvalidArgumentException("$modelClass is not Model");
        }

        return $model->load($responseData);
    }
}
