<?php

namespace AstralWeb\LibFedex\Web\Service\Soap;

class Response
{
    protected $_requestResponseArray = null;
    protected $_content = [];


    public function __construct(
        array $content = [])
    {
        $this->setContent($content);
    }

    /**
     * @param array $arr
     * @return array
     */
    protected function _arrayToArray(array $arr)
    {
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $arr[$k] = $this->_arrayToArray($v);
                continue;
            }

            if (is_object($v)) {
                $arr[$k] = $this->_stdClassToArray($v);
                continue;
            }
        }

        return $arr;
    }

    /**
     * @param \stdClass $stdClass
     * @return array
     */
    protected function _stdClassToArray(\stdClass $stdClass)
    {
        $arr = (array)$stdClass;
        foreach ($arr as $k => $v) {

            if (is_object($v)) {
                $arr[$k] = $this->_stdClassToArray($v);
                continue;
            }
            if(is_array($v)) {
                $arr[$k] = $this->_arrayToArray($v);
                continue;
            }
        }

        return $arr;
    }

    /**
     * @param array $content
     * @return self
     */
    public function setContent(array $content)
    {
        $this->_content = $content;
        $this->_requestResponseArray = null;

        return $this;
    }

    /**
     * @return null | int
     */
    public function getHttpStatusCode()
    {
        if ( ! $this->getHeadersString()) {

            return null;
        }

        preg_match("/HTTP\/\d\.\d\s*\K[\d]+/", $this->getHeadersString(), $matches);

        return isset($matches[0]) ? (int)$matches[0] : null;
    }

    /**
     * @return null | string
     */
    public function getHeadersString()
    {
        if (isset($this->_content['headers_string'])) {

            return $this->_content['headers_string'];
        }

        return null;
    }

    /**
     * Get content of raw response from soap server
     *
     * @return null | \stdClass
     */
    public function getRequestResponse()
    {
        if (isset($this->_content['response'])) {

            return $this->_content['response'];
        }

        return null;
    }

    /**
     * Convert content of raw response to array
     *
     * @return array
     */
    public function getRequestResponseArray()
    {
        if ( ! is_null($this->_requestResponseArray)) {

            return $this->_requestResponseArray;
        }

        $res = $this->getRequestResponse();
        if ( ! $res) {

            $this->_requestResponseArray = [];

            return $this->_requestResponseArray;
        }

        $this->_requestResponseArray = $this->_stdClassToArray($res);

        return $this->_requestResponseArray;
    }

    /**
     * @return null | \Exception
     */
    public function getException()
    {
        if (isset($this->_content['exception'])) {

            return $this->_content['exception'];
        }

        return null;
    }

    /**
     * @return null | string
     */
    public function getSoapFaultMessage()
    {
        $ex = $this->getException();
        if ( ! $ex) {

            return null;
        }

        if ( ! $ex instanceof \SoapFault) {

            return null;
        }

        return $ex->getMessage();
    }
}
