<?php

namespace App\Enums;

enum GrantStatus: string
{
    case Due = 'due';
    case Running = 'running';
    case Cancelled = 'cancelled';
    case Completed = 'completed';
    case Reviewing = 'reviewing';
    case Rejected = 'rejected';
}
