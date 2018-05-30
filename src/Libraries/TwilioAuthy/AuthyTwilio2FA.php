<?php


namespace Tyondo\Sms\Libraries\TwilioAuthy;

use Zttp\Zttp;

class AuthyTwilio2FA
{
    public $api_key, $api_url;


    public function __construct($api_key, $api_url = "https://api.authy.com/protected/json")
    {
        $this->api_key = $api_key;
        $this->api_url = $api_url;
    }

    private function geturl($url){
        return $this->api_url.$url;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function startVerification(array $data){
       $response = Zttp::post($this->geturl('/phones/verification/start'),[
           'api_key'=>$this->api_key,
           'via'=>'sms',
           'code_length' => 6,
           'locale' => 'en',
           'phone_number'=>$data['mobileNumber'],
           'country_code'=>$data['countryCode'],
       ]);
       return $response->json();
    }

    public function confirmVerificationCode(array $data){
        $response = Zttp::get($this->geturl('/phones/verification/check'),[
            'api_key'=>$this->api_key,
            'country_code'=>$data['countryCode'],
            'phone_number'=>$data['mobileNumber'],
            'verification_code'=>$data['verificationCode'],

        ]);
        return $response->json();
    }



    private function makeCall(array $data){
        $params = array(
            'api_key'=>$this->api_key,
            'via'=>'sms',
            'code_length' => 6,
            'locale' => 'en',
            'phone_number'=>$data['phoneNumber'],
            'country_code'=>$data['countryCode'],
        );
        $defaults = array(
            CURLOPT_URL => "https://api.authy.com/protected/json/phones/verification/start",
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $params,
        );
        $ch = curl_init();
        curl_setopt_array($ch, $defaults);
        $output = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            echo "cURL Error #:" . $err; //build a function that notifies the admin of the failure
        } //else {
            $obj = json_decode($output, true);
            return $obj;
        //}
    }
}