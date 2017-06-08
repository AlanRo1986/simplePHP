<?php

namespace App\System\Utils;

class DecoderUtils
{
    /**
     * Decode JSON
     *
     * @access public
     * @return mixed
     * @internal param $json
     * @internal param $assoc
     * @internal param $depth
     * @internal param $options
     */
    public static function decodeJson()
    {
        $args = func_get_args();

        // Call json_decode() without the $options parameter in PHP
        // versions less than 5.4.0 as the $options parameter was added in
        // PHP version 5.4.0.
        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            $args = array_slice($args, 0, 3);
        }

        $response = call_user_func_array('json_decode', $args);
        if ($response === null) {
            $response = $args['0'];
        }
        return $response;
    }

    /**
     * Decode XML
     *
     * @access public
     * @param  $response
     * @return \SimpleXMLElement
     */
    public static function decodeXml($response)
    {
        $xml_obj = @simplexml_load_string($response);
        if (!($xml_obj === false)) {
            $response = $xml_obj;
        }
        return $response;
    }
}
