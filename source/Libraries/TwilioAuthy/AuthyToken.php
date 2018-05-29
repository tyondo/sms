<?php

namespace Tyondo\Sms\Libraries\TwilioAuthy;

class AuthyToken extends AuthyResponse
{

    /**
     * Check if the response was ok
     *
     * @return boolean return true if the response code is 200
     */
    public function ok()
    {
        if( parent::ok() ){
            return $this->bodyvar('token') == 'is valid';
        }
        return false;
    }
}
