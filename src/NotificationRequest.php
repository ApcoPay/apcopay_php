<?php

namespace ApcoPay;

class NotificationRequest
{
    public $order_reference;
    public $result;
    public $authorization_code;
    public $card_input;
    public $psp_id;
    public $status_3ds;
    public $currency_code; // ISO 4217 numeric code
    public $amount;
    public $iso_response;
    public $card_number;
    public $card_expiry;
    public $card_holder;
    public $acquirer_code;
    public $source;
    public $card_country;
    public $card_type;
}
