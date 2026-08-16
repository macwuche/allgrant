<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ __('Bank Statement') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }

        .header {
            margin-bottom: 30px;
        }

        .account-info,
        .statement-period {
            margin-bottom: 20px;
        }

        .summary-table,
        .transactions-table,
        .balance-summary table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table th,
        .summary-table td,
        .transactions-table th,
        .transactions-table td,
        .balance-summary td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .summary-table th,
        .transactions-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .transactions-table {
            margin-top: 3rem;
        }

        .deposit {
            color: green;
        }

        .withdrawal {
            color: red;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="header">
        @php
            $height =
                setting('site_logo_height', 'global') == 'auto' ? 'auto' : setting('site_logo_height', 'global') . 'px';
            $width =
                setting('site_logo_width', 'global') == 'auto' ? 'auto' : setting('site_logo_width', 'global') . 'px';
        @endphp
        <div class="logo">
            <a href="{{ route('home') }}">
                <img src="{{ base_path('assets/' . setting('site_logo', 'global')) }}"
                    style="height:{{ $height }};width:{{ $width }};max-width:none" alt="">
            </a>
        </div>
        <div style="margin-top: 10px;">
            <b>{{ __('Email') }}: </b><span>{{ setting('site_email', 'global') }}</span>
        </div>
    </div>

    <div class="account-info">
        <div><b>{{ __('Account Holder') }}: </b>{{ $user->fullname }}</div>
        <div><b>{{ __('Account Number') }}: </b>{{ $user->account_number }}</div>
        @if ($user->branch_id)
            <div><b>{{ __('Branch') }}: </b>{{ $user->branch->name }}</div>
        @endif
    </div>

    <div class="statement-period">
        {{ __('Statement Period') }}: {{ $from_date }} {{ __('to') }} {{ $to_date }}
    </div>

    <table class="summary-table">
        <thead>
            <tr>
                <th>{{ __('Opening Balance') }}</th>
                <th>{{ __('Total Withdrawal (Dr.)') }}</th>
                <th>{{ __('Total Deposit (Cr.)') }}</th>
                <th>{{ __('Closing Balance') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ number_format($opening_balance, 2) }} {{ $currency }}</td>
                <td>{{ number_format($total_withdrawals, 2) }} {{ $currency }}</td>
                <td>{{ number_format($total_deposits, 2) }} {{ $currency }}</td>
                <td>{{ number_format($closing_balance, 2) }} {{ $currency }}</td>
            </tr>
        </tbody>
    </table>

    <table class="transactions-table">
        <thead>
            <tr>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Transaction ID') }}</th>
                <th>{{ __('Description') }}</th>
                <th>{{ __('Deposit (Cr.)') }}</th>
                <th>{{ __('Withdrawal (Dr.)') }}</th>
                <th>{{ __('Balance') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="5">{{ __('Opening Balance') }}</td>
                <td>{{ number_format($opening_balance, 2) }} {{ $currency }}</td>
            </tr>
            @foreach ($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->date_formatted }}</td>
                    <td>{{ $transaction->tnx }}</td>
                    <td>{{ $transaction->description }}</td>
                    <td class="deposit">{{ $transaction->is_deposit ? number_format($transaction->amount, 2) : '' }}
                    </td>
                    <td class="withdrawal">
                        {{ !$transaction->is_deposit ? number_format($transaction->amount, 2) : '' }}</td>
                    <td>{{ number_format($transaction->running_balance, 2) }} {{ $currency }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="5">{{ __('Closing Balance') }}</td>
                <td>{{ number_format($closing_balance, 2) }} {{ $currency }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>{{ __('This is a computer generated statement and does not require a signature') }}</p>
        <p>{{ __('Generated on') }}: {{ now()->format('d M Y, h:i A') }}</p>
    </div>
</body>

</html>
