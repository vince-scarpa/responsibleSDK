<?php
/**
 * ======================================
 * Responsible API Simple Client example
 * ======================================
 *
 * @link Git https://github.com/vince-scarpa/responsible.git
 *
 * @api Responible API
 * @package cUrlErrors
 *
 * @author Vince scarpa <vince.in2net@gmail.com>
 *
 */
namespace responsibleClient;

class requestError
{
    /**
     * A list of cURL errors
     * CURLE changed to CURL_ERROR
     * @link  https://curl.haxx.se/libcurl/c/libcurl-errors.html
     */
    const CURL_ERRORS = [
        '1' => 'CURL_ERROR_UNSUPPORTED_PROTOCOL',
        '2' => 'CURL_ERROR_FAILED_INIT',
        '3' => 'CURL_ERROR_URL_MALFORMAT',
        '4' => 'CURL_ERROR_URL_MALFORMAT_USER',
        '5' => 'CURL_ERROR_COULDNT_RESOLVE_PROXY',
        '6' => 'CURL_ERROR_COULDNT_RESOLVE_HOST',
        '7' => 'CURL_ERROR_COULDNT_CONNECT',
        '8' => 'CURL_ERROR_FTP_WEIRD_SERVER_REPLY',
        '9' => 'CURL_ERROR_REMOTE_ACCESS_DENIED',
        '11' => 'CURL_ERROR_FTP_WEIRD_PASS_REPLY',
        '13' => 'CURL_ERROR_FTP_WEIRD_PASV_REPLY',
        '14' => 'CURL_ERROR_FTP_WEIRD_227_FORMAT',
        '15' => 'CURL_ERROR_FTP_CANT_GET_HOST',
        '17' => 'CURL_ERROR_FTP_COULDNT_SET_TYPE',
        '18' => 'CURL_ERROR_PARTIAL_FILE',
        '19' => 'CURL_ERROR_FTP_COULDNT_RETR_FILE',
        '21' => 'CURL_ERROR_QUOTE_ERROR',
        '22' => 'CURL_ERROR_HTTP_RETURNED_ERROR',
        '23' => 'CURL_ERROR_WRITE_ERROR',
        '25' => 'CURL_ERROR_UPLOAD_FAILED',
        '26' => 'CURL_ERROR_READ_ERROR',
        '27' => 'CURL_ERROR_OUT_OF_MEMORY',
        '28' => 'CURL_ERROR_OPERATION_TIMEDOUT',
        '30' => 'CURL_ERROR_FTP_PORT_FAILED',
        '31' => 'CURL_ERROR_FTP_COULDNT_USE_REST',
        '33' => 'CURL_ERROR_RANGE_ERROR',
        '34' => 'CURL_ERROR_HTTP_POST_ERROR',
        '35' => 'CURL_ERROR_SSL_CONNECT_ERROR',
        '36' => 'CURL_ERROR_BAD_DOWNLOAD_RESUME',
        '37' => 'CURL_ERROR_FILE_COULDNT_READ_FILE',
        '38' => 'CURL_ERROR_LDAP_CANNOT_BIND',
        '39' => 'CURL_ERROR_LDAP_SEARCH_FAILED',
        '41' => 'CURL_ERROR_FUNCTION_NOT_FOUND',
        '42' => 'CURL_ERROR_ABORTED_BY_CALLBACK',
        '43' => 'CURL_ERROR_BAD_FUNCTION_ARGUMENT',
        '45' => 'CURL_ERROR_INTERFACE_FAILED',
        '47' => 'CURL_ERROR_TOO_MANY_REDIRECTS',
        '48' => 'CURL_ERROR_UNKNOWN_TELNET_OPTION',
        '49' => 'CURL_ERROR_TELNET_OPTION_SYNTAX',
        '51' => 'CURL_ERROR_PEER_FAILED_VERIFICATION',
        '52' => 'CURL_ERROR_GOT_NOTHING',
        '53' => 'CURL_ERROR_SSL_ENGINE_NOTFOUND',
        '54' => 'CURL_ERROR_SSL_ENGINE_SETFAILED',
        '55' => 'CURL_ERROR_SEND_ERROR',
        '56' => 'CURL_ERROR_RECV_ERROR',
        '58' => 'CURL_ERROR_SSL_CERTPROBLEM',
        '59' => 'CURL_ERROR_SSL_CIPHER',
        '60' => 'CURL_ERROR_SSL_CACERT',
        '61' => 'CURL_ERROR_BAD_CONTENT_ENCODING',
        '62' => 'CURL_ERROR_LDAP_INVALID_URL',
        '63' => 'CURL_ERROR_FILESIZE_EXCEEDED',
        '64' => 'CURL_ERROR_USE_SSL_FAILED',
        '65' => 'CURL_ERROR_SEND_FAIL_REWIND',
        '66' => 'CURL_ERROR_SSL_ENGINE_INITFAILED',
        '67' => 'CURL_ERROR_LOGIN_DENIED',
        '68' => 'CURL_ERROR_TFTP_NOTFOUND',
        '69' => 'CURL_ERROR_TFTP_PERM',
        '70' => 'CURL_ERROR_REMOTE_DISK_FULL',
        '71' => 'CURL_ERROR_TFTP_ILLEGAL',
        '72' => 'CURL_ERROR_TFTP_UNKNOWNID',
        '73' => 'CURL_ERROR_REMOTE_FILE_EXISTS',
        '74' => 'CURL_ERROR_TFTP_NOSUCHUSER',
        '75' => 'CURL_ERROR_CONV_FAILED',
        '76' => 'CURL_ERROR_CONV_REQD',
        '77' => 'CURL_ERROR_SSL_CACERT_BADFILE',
        '78' => 'CURL_ERROR_REMOTE_FILE_NOT_FOUND',
        '79' => 'CURL_ERROR_SSH',
        '80' => 'CURL_ERROR_SSL_SHUTDOWN_FAILED',
        '81' => 'CURL_ERROR_AGAIN',
        '82' => 'CURL_ERROR_SSL_CRL_BADFILE',
        '83' => 'CURL_ERROR_SSL_ISSUER_ERROR',
        '84' => 'CURL_ERROR_FTP_PRET_FAILED',
        '84' => 'CURL_ERROR_FTP_PRET_FAILED',
        '85' => 'CURL_ERROR_RTSP_CSEQ_ERROR',
        '86' => 'CURL_ERROR_RTSP_SESSION_ERROR',
        '87' => 'CURL_ERROR_FTP_BAD_FILE_LIST',
        '88' => 'CURL_ERROR_CHUNK_FAILED',
        '89' => 'CURL_ERROR_NO_CONNECTION_AVAILABLE',
        '90' => 'CURL_ERROR_SSL_PINNEDPUBKEYNOTMATCH',
        '91' => 'CURL_ERROR_SSL_INVALIDCERTSTATUS',
        '92' => 'CURL_ERROR_HTTP2_STREAM',
        '93' => 'CURL_ERROR_RECURSIVE_API_CALL',
    ];

    /**
     * [error Check if the returned error by cURL exists in our list]
     * @param  [integer] $digit [The cURL error code]
     * @return [string]
     */
    public static function error($digit)
    {
        if (isset(self::CURL_ERRORS[$digit])) {
            return self::CURL_ERRORS[$digit];
        }
        return;
    }
}
