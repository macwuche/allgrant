<?php

namespace App\Enums;

enum TxnType: string
{
    case Deposit = 'deposit';
    case Subtract = 'subtract';
    case ManualDeposit = 'manual_deposit';
    case SendMoney = 'send_money';
    case Exchange = 'exchange';
    case Referral = 'referral';
    case SignupBonus = 'signup_bonus';
    case PortfolioBonus = 'portfolio_bonus';
    case RewardRedeem = 'reward_redeem';
    case Withdraw = 'withdraw';
    case WithdrawAuto = 'withdraw_auto';
    case ReceiveMoney = 'receive_money';
    case Refund = 'refund';
    case FundTransfer = 'fund_transfer';
    case Grant = 'grant';
    case GrantApply = 'grant_applied';
    case PayBill = 'pay_bill';
    case CardCreate = 'card_create';
    case CardLoad = 'card_load';
    case CardDeposit = 'card_deposit';
}
