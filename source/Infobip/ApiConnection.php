<?php
/**
 * Created by PhpStorm.
 * User: raphael
 * Date: 1/26/18
 * Time: 10:22 PM
 */

namespace Rndwiga\Mifos\Helpers\Infobip;


//use Tyondo\Cirembo\Modules\Setting\Facades\Settings;

class ApiConnection
{
    private static $baseUrl;
    private static $username;
    private static $password;

    public static $from;
    public $message;
    public $text;
    public $to;

    public function __construct()
    {
        self::$baseUrl = $this->getBaseUrl();
        self::$username = $this->getUserName();
        self::$password = $this->getUserPassword();
        self::$from = $this->getSenderId();
    }

    private function getBaseUrl(){
        $baseUrl = env('INFOBIP_BASE_URL') ? env('INFOBIP_BASE_URL') : 'https://api.infobip.com';
        //return  Settings::get('infobip_base_url');
        return $baseUrl;
    }
    private function getUserName(){
        $userName = env('INFOBIP_USERNAME') ? env('INFOBIP_USERNAME') : null;
        //return  Settings::get('infobip_username');
        return $userName;
    }
    private function getUserPassword(){
        $userPassword = env('INFOBIP_PASSWORD') ? env('INFOBIP_PASSWORD') : null;
        return $userPassword;
       //return  Settings::get('infobip_password');
    }
    private function getSenderId(){
        $senderId = env('INFOBIP_FROM') ? env('INFOBIP_FROM') : null;
        return $senderId;
       // return  Settings::get('infobip_sender_id');
    }
    private static function getAppKey(){
        $apiKey = env('INFOBIP_API_KEY') ? env('INFOBIP_API_KEY') : null;
        return $apiKey;
        //return  Settings::get('infobip_api_key');
    }

    /**
     * Generate the base64 encoded authorization key.
     *
     * @return string
     */
    private static function generateBasicAuthorizationCredentials()
    {
        return base64_encode(self::$username.':'. self::$password);
    }

    public static function getApiKey(){

        $url = self::$baseUrl."/2fa/1/api-key";

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

    public static function postRequest($data, $urlSegment=null, $authType = null){
        $url = self::$baseUrl.$urlSegment;
        if ($authType){
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