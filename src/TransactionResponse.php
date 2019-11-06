<?php

namespace ApcoPay;

class TransactionResponse
{
    public $result;
    public $psp_id;
    public $bank_transaction_id;
    public $date; // Format: yyyyMMdd
    public $time; // Format: HHmmss
    public $acquirer_reference;
    public $authorization_code;
    public $address_verification_response;
    public $acquirer_code;
    public $user_ip;
    public $user_defined_function;
    public $extra_data;
    public $card_country;
}
