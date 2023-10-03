<?php

/**
 * ======================================
 * Responsible API Simple Client example
 * ======================================
 *
 * @link Git https://github.com/vince-scarpa/responsible.git
 *
 * @api Responible API
 * @package responsiblAPIClient
 *
 * @author Vince scarpa <vince.in2net@gmail.com>
 *
 */

namespace responsibleClient;

class responsiblAPIClient
{
    /**
     * [$domain]
     * @var string
     */
    private static $domain = '';

    /**
     * [$port Optional port]
     * @var string
     */
    private static $port = '';

    /**
     * [$endpoint]
     * @var string
     */
    private static $endpoint = '';

    /**
     * [$credentials]
     * @var array
     */
    private static $credentials = array();

    /**
     * [$methodsSupported Supported Responsible API methods]
     * @var array
     */
    private static $methodsSupported = ['GET', 'POST', 'PUT'];

    /**
     * [$lastRequestMethod]
     * Set the last request method
     * Used for method callback when refresh is called
     *
     * @var [type]
     */
    private static $lastRequestMethod;

    /**
     * [$patload]
     * Set the payload build
     * Used for method callback when refresh is called
     *
     * @var boolean
     */
    private static $payload = false;

    /**
     * [$token]
     * @var string
     */
    private static $token = '';

    /**
     * [$timeout Set the cURL timeout amount]
     * @var [type]
     */
    private static $timeout = 30;

    /**
     * [__construct Client credentials setup]
     * @param [string] $clientID
     * @param [string] $clientSecret
     */
    public function __construct($clientID, $clientSecret)
    {
        self::$credentials = [
            'clientID' => $clientID,
            'clientSecret' => $clientSecret,
        ];
    }

    /**
     * [request Call the resource server via cURL]
     * @param  [string] $url   [Endpoint full URL]
     * @param  [string] $token [The User JWT]
     * @return [object]        [Success or error json object]
     */
    public static function request($url, $method, $payload = [])
    {
        self::methodSupported($method);

        self::$endpoint = $url;
        $token = self::$token;

        if (empty($token)) {
            return self::accessToken();
        }

        $curl = curl_init(self::$domain . self::$endpoint);

        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_TIMEOUT => self::getTimeout(),
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer {$token}",
                "cache-control: no-cache",
            ),
        ));

        if ($method == 'POST') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        }

        if (self::$port) {
            curl_setopt($curl, CURLOPT_PROXYPORT, self::$port);
        }

        if (self::getHTTPVersion() > 1) {
            curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
        }

        $response = curl_exec($curl);

        if ($error = curl_errno($curl)) {
            throw new \Exception($error);
        }

        curl_close($curl);

        if (self::isAccessExpired($response)) {
            return self::accessToken();
        }

        if (self::isAccessInvalid($response)) {
            return self::accessToken();
        }

        return $response;
    }

    /**
     * [accessToken description]
     * @return [type] [description]
     */
    public static function accessToken()
    {
        $client_id = self::$credentials['clientID'];
        $client_secret = self::$credentials['clientSecret'];

        $auth_string = "{$client_id}:{$client_secret}";

        $request = self::$domain . "/token/access_token?grant_type=client_credentials";

        $curl = curl_init($request);

        curl_setopt_array($curl, array(
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 2,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERPWD => $auth_string,
            CURLOPT_HTTPHEADER => array(
                'Content-type: application/x-www-form-urlencoded',
            ),
        ));

        if (self::$port) {
            curl_setopt($curl, CURLOPT_PROXYPORT, self::$port);
        }

        if (self::getHTTPVersion() > 1) {
            curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
        }

        $response = curl_exec($curl);

        if ($error = curl_errno($curl)) {
            throw new \Exception($error);
        }

        curl_close($curl);

        if (is_object($response = json_decode($response))) {
            if (
                (isset($response->headerStatus) && $response->headerStatus < 400) &&
                (isset($response->refresh_token) && !empty($response->refresh_token))
            ) {
                self::$token = $response->refresh_token;
                self::store('responsible_token', self::$token);
                $callback = strtolower(self::$lastRequestMethod);

                return call_user_func_array(
                    [__CLASS__, $callback],
                    [self::$endpoint, self::$token, self::getPayload()]
                );
            } else {
                return json_encode($response);
            }
        }

        throw new \Exception('API Request unknown error: No response returned');
    }

    /**
     * [methodSupported Check if the request method is supported by Responsible API]
     * @param  [string] $method [description]
     * @return [boolean]
     */
    private static function methodSupported($method)
    {
        $method = strtoupper($method);
        if (!in_array($method, self::$methodsSupported)) {
            throw new \Exception('API Request method (' . $method . ') is not supported by the Responsible API');
        }
        self::$lastRequestMethod = $method;
    }

    /**
     * [isAccessExpired Check if the access token has expired]
     * @param  [array]  $response
     * @return [boolean]
     */
    public static function isAccessExpired($response)
    {
        $response = json_decode($response, true);

        if (
            (isset($response['ERROR_CODE']) && $response['ERROR_CODE'] == 401) &&
            (isset($response['MESSAGE']['error']) && $response['MESSAGE']['error'] == 'expired')
        ) {
            return true;
        }

        return false;
    }

    /**
     * [isAccessInvalid Check if the access token is invalid]
     * @param  [array]  $response
     * @return [boolean]
     */
    public static function isAccessInvalid($response)
    {
        $response = json_decode($response, true);

        if (
            (isset($response['ERROR_CODE']) && $response['ERROR_CODE'] == 401) &&
            (isset($response['MESSAGE']['error']) && $response['MESSAGE']['error'] == 'invalid token')
        ) {
            return true;
        }

        return false;
    }

    /**
     * [buildPostString Compress down the payload so the request is not large]
     * @param  [array] $payload [Request body]
     * @return [string] return a base64 encoded string with key "payload"
     */
    public static function buildPostString($payload)
    {
        self::$payload = $payload;

        if (empty($payload)) {
            $payload = json_encode("");
        } else {
            $payload = json_encode($payload);
        }

        return 'payload=' . base64_encode($payload);
    }

    /**
     * [getPayload Return the set payload]
     * @return [string]
     */
    private static function getPayload()
    {
        return self::$payload;
    }

    /**
     * [getHTTPVersion Get the servers HTTP version]
     * @return [interger]
     */
    private static function getHTTPVersion()
    {
        $VERSION = '1';

        if (isset($_SERVER['SERVER_PROTOCOL']) && !empty($_SERVER['SERVER_PROTOCOL'])) {
            $split = preg_split("#/#", $_SERVER['SERVER_PROTOCOL']);
            if (!empty($split) && is_array($split) && sizeof($split) == 2) {
                $httpVersion = $split[1];
                $VERSION = intval($httpVersion);
            }
        }

        if (isset($_SERVER['HTTP2']) && $_SERVER['HTTP2'] == 'on') {
            $VERSION = 2;
        }

        return intval($VERSION);
    }

    /**
     * [setTimeout Set the cURL timeout amount]
     * @param [integer] $time [in seconds]
     */
    public static function setTimeout($time = 30)
    {
        self::$timeout = $time;
    }

    /**
     * [getTimeout Get the cURL timeout]
     * @return [integer]
     */
    private static function getTimeout()
    {
        return self::$timeout;
    }

    /**
     * [handleError Basic error handler]
     * @param  [string] $message [Exception error message]
     * @return [array]
     */
    private static function handleError($message)
    {
        if (ctype_digit($digit = (string) $message)) {
            if ($errorStatus = requestError::error($digit)) {
                return [
                    'ERROR_CODE' => $digit,
                    'ERROR_STATUS' => 'CURL_ERROR',
                    'MESSAGE' => [
                        'error' => $errorStatus,
                        'description' => ucfirst(preg_replace('#_#', ' ', $errorStatus)),
                    ],
                ];
            }
        }
        return [
            'ERROR_CODE' => -1,
            'ERROR_STATUS' => 'CURL_ERROR',
            'MESSAGE' => [
                'error' => -1,
                'description' => 'cURL Error#' . $message,
            ],
        ];
    }

    /**
     * [setAPIDomain Set the API domain]
     * @param [string] $domain
     */
    public static function setAPIDomain($domain, $port = null)
    {
        if (!is_null($port)) {
            self::$port = $port;
        }
        self::$domain = $domain;
    }

    /**
     * [store]
     * @param  [type] $key   [description]
     * @param  [type] $value [description]
     */
    public static function store($key, $value)
    {
        $domain = ($_SERVER['HTTP_HOST'] !== 'localhost') ? $_SERVER['HTTP_HOST'] : false;

        if (!empty(self::$port)) {
            $domain = str_replace(':' . self::$port, '', $domain);
        }

        /**
         * Delete the cookie when a token refresh is called
         */
        setcookie(
            $key,
            $value ?? '',
            time() - 86400,
            '/',
            $domain,
            false
        );
        /**
         * Set a new token cookie
         */
        setcookie(
            $key,
            $value ?? '',
            time() + 300,
            '/',
            $domain,
            false
        );
    }

    /**
     * [API New factory instance of the Responsible request API]
     * @param [string] $clientID
     * @param [string] $clientSecret
     * @return [object:self]
     */
    public static function API($clientID, $clientSecret)
    {
        return new self($clientID, $clientSecret);
    }

    /**
     * [get Try get an endpoint response]
     * @param  [string] $url   [Endpoint full URL]
     * @param  [string] $token [The User JWT]
     * @return [object]        [Success or error json object]
     */
    public static function get($url, $token = '', array $payload = [])
    {
        self::$token = $token;

        try {
            $payload = self::buildPostString($payload);
            $response = self::request($url, 'GET', $payload);
        } catch (\Exception $e) {
            return json_encode(
                self::handleError($e->getMessage())
            );
        }

        return $response;
    }

    /**
     * [post Try get an endpoint response]
     * @param  [string] $url   [Endpoint full URL]
     * @param  [string] $token [The User JWT]
     * @param  [array] $payload [Request body]
     * @return [object]        [Success or error json object]
     */
    public static function post($url, $token = '', array $payload = [])
    {
        self::$token = $token;

        try {
            $payload = self::buildPostString($payload);
            $response = self::request($url, 'POST', $payload);
        } catch (\Exception $e) {
            return json_encode(
                self::handleError($e->getMessage())
            );
        }

        return $response;
    }
}
