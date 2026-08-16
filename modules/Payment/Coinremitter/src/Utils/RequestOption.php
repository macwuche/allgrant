<?php

namespace Coinremitter\Utils;

class RequestOption
{
    public static function setRequestParam($function, $param): array
    {
        if ($function == 'createAddress') {
            return [
                'label' => request()->label ?? '',
            ];
        } elseif ($function == 'validateAddress') {
            return [
                'address' => request()->address ?? '',
            ];
        } elseif ($function == 'estimateWithdraw') {
            return [
                'address' => request()->address ?? '',
                'amount' => request()->amount ?? '',
                'withdrawal_speed' => request()->withdrawal_speed ?? '',
            ];
        } elseif ($function == 'withdraw') {
            return [
                'address' => request()->address ?? '',
                'amount' => request()->amount ?? '',
                'withdrawal_speed' => request()->withdrawal_speed ?? '',
            ];
        } elseif ($function == 'getTransaction') {
            return [
                'id' => request()->id ?? '',
            ];
        } elseif ($function == 'getTransactionByAddress') {
            return [
                'address' => request()->address ?? '',
            ];
        } elseif ($function == 'createInvoice') {
            return [
                'amount' => request()->amount ?? '',
                'name' => request()->name ?? '',
                'email' => request()->email ?? '',
                'fiat_currency' => request()->fiat_currency ?? '',
                'expiry_time_in_minutes' => request()->expiry_time_in_minutes ?? '',
                'notify_url' => request()->notify_url ?? '',
                'success_url' => request()->success_url ?? '',
                'fail_url' => request()->fail_url ?? '',
                'description' => request()->description ?? '',
                'custom_data1' => request()->custom_data1 ?? '',
                'custom_data2' => request()->custom_data2 ?? '',
            ];
        } elseif ($function == 'getInvoice') {
            return [
                'invoice_id' => request()->invoice_id ?? '',
            ];
        } elseif ($function == 'fiatToCryptoRate') {
            return [
                'fiat' => request()->fiat ?? '',
                'fiat_amount' => request()->fiat_amount ?? '',
                'crypto' => request()->crypto ?? '',
            ];
        } elseif ($function == 'cryptoToFiatRate') {
            return [
                'crypto' => request()->crypto ?? '',
                'crypto_amount' => request()->crypto_amount ?? '',
                'fiat' => request()->fiat ?? '',
            ];
        }

        return [];

    }
}
