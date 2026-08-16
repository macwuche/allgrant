<?php

namespace Payment\Coinbase;

use Payment\Transaction\BaseTxn;

class PaystackTxn extends BaseTxn
{
    public function __construct($txnInfo)
    {
        parent::__construct($txnInfo);
    }

    public function deposit()
    {
        $paystackCredential = gateway_info('paystack');
        config()->set([
            'paystack.publicKey' => $paystackCredential->public_key,
            'paystack.merchantEmail' => $paystackCredential->merchant_email,
            'paystack.secretKey' => $paystackCredential->secret_key,
        ]);

        $data = [
            'amount' => $this->amount * 100,
            'reference' => $this->txn,
            'email' => $this->userEmail,
            'currency' => $this->currency,
            'orderID' => $this->txn,
        ];

        return \Unicodeveloper\Paystack\Facades\Paystack::getAuthorizationUrl($data)->redirectNow();
    }
}
