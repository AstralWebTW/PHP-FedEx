<?php

namespace AstralWeb\LibFedex\Web\Service\Response;

class Track extends \AstralWeb\LibFedex\Web\Service\Soap\Response
{
    /**
     * @return array
     */
    protected function listTrackDetails()
    {
        $content = $this->getRequestResponseArray();
        if ( ! isset($content['CompletedTrackDetails']['TrackDetails'])) {

            return [];
        } 

        return $content['CompletedTrackDetails']['TrackDetails'];
    }

    /**
     * @param array $detailRow
     * @return bool
     */
    protected function _isDetailDelivered(array $detailRow)
    {
        if (isset($detailRow['StatusDetail']['Code'])
            and $detailRow['StatusDetail']['Code'] == 'DL') {

            return true;
        }

        if (isset($detailRow['DatesOrTimes'])) {
            $timeRecords = $detailRow['DatesOrTimes'];
            foreach ($timeRecords as $record) {
                if ($record['Type'] == 'ACTUAL_DELIVERY') {

                    return true;
                }
            }
        }

        if (isset($detailRow['Events'])) {
            $events = $detailRow['Events'];
            foreach ($events as $event) {
                if ($event['EventType'] == 'DL') {

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isDelivered()
    {
        $trackDetails = $this->listTrackDetails();
        if (empty($trackDetails)) {

            return false;
        }

        if (isset($trackDetails['TrackingNumber'])) {

            return $this->_isDetailDelivered($trackDetails);
        }

        foreach ($trackDetails as $detailRow) {
            if ($this->_isDetailDelivered($detailRow)) {

                return true;
            }
        }

        return false;
    }
}
