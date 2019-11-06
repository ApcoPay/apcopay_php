<?php

namespace ApcoPay;

class Configuration
{
    public $merchant_id;
    public $merchant_password;
    public $hashing_secret;
    public $notification_url;
    public $redirection_url;

    public function __construct(
        $merchant_id,
        $merchant_password,
        $hashing_secret,
        $notification_url,
        $redirection_url
    ) {
        $this->merchant_id = $merchant_id;
        $this->merchant_password = $merchant_password;
        $this->hashing_secret = $hashing_secret;
        $this->notification_url = $notification_url;
        $this->redirection_url = $redirection_url;
    }
}
