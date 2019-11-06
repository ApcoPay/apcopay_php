<?php

namespace ApcoPay;

use ApcoPay\TransactionResponse;

class ApcoPayGateway
{
    public $configuration;

    public function __construct($configuration)
    {
        $this->configuration = $configuration;
    }

    private function getXmlTag($xmlStr, $tagName)
    {
        if (preg_match('/(<' . $tagName . '>)(.*)(<\/' . $tagName . '>)/', $xmlStr, $regexMatch)) {
            return $regexMatch[2];
        } else {
            return null;
        }
    }

    public function processTransaction($transaction)
    {
        $udf3 =
            "<WS>" .
            "<ORef>" . $transaction->order_reference . "</ORef>" .
            "<status_url urlEncode=\"true\">" . $this->configuration->notification_url . "</status_url>" .
            "<RedirectionURL>" . $this->configuration->redirection_url . "</RedirectionURL>" .
            "</WS>";

        if (strpos($udf3, '||') !== false) {
            throw new \InvalidArgumentException('The configuration->notification_url and transaction->order_reference cannot contain ||');
        }
        if (strpos($transaction->user_defined_function, '||') !== false) {
            throw new \InvalidArgumentException('The transaction->user_defined_function cannot contain ||');
        }

        $requestStr =
            "<s:Envelope xmlns:s=\"http://schemas.xmlsoap.org/soap/envelope/\">" .
            "<s:Body xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\">" .
            "<Do3DSTransaction xmlns=\"https://www.apsp.biz/\">" .
            "<MerchID>" . $this->configuration->merchant_id . "</MerchID>" .
            "<MerchPassword>" . $this->configuration->merchant_password . "</MerchPassword>" .
            "<TrType>" . $transaction->action_type . "</TrType>" .
            "<CardNum>" . $transaction->card_number . "</CardNum>" .
            "<CVV2>" . $transaction->card_cvv . "</CVV2>" .
            "<ExpDay></ExpDay>" .
            "<ExpMonth>" . $transaction->card_expiry_month . "</ExpMonth>" .
            "<ExpYear>" . $transaction->card_expiry_year . "</ExpYear>" .
            "<CardHName>" . $transaction->card_holder . "</CardHName>" .
            "<Amount>" . $transaction->amount . "</Amount>" .
            "<CurrencyCode>" . $transaction->currency_code . "</CurrencyCode>" .
            "<Addr/>" .
            "<PostCode/>" .
            "<TransID>" . $transaction->original_transaction_id . "</TransID>" .
            "<UserIP>" . $transaction->user_ip . "</UserIP>" .
            "<UDF1/>" .
            "<UDF2>" .  htmlentities($transaction->user_defined_function) . "</UDF2>" .
            "<UDF3>" . htmlentities($udf3) . "</UDF3>" .
            "<OrderRef>" . $transaction->order_reference . "</OrderRef>" .
            "</Do3DSTransaction>" .
            "</s:Body>" .
            "</s:Envelope>";

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_PORT => "9085",
            CURLOPT_URL => "https://www.apsp.biz:9085/Service.asmx",
            CURLOPT_FAILONERROR => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $requestStr,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: text/xml; charset=utf-8",
                "SOAPAction: \"https://www.apsp.biz/Do3DSTransaction\""
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err || !isset($response) || empty($response)) {
            return null;
        }

        $response = $this->getXmlTag($response, 'Do3DSTransactionResult');

        $responseFields = explode("||", $response);

        $transactionResponse = new TransactionResponse();
        $transactionResponse->result = $responseFields[0];
        $transactionResponse->psp_id = $responseFields[1];
        $transactionResponse->bank_transaction_id = $responseFields[2];
        $transactionResponse->date = $responseFields[3];
        $transactionResponse->time = $responseFields[4];
        $transactionResponse->acquirer_reference = $responseFields[5];
        $transactionResponse->authorization_code = $responseFields[6];
        $transactionResponse->address_verification_response = $responseFields[7];
        $transactionResponse->acquirer_code = $responseFields[10];
        $transactionResponse->user_ip = $responseFields[11];
        $transactionResponse->user_defined_function = $responseFields[13];
        $transactionResponse->extra_data = $responseFields[14];
        $transactionResponse->card_country = $responseFields[15];
        return $transactionResponse;
    }

    public function verify($requestXmlStr)
    {
        preg_match('/(hash=")(.*?)(")/', $requestXmlStr, $regexMatch);
        $receivedHash = $regexMatch[2];
        $secretRequestXml = preg_replace('/(hash=")(.*?)(")/', '${1}' . $this->configuration->hashing_secret . '${3}', $requestXmlStr);
        $generatedHash = md5($secretRequestXml);
        return $generatedHash === $receivedHash;
    }

    public function parseNotification($requestXmlStr)
    {
        if (!isset($requestXmlStr)) {
            return null;
        }
        $notificationRequest = new NotificationRequest();
        $notificationRequest->order_reference = $this->getXmlTag($requestXmlStr, 'ORef');
        $notificationRequest->result = $this->getXmlTag($requestXmlStr, 'Result');
        $notificationRequest->authorization_code = $this->getXmlTag($requestXmlStr, 'AuthCode');
        $notificationRequest->card_input = $this->getXmlTag($requestXmlStr, 'CardInput');
        $notificationRequest->psp_id = $this->getXmlTag($requestXmlStr, 'pspid');
        $notificationRequest->status_3ds = $this->getXmlTag($requestXmlStr, 'Status3DS');
        $notificationRequest->currency_code = $this->getXmlTag($requestXmlStr, 'Currency');
        $notificationRequest->amount = $this->getXmlTag($requestXmlStr, 'Value');
        $notificationRequest->iso_response = $this->getXmlTag($requestXmlStr, 'ISOResp');
        $notificationRequest->card_number = $this->getXmlTag($requestXmlStr, 'CardNum');
        $notificationRequest->card_expiry = $this->getXmlTag($requestXmlStr, 'CardExpiry');
        $notificationRequest->card_holder = $this->getXmlTag($requestXmlStr, 'CardHName');
        $notificationRequest->acquirer_code = $this->getXmlTag($requestXmlStr, 'Acq');
        $notificationRequest->source = $this->getXmlTag($requestXmlStr, 'Source');
        $notificationRequest->card_country = $this->getXmlTag($requestXmlStr, 'CardCountry');
        $notificationRequest->card_type = $this->getXmlTag($requestXmlStr, 'CardType');
        return $notificationRequest;
    }
    
    public function parseRedirect($requestXmlStr)
    {
        if (!isset($requestXmlStr)) {
            return null;
        }
        $redirectRequest = new RedirectRequest();
        $redirectRequest->order_reference = $this->getXmlTag($requestXmlStr, 'ORef');
        $redirectRequest->result = $this->getXmlTag($requestXmlStr, 'Result');
        return $redirectRequest;
    }
}
