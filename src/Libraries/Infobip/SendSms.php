<?php

namespace Tyondo\Sms\Libraries\Infobip;


use Tyondo\Sms\Helpers\SmsUtility;

class SendSms extends ApiConnection
{

    private $app2FaId;
    private $message2FaId;
    private $countryCode;

    public function __construct()
    {
        parent::__construct();
        $this->setApp2FaId($this->getApp2FaId());
        $this->setMessage2FaId($this->get2FaMessageId());
        $this->setCountryCode($this->getCountryCode());
    }

    private function formatReceipientNumber($to){
        //$match =preg_match("/^".$this->countryCode."254+(?!$)/", $to);
        $match =preg_match("/^".$this->countryCode."+(?!$)/", $to);
        if ($match == true){
            return $to;
        }else{
            $mssdn = preg_replace("/^0+(?!$)/", "", $to);
            $countryCode = $this->countryCode;
            return $countryCode.$mssdn;
        }
    }
    public function setCountryCode($countryCode){
        $this->countryCode = $countryCode;
        return $this;
    }

    private function getCountryCode(){
        return env('SMS_INFOBIP_2FA_APP_ID') ? env('SMS_INFOBIP_2FA_APP_ID') : null;
    }

    public function setApp2FaId($app2FaId){
        $this->app2FaId = $app2FaId;
        return $this;
    }

    private function getApp2FaId(){
        return env('SMS_INFOBIP_2FA_APP_ID') ? env('SMS_INFOBIP_2FA_APP_ID') : null;
    }

    public function setMessage2FaId($messageId){
        $this->message2FaId = $messageId;
        return $this;
    }
    private function get2FaMessageId(){
        return env('SMS_INFOBIP_2FA_MESSAGE_ID') ? env('SMS_INFOBIP_2FA_MESSAGE_ID') : null;

    }
    /**
     * @param $to
     * @param $message
     * @return mixed
     */

    public function sendBulkTextSms($to, $textMessage)
    {
        $this->message = [];
        $this->message['from'] = $this->from;
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
            $this->message['from'] = $this->from;
            $this->message['to'] = self::formatReceipientNumber($to);
            $this->message['text'] = $textMessage;
        $response = self::postRequest($this->message,'/sms/1/text/single',true);

        SmsUtility::logInfo($response,'infobip_sms_feedback','InfoBip');

        return $response;
    }

    public function request2FaSms($to){
        $this->message = [];
        $this->message['from'] = $this->from;
        $this->message['to'] = self::formatReceipientNumber($to);

        //TODO:: Get these values dynamically from the db

        $this->message['applicationId'] = $this->app2FaId;
        $this->message['messageId'] = $this->message2FaId;
        $this->message['ncNeeded'] = true;
        $response = self::postRequest($this->message,'/2fa/1/pin',false);
        SmsUtility::logInfo($response,'infobip_sms_feedback','InfoBip');

        //TODO::implement caching of the result to facilitate the validation

        /**
         * {
        "pinId": "65C9DD10532896B55255EC8D267E54C1",
        "to": "2547XXXXXXX",
        "ncStatus": "NC_DESTINATION_REACHABLE",
        "smsStatus": "MESSAGE_SENT"
        }
         */
        return $response;
    }


    public function verify2FASmsCode($pinId,$otpPin){
        $this->message = [];
        $this->message['pin'] = $otpPin;

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
    }
    /**
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
