<?php

namespace App\Enums;

enum CarrierCode: int
{
    case Success = 200; // success
    case InternalServerError = 999; // Internal Server Error
    case InvalidParameter = 10002; // Invalid parameter
}
