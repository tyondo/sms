<?php
/**
 * Created by PhpStorm.
 * User: raphael
 * Date: 1/26/18
 * Time: 10:22 PM
 */

namespace Tyondo\Sms\Libraries\Infobip;

class ApiConnection
{
    private $baseUrl;
    private $username;
    private $password;
    private $appKey;

    public $from;
    public $message;
    public $text;
    public $to;

    public function __construct()
    {
       $this->setBaseUrl($this->getBaseUrl());
       $this->setUserName($this->getUserName());
       $this->setUserPassword($this->getUserPassword());
       $this->setSenderId($this->getSenderId());
    }
    
    public function setBaseUrl($baseUrl){
        $this->baseUrl = $baseUrl;
        return $this;
    }

    private function getBaseUrl(){
        $baseUrl = env('SMS_INFOBIP_BASE_URL') ? env('SMS_INFOBIP_BASE_URL') : 'https://api.infobip.com';
        return $baseUrl;
    }
    
    public function setUserName($userName){
        $this->username = $userName;
        return $this;
    }
    
    private function getUserName(){
        $userName = env('SMS_INFOBIP_USERNAME') ? env('SMS_INFOBIP_USERNAME') : null;
        return $userName;
    }
    
    public function setUserPassword($userPassword){
        $this->password = $userPassword;
        return $this;
    }
    
    private function getUserPassword(){
        $userPassword = env('SMS_INFOBIP_PASSWORD') ? env('SMS_INFOBIP_PASSWORD') : null;
        return $userPassword;
    }
    
    public function setSenderId($senderId){
        $this->from = $senderId;
        return $this;
    }
    
    private function getSenderId(){
        $senderId = env('SMS_INFOBIP_FROM') ? env('SMS_INFOBIP_FROM') : null;
        return $senderId;
    }
    
    public function setAppKey($appKey){
        $this->appKey = $appKey;
        return $this;
    }
    
    private function getAppKey(){
        $apiKey = env('SMS_INFOBIP_API_KEY') ? env('SMS_INFOBIP_API_KEY') : null;
        return $apiKey;
    }

    /**
     * Generate the base64 encoded authorization key.
     *
     * @return string
     */
    private function generateBasicAuthorizationCredentials()
    {
        return base64_encode($this->username.':'. $this->password);
    }

    public function getApiKey(){

        $url = $this->baseUrl."/2fa/1/api-key";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization' => 'Basic ' . self::generateBasicAuthorizationCredentials(),
            'content-type' => "application/json",
        ]);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        //curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $curl_response = curl_exec($curl);
        return $curl_response;
    }

    public function postRequest($data, $urlSegment=null, $authTypeApiKey = true){
        $url = $this->baseUrl.$urlSegment;
        if ($authTypeApiKey){
            $credentials = "App ". self::getAppKey();
        }else{
            $credentials = "Basic ".self::generateBasicAuthorizationCredentials();
        }
        $data_string = json_encode($data);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "{$url}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data_string,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => array(
                "Authorization: {$credentials}",
                "Content-Type: application/json",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }
}