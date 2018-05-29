<?php

namespace Rndwiga\Mifos\Helpers\Infobip;

//use Tyondo\Cirembo\Modules\Setting\Facades\Settings;

use Rndwiga\Mifos\Helpers\MifosUtility;

class SendSms extends ApiConnection
{

    private function formatReceipientNumber($to){
        $match =preg_match("/^254+(?!$)/", $to); //TODO::make the country extension dynamic
        if ($match == true){
            return $to;
        }else{
            $mssdn = preg_replace("/^0+(?!$)/", "", $to);
            $countryCode = 254;
            return $countryCode.$mssdn;
        }
    }
    private function getApp2FaId(){
        $twoFaAppId = env('INFOBIP_2FA_APP_ID') ? env('INFOBIP_2FA_APP_ID') : null;
        return $twoFaAppId;
        //return  Settings::get('infobip_2fa_app_id');
    }
    private function get2FaMessageId(){
        $twoFaMessageId = env('INFOBIP_2FA_MESSAGE_ID') ? env('INFOBIP_2FA_MESSAGE_ID') : null;
        return $twoFaMessageId;
        //return  Settings::get('infobip_2fa_message_id');
    }
    /**
     * @param $to
     * @param $message
     * @return mixed
     */

    public function sendBulkTextSms($to, $textMessage)
    {
        $this->message = [];
        $this->message['from'] = self::$from;
        $this->message['to'] = $to;
        $this->message['text'] = $textMessage;
        $response = self::postRequest($this->message,'/sms/1/text/single',true);
        return $response;
    }
    /**
     * @return mixed
     */
    public function sendSingleTextSms($to, $textMessage)
    {
        $this->message = [];
            $this->message['from'] = self::$from;
            $this->message['to'] = $this->formatReceipientNumber($to);
            $this->message['text'] = $textMessage;
        $response = self::postRequest($this->message,'/sms/1/text/single',true);

        MifosUtility::logInfo($response,'infobip_sms_feedback','InfoBip');

        return $response;
    }

    public function request2FaSms($to){
        $this->message = [];
        $this->message['from'] = self::$from;
        $this->message['to'] = $this->formatReceipientNumber($to);
        //TODO:: Get these values dynamically from the db
        $this->message['applicationId'] = $this->getApp2FaId();
        $this->message['messageId'] = $this->get2FaMessageId();
        $this->message['ncNeeded'] = true;
        $response = self::postRequest($this->message,'/2fa/1/pin',true);
        //TODO::implement caching of the result to facilitate the validation
        /**
         * {
        "pinId": "65C9DD10532896B55255EC8D267E54C1",
        "to": "254712550547",
        "ncStatus": "NC_DESTINATION_REACHABLE",
        "smsStatus": "MESSAGE_SENT"
        }
         */
        return $response;
    }


    public function verify2FASmsCode($pinId,$otpPin){
        $this->message = [];
        $this->message['pin'] = $otpPin;
        // POST https://api.infobip.com/2fa/1/pin/{pinId}/verify
        $response = self::postRequest($this->message,"/2fa/1/pin/{$pinId}/verify",true);

        return $response;
    }

    /**
     * @return mixed
     */
    public function sendAdvancedTextSms()
    {
       //
    }
    /**
     * @return mixed
     */
    public function sendBulkMultimediaSms()
    {
        return $this->method;
    }
    /**
     * @return mixed
     */
    public function sendSingleMultimediaSms()
    {
        return $this->method;
    }
    /**
     * @return mixed
     */
    public function getSingleSmsDeliveryReport()
    {
        return $this->method;
    }    /**
 * @return mixed
 */
    public function getMultipleSmsDeliveryReport()
    {
        return $this->method;
    }
    /**
     * @return mixed
     */
    public function getSingleSmsLogReport()
    {
        return $this->method;
    }    /**
 * @return mixed
 */
    public function getMultipleSmsLogReport()
    {
        return $this->method;
    }

}
