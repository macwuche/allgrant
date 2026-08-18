<?php

namespace App\Enums;

enum ReferralType: string
{
    case Deposit = 'deposit';
    case Grant = 'grant';
    case PayBill = 'pay_bill';
}
