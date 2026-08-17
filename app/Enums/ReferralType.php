<?php

namespace App\Enums;

enum ReferralType: string
{
    case Deposit = 'deposit';
    case DPS = 'dps';
    case FDR = 'fdr';
    case Grant = 'grant';
    case PayBill = 'pay_bill';
}
