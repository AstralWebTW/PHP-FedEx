<?php

namespace AstralWeb\LibFedex\Web\Service\Request;

class Track extends \AstralWeb\LibFedex\Web\Service\Soap\Request
{
    const ENDPOINT = 'track';

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
            'TransactionDetail' => [
                'CustomerTransactionId' => '*** Track Request using PHP ***',
            ],
            'Version' => [
                'ServiceId' => 'trck',
                'Major' => 19,
                'Intermediate' => 0,
                'Minor' => 0
            ],
            'ProcessingOptions' => 'INCLUDE_DETAILED_SCANS',
            'SelectionDetails' => [
                'PackageIdentifier' => [
                    'Type' => 'TRACKING_NUMBER_OR_DOORTAG',
                    'Value' => ''
                ]
            ]
        ]
    ];

    public function __construct(
        string $env = self::ENV_PRODUCTION)
    {
        parent::__construct($env);

        $this->_defaultResponse = new \AstralWeb\LibFedex\Web\Service\Response\Track();
    }

    /**
     * @param string $env
     * @return string
     */
    protected function _getWsdlByEnv(string $env)
    {
        return dirname(__FILE__) . '/../../../_wsdl/TrackService_v19.wsdl';
    }

    /**
     * @param string $number
     */
    public function setTrackingNumber($number)
    {
        return $this->appendRequestParam(
            ['SelectionDetails', 'PackageIdentifier', 'Value'],
            $number
        );
    }
}
