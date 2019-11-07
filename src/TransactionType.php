<?php

namespace ApcoPay;

abstract class TransactionType
{
    const Purchase = 1; // Transfers value from the cardholders account
    const VoidPurchase = 3; // Cancellation of a purchase transaction – Before end of day
    const Authorisation = 4; // Reserve the specified value from the card holder
    const Capture = 5; // Transfers a reserved value from the cardholders account
    const VoidCredit = 6; // Cancel a credit - Before end of day
    const VoidCapture = 7; // Cancel a capture - Before end of day
    const VoidAuthorisation = 9; // Cancel an authorization - Before end of day
    const RepeatPurchase = 11; // Repeats a purchase by submitting the original PSPID
    const PartialRefund = 12; // Reverse a partial/full amount of the original transaction
    const OriginalCredit = 13; // Pay out an amount greater than the amount of the original transaction
    const RepeatAuthorisation = 14; // Repeats an authorization by submitting the original PSPID
}
