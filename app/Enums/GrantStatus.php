<?php

namespace App\Enums;

enum GrantStatus: string
{
    case Reviewing = 'reviewing';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
}
