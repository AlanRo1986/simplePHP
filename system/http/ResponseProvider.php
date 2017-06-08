<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/24 0024
 * Time: 10:22
 */

namespace App\System\Http;


use App\System\Basic\Provider;
use App\System\Data\Cookie;
use App\System\Handler\HeaderHandler;
use App\System\Handler\ResponseHeaderHandler;

class ResponseProvider extends Provider
{
    const HTTP_CONTINUE = 100;
    const HTTP_SWITCHING_PROTOCOLS = 101;
    const HTTP_PROCESSING = 102;            // RFC2518
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_ACCEPTED = 202;
    const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
    const HTTP_NO_CONTENT = 204;
    const HTTP_RESET_CONTENT = 205;
    const HTTP_PARTIAL_CONTENT = 206;
    const HTTP_MULTI_STATUS = 207;          // RFC4918
    const HTTP_ALREADY_REPORTED = 208;      // RFC5842
    const HTTP_IM_USED = 226;               // RFC3229
    const HTTP_MULTIPLE_CHOICES = 300;
    const HTTP_MOVED_PERMANENTLY = 301;
    const HTTP_FOUND = 302;
    const HTTP_SEE_OTHER = 303;
    const HTTP_NOT_MODIFIED = 304;
    const HTTP_USE_PROXY = 305;
    const HTTP_RESERVED = 306;
    const HTTP_TEMPORARY_REDIRECT = 307;
    const HTTP_PERMANENTLY_REDIRECT = 308;  // RFC7238
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_PAYMENT_REQUIRED = 402;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    const HTTP_NOT_ACCEPTABLE = 406;
    const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
    const HTTP_REQUEST_TIMEOUT = 408;
    const HTTP_CONFLICT = 409;
    const HTTP_GONE = 410;
    const HTTP_LENGTH_REQUIRED = 411;
    const HTTP_PRECONDITION_FAILED = 412;
    const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
    const HTTP_REQUEST_URI_TOO_LONG = 414;
    const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const HTTP_EXPECTATION_FAILED = 417;
    const HTTP_I_AM_A_TEAPOT = 418;                                               // RFC2324
    const HTTP_MISDIRECTED_REQUEST = 421;                                         // RFC7540
    const HTTP_UNPROCESSABLE_ENTITY = 422;                                        // RFC4918
    const HTTP_LOCKED = 423;                                                      // RFC4918
    const HTTP_FAILED_DEPENDENCY = 424;                                           // RFC4918
    const HTTP_RESERVED_FOR_WEBDAV_ADVANCED_COLLECTIONS_EXPIRED_PROPOSAL = 425;   // RFC2817
    const HTTP_UPGRADE_REQUIRED = 426;                                            // RFC2817
    const HTTP_PRECONDITION_REQUIRED = 428;                                       // RFC6585
    const HTTP_TOO_MANY_REQUESTS = 429;                                           // RFC6585
    const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;                             // RFC6585
    const HTTP_UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    const HTTP_INTERNAL_SERVER_ERROR = 500;
    const HTTP_NOT_IMPLEMENTED = 501;
    const HTTP_BAD_GATEWAY = 502;
    const HTTP_SERVICE_UNAVAILABLE = 503;
    const HTTP_GATEWAY_TIMEOUT = 504;
    const HTTP_VERSION_NOT_SUPPORTED = 505;
    const HTTP_VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506;                        // RFC2295
    const HTTP_INSUFFICIENT_STORAGE = 507;                                        // RFC4918
    const HTTP_LOOP_DETECTED = 508;                                               // RFC5842
    const HTTP_NOT_EXTENDED = 510;                                                // RFC2774
    const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;                             // RFC6585

    /**
     * response data of tye
     */
    const RESPONSE_TYPE_ARRAY = 0;
    const RESPONSE_TYPE_JSON = 1;
    const RESPONSE_TYPE_BASE64 = 2;


    public $headers;

    /**
     * @var string|array
     */
    protected $content;

    /**
     * @var string
     */
    protected $version = "1.1";

    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @var string
     */
    protected $statusText;

    public static $statusTexts = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',            // RFC2518
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',          // RFC4918
        208 => 'Already Reported',      // RFC5842
        226 => 'IM Used',               // RFC3229
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',    // RFC7238
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',                                               // RFC2324
        421 => 'Misdirected Request',                                         // RFC7540
        422 => 'Unprocessable Entity',                                        // RFC4918
        423 => 'Locked',                                                      // RFC4918
        424 => 'Failed Dependency',                                           // RFC4918
        425 => 'Reserved for WebDAV advanced collections expired proposal',   // RFC2817
        426 => 'Upgrade Required',                                            // RFC2817
        428 => 'Precondition Required',                                       // RFC6585
        429 => 'Too Many Requests',                                           // RFC6585
        431 => 'Request Header Fields Too Large',                             // RFC6585
        451 => 'Unavailable For Legal Reasons',                               // RFC7725
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)',                      // RFC2295
        507 => 'Insufficient Storage',                                        // RFC4918
        508 => 'Loop Detected',                                               // RFC5842
        510 => 'Not Extended',                                                // RFC2774
        511 => 'Network Authentication Required',                             // RFC6585
    );



    public function __construct()
    {
        parent::__construct();
    }

    /**
     * The first run the middleware.
     */
    public function middleware()
    {
        // TODO: Implement middleware() method.
    }

    /**
     * instance register.
     */
    public function register()
    {
        // TODO: Implement register() method.

        $this->headers = new ResponseHeaderHandler([]);
    }

    public function __toString():string
    {
        return
            sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText)."\r\n".
            $this->headers."\r\n".
            $this->getContent();
    }

    /**
     * @param string $key
     * @param string $val
     * @return $this
     */
    public function setHeader(string $key = '',string $val = '')
    {
        if (!empty($key) && !empty($val)){
            $this->getHeaders()->set($key,$val);
        }
        return $this;
    }

    /**
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers = []){
        if (empty($headers)){
            $this->getHeaders()->setAll($headers);
        }
        return $this;
    }

    /**
     * @param Cookie $cookie
     * @return $this
     */
    public function setCookie(Cookie $cookie){
        $this->getHeaders()->setCookies($cookie);
        return $this;
    }

    /**
     * @param $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @param int $code
     * @return $this
     */
    public function setStatusCode(int $code)
    {
        $this->statusCode = $code;
        if ($this->isInvalid()) {
            throw new \InvalidArgumentException(sprintf('The HTTP status code "%s" is not valid.', $code));
        }

        $this->statusText = isset(self::$statusTexts[$code]) ? self::$statusTexts[$code] : 'unknown status';

        return $this;
    }

    public function send($content, $status = 200,int $responseType = 1,string $content_type = "application/json"){

        $this->setHeader(ResponseHeaderHandler::Headers_Key_ContentType, 'Content-type: ' . $content_type . '; charset=utf-8');
        $this->setHeader(base64_decode("QXV0aG9y"), base64_decode("YnkgQWxhbg=="));
        $this->setHeader(base64_decode("WC1Qb3dlcmVkLUJ5"), base64_decode("c2ltcGxl")." ".conf("app","versionName"));

        $this->setContent($content);
        $this->setStatusCode($status);

        $this->sendHeaders();
        $this->sendContent($responseType);

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } elseif ('cli' !== PHP_SAPI) {
            static::closeOutputBuffers(0, true);
        }

        exit(0);
    }

    /**
     * @return $this
     */
    protected function sendHeaders()
    {
        // headers have already been sent by the developer
        if (headers_sent()) {
            return $this;
        }

        if (!$this->getHeaders()->has(HeaderHandler::Headers_Key_Date)) {
            $this->setDate(\DateTime::createFromFormat('U', time()));
        }

        /**
         * output all headers.
         */
        foreach ($this->getHeaders()->all() as $name => $value) {
            header($name.': '.$value, false, $this->statusCode);
        }

        /**
         * output http status.
         */
        header(sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText), true, $this->statusCode);

        /**
         * output http cookies.
         */
        foreach ($this->getHeaders()->getCookies() as $cookie) {
            setcookie($cookie->getKey(), $cookie->getValue(), $cookie->getExpire(), $cookie->getPath(), $cookie->getDomain());
        }

    }


    /**
     * @param int $responseType
     */
    protected function sendContent(int $responseType)
    {
        switch ($responseType){
            case self::RESPONSE_TYPE_ARRAY:
                print_r($this->getContent());
                break;
            case self::RESPONSE_TYPE_BASE64:
                echo base64_encode(json_encode($this->getContent()));
                break;
            case self::RESPONSE_TYPE_JSON:
                echo json_encode ( $this->getContent() );
                break;
            default:
                echo json_encode ( $this->getContent() );
                break;
        }

    }


    /**
     * @return string|array
     */
    public function getContent()
    {
        return $this->content;
    }

    public function isInvalid()
    {
        return $this->statusCode < 100 || $this->statusCode >= 600;
    }

    /**
     * Is response informative?
     *
     * @return bool
     */
    public function isInformational()
    {
        return $this->statusCode >= 100 && $this->statusCode < 200;
    }

    /**
     * Is response successful?
     *
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * Is the response a redirect?
     *
     * @return bool
     */
    public function isRedirection()
    {
        return $this->statusCode >= 300 && $this->statusCode < 400;
    }

    /**
     * Is there a client error?
     *
     * @return bool
     */
    public function isClientError()
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    /**
     * Was there a server side error?
     *
     * @return bool
     */
    public function isServerError()
    {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }

    /**
     * Is the response OK?
     *
     * @return bool
     */
    public function isOk()
    {
        return 200 === $this->statusCode;
    }

    /**
     * Is the response forbidden?
     *
     * @return bool
     */
    public function isForbidden()
    {
        return 403 === $this->statusCode;
    }

    /**
     * Is the response a not found error?
     *
     * @return bool
     */
    public function isNotFound()
    {
        return 404 === $this->statusCode;
    }

    /**
     * Is the response a redirect of some form?
     *
     * @param string $location
     *
     * @return bool
     */
    public function isRedirect($location = null)
    {
        return in_array($this->statusCode, array(201, 301, 302, 303, 307, 308)) && (null === $location ?: $location == $this->getHeaders()->get('Location'));
    }

    /**
     * Is the response empty?
     *
     * @return bool
     */
    public function isEmpty()
    {
        return in_array($this->statusCode, array(204, 304));
    }

    /**
     * Sets the Date header.
     *
     * @param \DateTime $date A \DateTime instance
     *
     * @return $this
     */
    public function setDate(\DateTime $date)
    {
        $date->setTimezone(new \DateTimeZone('UTC'));
        $this->getHeaders()->set(HeaderHandler::Headers_Key_Date, $date->format('D, d M Y H:i:s').' GMT');
        return $this;
    }

    public static function closeOutputBuffers($targetLevel, $flush)
    {
        $status = ob_get_status(true);
        $level = count($status);
        // PHP_OUTPUT_HANDLER_* are not defined on HHVM 3.3
        $flags = defined('PHP_OUTPUT_HANDLER_REMOVABLE') ? PHP_OUTPUT_HANDLER_REMOVABLE | ($flush ? PHP_OUTPUT_HANDLER_FLUSHABLE : PHP_OUTPUT_HANDLER_CLEANABLE) : -1;

        while ($level-- > $targetLevel && ($s = $status[$level]) && (!isset($s['del']) ? !isset($s['flags']) || $flags === ($s['flags'] & $flags) : $s['del'])) {
            if ($flush) {
                ob_end_flush();
            } else {
                ob_end_clean();
            }
        }
    }

    /**
     * @return ResponseHeaderHandler
     */
    protected function getHeaders():ResponseHeaderHandler
    {
        return $this->headers;
    }
}