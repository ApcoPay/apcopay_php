<?php

namespace ApcoPay;

class TransactionRequest
{
    public $amount;
    public $currency_code; // ISO 4217 numeric code
    public $transaction_type;
    public $order_reference; // Must not contain ||

    public $card_number;
    public $card_cvv;
    public $card_holder;
    public $card_expiry_month;
    public $card_expiry_year;

    public $user_ip;

    public $original_transaction_id;
    public $user_defined_function; // Must not contain ||
}
