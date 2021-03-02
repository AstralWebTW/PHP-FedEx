<?php
namespace AstralWeb\LibFedex;

class Example
{
    /**
     * @param string $trackingNumber
     * @param array $config
     * @return bool
     * @param throws \Exception
     */
    static public function isTrackingNumberDeilvered(
        string $trackingNumber,
        $config = [
            'key' => '',
            'password' => '',
            'accountNumber' => '',
            'meterNumber' => ''
        ])
    {
        $client = new \AstralWeb\LibFedex\Web\Service\Soap\Client(
            $config['key'],
            $config['password'],
            $config['accountNumber'],
            $config['meterNumber']
        );

        $request = new \AstralWeb\LibFedex\Web\Service\Request\Track();
        $request->setClient($client);
        $request->setTrackingNumber($trackingNumber);

        if ( ! $request->execute()) {

            throw new \Exception($request->getRequestError());
        }

        $response = $request->getFormattedResponse();
        if ($response->getException()) {

            throw new \Exception($response->getException()->getMessage());
        }

        // for version v19
        $getRemoteQueryError = function($response) {

            $content = $response->getRequestResponseArray();
            // The judgement could be different in version, use $content to investigate
            if ( ! isset($content['HighestSeverity'])
                or $content['HighestSeverity'] == 'SUCCESS') {

                return '';
            }

            if ( ! isset($content['Notifications'])) {

                return '';
            }

            $notifications = $content['Notifications'];
            if (isset($notifications['Message'])) {

                return $notifications['Message'];
            }

            return '';
        };
        $remoteError = $getRemoteQueryError($response);
        if ($remoteError) {

            throw new \Exception($remoteError);
        }

        return $response->isDelivered();
    }
}
