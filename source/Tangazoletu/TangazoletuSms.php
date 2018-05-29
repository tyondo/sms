<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 4/11/18
 * Time: 12:43 AM
 */

namespace Rndwiga\Mifos\Helpers;


use Illuminate\Support\Facades\Mail;
use Rndwiga\Mifos\ModuleHelper;

class TangazoletuSms
{

    private $userId = 15463;
    private $passKey = 'D98p7MxOka';
    private $service = 1;
    private $sender = 'MUSONIKENYA';

    public function withUserId($userId){
        $this->userId = $userId;
        return $this;
    }

    public function withPassKey($passKey){
        $this->passKey = $passKey;
        return $this;
    }

    public function withService($serviceId){
        $this->service = $serviceId;
        return $this;
    }

    public function withSender($senderName){
        $this->sender = $senderName;
        return $this;
    }


    public function sendSmsNotification($destination,$message){

       //TODO:: make sure the number begins with 254

        //foreach ([254712550547] as $destination){

            //MifosUtility::logInfo($destination,'sms_number');

            $fullUrl = "http://api.prsp.tangazoletu.com/?User_ID=$this->userId&passkey=$this->passKey&".
                "service=$this->service&sender=$this->sender&dest=$destination&msg=";
          $response =  $this->curlGetRequest($fullUrl,$message);


       // }
    }

    public function handleSmsNotificationResponse($responseJson){
        $responseData = json_decode($responseJson,true);

        if (stripos(strtolower($responseData['data']), 'Successful') !== false) {
            MifosUtility::logInfo([
                'status' => 'success',
                'payload' => $responseData
            ],'sms_send_response');
        }else{
            MifosUtility::logInfo([
                'status' => 'fail',
                'payload' => var_dump($responseJson),
                'data' => $responseJson,
            ],'sms_send_response');

            $content = [
                'message' => 'Unable to send SMS Notification',
                'payload' => $responseData,
            ];
            ModuleHelper::sendAdminEmail(null,'MMT SMS Notification Failure',$content,'MMT Application');
        }
    }


    private function curlGetRequest($url,$message){

        $curl = curl_init();
        $requestUrl = $url. curl_escape($curl,$message);

        curl_setopt_array($curl, array(
            CURLOPT_URL => $requestUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Cache-Control: no-cache",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            //MifosUtility::logInfo($err,'sms_notification_error');

            $this->handleSmsNotificationResponse(json_encode([
                'status' => 'fail',
                'data' => $err
            ]));

            return "cURL Error #:" . $err;

        } else {

            //MifosUtility::logInfo($response,'sms_notification_response');

            $this->handleSmsNotificationResponse(json_encode([
                'status' => 'success',
                'data' => $response
            ]));
            return $response;
        }
    }

}