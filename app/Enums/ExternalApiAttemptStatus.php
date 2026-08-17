<?php

namespace App\Enums;

enum ExternalApiAttemptStatus: string
{
    case Started = 'started';
    case Successful = 'successful';
    case Failed = 'failed';
}