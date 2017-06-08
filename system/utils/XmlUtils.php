<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/23 0023
 * Time: 16:51
 */

namespace App\System\Utils;


use App\System\Basic\CompactUtils;
use App\System\Data\Xml;

class XmlUtils extends CompactUtils
{

    public function __construct()
    {
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.

    }

    /**
     * @param string $fileName
     */
    public function parseXMLLoad(string $fileName){
        $dom = new \DOMDocument();
        $dom->load($fileName);
        $this->toArray($dom->documentElement);
    }

    /**
     * @param string $xmlFile
     * @return array
     */
    public function parseXML(string $xmlFile){
        $dom = new \DOMDocument();
        $dom->loadXML($xmlFile);
        return $this->toArray($dom->documentElement);
    }

    /**
     * @param $node
     * @return array
     */
    public function toArray($node){
        $data = [];

        if ($node->hasAttributes()){
            foreach ($node->attributes as $attr){
                $data[$attr->nodeName] = $attr->nodeValue;
            }
        }
        if ($node->hasChildNodes()){
            if ($node->childNodes->length == 1){
                $data[$node->firstChild->nodeName] = $this->toArray($node->firstChild);

            }else{
                foreach ($node->childNodes as $childNode){
                    if ($childNode->nodeType != XML_TEXT_NODE){
                        $data[$childNode->nodeName][] = $this->toArray($childNode);
                    }
                }
            }
        }else{
            if (!empty($node->nodeValue)){
                $data['value'] =  $node->nodeValue;
            }
        }

        return $data;
    }




}