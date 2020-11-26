<?php

namespace AstralWeb\LibFedex\Web\Service\Soap;

abstract class Request
{
    const ENV_PRODUCTION = 'prouduction';
    const ENV_STAGING = 'testing';

    const ENDPOINT = '';

    protected static $_defaultOptions = [
        'trace' => true,
        'exception' => true
    ];

    protected $_soapClient;


    protected $_requestParams = [
        self::ENDPOINT => [
            'WebAuthenticationDetail' => [
                'UserCredential' => [
                    'Key' => '',
                    'Password' => ''
                ]
            ],
            'ClientDetail' => [
                'AccountNumber' => '',
                'MeterNumber' => ''
            ],
        ]
    ];

    /**
     * @var \AstralWeb\LibFedex\Web\Service\Soap\Client
     */
    protected $_serviceClient;

    /**
     * @param \AstralWeb\LibFedex\Web\Service\Soap\Response
     */
    protected $_response;

    /**
     * @param \AstralWeb\LibFedex\Web\Service\Soap\Response
     */
    protected $_defaultResponse = null;

    /**
     * @var []
     */
    protected $_responseContentArray = [];

    /**
     * @param string
     */
    protected $_requestError = '';


    public function __construct(
        string $env = self::ENV_PRODUCTION)
    {
        $this->_soapClient = new \SoapClient(
            $this->_getWsdlByEnv($env),
            static::$_defaultOptions
        );
    }

    /**
     * @param string $env
     * @return string
     */
    protected function _getWsdlByEnv(string $env)
    {
        return '';

        switch ($env) {
            case static::ENV_PRODUCTION:

                return sprintf("https://swsim.stamps.com/swsim/swsimv%s.asmx?wsdl", $version);
                break;
            case static::ENV_STAGING:
            default:

                return sprintf("https://swsim.testing.stamps.com/swsim/swsimv%s.asmx?wsdl", $version);
                break;
        }
    }

    /**
     * @param \AstralWeb\LibFedex\Web\Service\Soap\Client $client
     * @return self
     */
    public function setClient(
        \AstralWeb\LibFedex\Web\Service\Soap\Client $client)
    {
        $this->_serviceClient = $client;

        $this->appendRequestParam(
            ['WebAuthenticationDetail', 'UserCredential', 'Key'],
            $client->getCredentialKey()
        );
        $this->appendRequestParam(
            ['WebAuthenticationDetail', 'UserCredential', 'Password'],
            $client->getCredentialPassword()
        );
        $this->appendRequestParam(
            ['ClientDetail', 'AccountNumber'],
            $client->getAccountNumber()
        );
        $this->appendRequestParam(
            ['ClientDetail', 'MeterNumber'],
            $client->getMeterNumber()
        );

        return $this;
    }

    /**
     * @return \AstralWeb\LibFedex\Web\Service\Soap\Client
     */
    public function getClient()
    {
        return $this->_serviceClient;
    }

    /**
     * @return array
     */
    public function getResponseContentArray()
    {
        return $this->_responseContentArray;
    }

    /**
     * @return AstralWeb\LibFedex\Web\Service\Soap\Response | null
     */
    public function getFormattedResponse()
    {
        return $this->_response;
    }

    /**
     * @return string
     */
    public function getExecuteError()
    {
        return $this->_requestError;
    }

    /**
     * @param array $keyPath
     * @param scalar $value
     * @return string
     */
    public function appendRequestParam(array $keyPath, $value)
    {
        $endKeyIndex = count($keyPath) - 1;

        $updateArray = &$this->_requestParams[static::ENDPOINT];        
        foreach ($keyPath as $no => $key) {
            if ( ! isset($updateArray[$key])) {
                $updateArray[$key] = [];
            }
            if ($no == $endKeyIndex) {
                $updateArray[$key] = $value;
            } else {
                $updateArray = &$updateArray[$key];
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getRequestError()
    {
        return $this->_requestError;
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        if ( ! $this->_response) {

            return false;
        }

        return ( ! $this->_response->getException());
    }

    /**
     * @param string $endpoint
     * @param array $paramters
     * @param \AstralWeb\LibFedex\Web\Service\Soap\Response $formattedResponse
     * @return \AstralWeb\LibFedex\Web\Service\Soap\Response
     */
    protected function makeRequest(
        string $endpoint,
        array $parameters = [],
        $formattedResponse = null)
    {
        if (is_null($formattedResponse)) {
            if ($this->_defaultResponse) {
                $formattedResponse = $this->_defaultResponse;
            } else {
                $formattedResponse = new \AstralWeb\LibFedex\Web\Service\Soap\Response();
            }
        }

        $return = [
            'response' => null,
            'exception' => null,
            'headers_string' => null
        ];

        try {

            if ( ! $formattedResponse instanceof \AstralWeb\LibFedex\Web\Service\Soap\Response) {

                throw new \AstralWeb\LibFedex\Exception\ValidationError('Invalid response class!');
            }

            $return['response'] = $this->_soapClient->{$endpoint}($parameters);
            $return['headers_string'] = $this->_soapClient->__getLastResponseHeaders();

        } catch (\AstralWeb\LibFedex\Exception\ValidationError $ex) {

            $return['exception'] = $ex;

        } catch (\Exception $ex) {

            $return['headers_string'] = $this->_soapClient->__getLastResponseHeaders();
            $return['exception'] = $ex;
        }

        $formattedResponse->setContent($return);

        return $formattedResponse;
    }

    /**
     * @param \AstralWeb\LibFedex\Web\Service\Soap\Response | null $formattedResponse
     * @return bool
     */
    public function execute($formattedResponse = null)
    {
        $client = $this->getClient();
        if ( ! $client) {

            $this->_response = null;
            $this->_requestError = 'Invalid client!';

            return false;
        }

        $response = $this->makeRequest(
            static::ENDPOINT,
            $this->_requestParams[static::ENDPOINT],
            $formattedResponse
        );
        $this->_response = $response;

        if ( ! $this->isSuccess()) {

            $this->_requestError = $this->_response->getException()->getMessage();

            return false;
        }

        $this->_responseContentArray = $this->_response->getRequestResponseArray();
        $this->_requestError = '';

        return true;
    }
}
