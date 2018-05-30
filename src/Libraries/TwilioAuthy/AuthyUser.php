<?php

namespace Tyondo\Sms\Libraries\TwilioAuthy;

class AuthyUser extends AuthyResponse
{
    /**
     * Constructor.
     *
     * @param array $raw_response Raw server response
     */
    public function __construct($raw_response)
    {
        $body = $raw_response->json(['object' => true]);

        if (isset($body->user)) {
            // response is {user: {id: id}}
            $raw_response->body = $body->user;
        }

        parent::__construct($raw_response);
    }
}
