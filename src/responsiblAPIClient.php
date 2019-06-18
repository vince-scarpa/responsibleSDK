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
     * [$token]
     * @var string
     */
    private static $token = '';

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
    public static function request($url, $method)
    {
        self::$endpoint = $url;
        $token = self::$token;

        if(empty($token)) {
            return self::accessToken();
        }

        $curl = curl_init(self::$domain . self::$endpoint);

        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer {$token}",
                "cache-control: no-cache",
            ),
        ));

        if (self::$port) {
            curl_setopt($curl, CURLOPT_PROXYPORT, self::$port);
        }

        if( self::getHTTPVersion() > 1 ) {
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
            CURLOPT_USERPWD => $auth_string,
            CURLOPT_HTTPHEADER => array(
                'Content-type: application/x-www-form-urlencoded',
            ),
        ));

        if (self::$port) {
            curl_setopt($curl, CURLOPT_PROXYPORT, self::$port);
        }

        if( self::getHTTPVersion() > 1 ) {
            curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
        }

        $response = curl_exec($curl);

        if ($error = curl_errno($curl)) {
            throw new \Exception($error);
        }

        curl_close($curl);

        if (is_object($response = json_decode($response))) {
            if ((isset($response->headerStatus) && $response->headerStatus < 400) &&
                (isset($response->refresh_token) && !empty($response->refresh_token))) {
                self::store('responsible_token', $response->refresh_token);
                return self::get(self::$endpoint, $response->refresh_token);

            } else {
                return json_encode($response);
            }
        }
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
     * [getHTTPVersion Get the servers HTTP version]
     * @return [interger]
     */
    private static function getHTTPVersion()
    {
        $VERSION = '1';

        if(isset($_SERVER['SERVER_PROTOCOL']) && !empty($_SERVER['SERVER_PROTOCOL'])) {
            $split = preg_split("#/#", $_SERVER['SERVER_PROTOCOL']);
            if( !empty($split) && is_array($split) && sizeof($split) == 2 ) {
                $httpVersion = $split[1];
                $VERSION = intval($httpVersion);
            }
            exit;
        }
        return intval($VERSION);
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
            $value,
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
            $value,
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
    public static function get($url, $token)
    {
        self::$token = $token;

        try {
            $response = self::request($url, 'GET');
        } catch (\Exception $e) {
            return json_encode(
                self::handleError($e->getMessage())
            );
        }

        return $response;
    }
}