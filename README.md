# ApcoPay PHP library

The ApcoPay PHP library provides the functionality to process financial transactions with the [ApcoPay](https://www.apcopay.eu/) gateway.

## Installation

The recommended method of installation is via [Packagist](https://packagist.org/) and [Composer](https://getcomposer.org/).
Run the following command to install the package and add it as a requirement to your project's `composer.json`:

```bash
composer require apcopay/apcopay_php
```

## Dependencies

The PHP extension cURL is required.

## Description

The ApcoPayGateway configuration requires 5 parameters.

* Merchant id - The merchant id/code with ApcoPay
* Merchant password - The merchant password with ApcoPay
* Hashing secret - The hashing secret word with ApcoPay
* Notification url - The url that transaction status notifications are sent to. Should be processed as shown in the [notification request example](#Notification-request) below.
* Redirection url - The url the user is redirected to after a 3DS payment. Should be processed as shown in the [redirect request example](#Redirect-request) below.

### Transaction types

|   Name                | Value |   Description                                                             |
|-----------------------|-------|---------------------------------------------------------------------------|
|   Purchase            |   1   |   Transfers value from the cardholders account                            |
|   VoidPurchase        |   3   |   Cancellation of a purchase transaction – Before end of day              |
|   Authorisation       |   4   |   Reserve the specified value from the card holder                        |
|   Capture             |   5   |   Transfers a reserved value from the cardholders account                 |
|   VoidCredit          |   6   |   Cancel a credit - Before end of day                                     |
|   VoidCapture         |   7   |   Cancel a capture - Before end of day                                    |
|   VoidAuthorisation   |   9   |   Cancel an authorization - Before end of day                             |
|   Verify              |   10  |   Verify a transaction’s status                                           |
|   RepeatPurchase      |   11  |   Repeats a purchase by submitting the original PSPID                     |
|   PartialRefund       |   12  |   Reverse a partial/full amount of the original transaction               |
|   OriginalCredit      |   13  |   Pay out an amount greater than the amount of the original transaction   |
|   RepeatAuthorisation |   14  |   Repeats an authorization by submitting the original PSPID               |

## Examples

### Initialise ApcoPayGateway

```php
$gateway = new ApcoPayGateway(
    new Configuration(
        "1234",
        "dfnu2345b2354vbu",
        "3ui423ui4",
        "https://merchanturl.com/apcopay/notification",
        "https://merchanturl.com/apcopay/redirect"
    )
);
```

### Process transaction

```php
$transactionRequest = new TransactionRequest();
$transactionRequest->amount = "2.40";
$transactionRequest->currency_code = "978";
$transactionRequest->order_reference = "1234";
$transactionRequest->action_type = TransactionType::Purchase;

$transactionRequest->card_number = "4444444444444444";
$transactionRequest->card_cvv = "123";
$transactionRequest->card_holder = "John Doe";
$transactionRequest->card_expiry_month = "12";
$transactionRequest->card_expiry_year = "2023";

$transactionResponse = $gateway->processTransaction($transactionRequest);

if ($transactionResponse->result === "CAPTURED" || $transactionResponse->result === "APPROVED" || $transactionResponse->result === "VOIDED") {
    // Transaction successful
} else if ($transactionResponse->result == 'ENROLLED') {
    $redirectUrl = "https://www.apsp.biz/pay/3DSFP2/verify.aspx?id=" . $transactionResponse->psp_id;
    // Redirect to $redirectUrl
} else {
    // Transaction failed
}
```

### Notification request

>**Note:** The notification should always return be HTTP status code 200 with content OK in the response.

```php
$request = $_POST["params"];
$request = urldecode($request);
if (!$gateway->verify($request)) {
    die("Hash mismatch");
    return;
}
$notificationRequest = $gateway->parseNotification($request);
if ($notificationRequest->result === "OK") {
    // TODO: update order to successful
} else {
    // TODO: update order to declined
}
echo 'OK';
header("HTTP/1.1 200 OK");
```

### Redirect request

```php
$request = $_GET["params"];
$request = str_replace("\\\"", "\"", $request);
if (!$gateway->verify($request)) {
    die("Hash mismatch");
    return;
}
$redirectRequest = $gateway->parseRedirect($request);
if ($redirectRequest->result === "OK") {
    echo '<div>Transaction successful</div>';
    echo '<div>Order reference: ' . $redirectRequest->order_reference . '</div>';
} else {
    echo '<div>Transaction failed</div>';
    echo '<div>Order reference: ' . $redirectRequest->order_reference . '</div>';
    echo '<div>Result: ' . $redirectRequest->result . '</div>';
}
```

## License

The ApcoPay PHP library is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
