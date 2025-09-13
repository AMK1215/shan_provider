<?php

namespace App\Enums;

enum SeamlessWalletCode: int
{
    case Success = 0; // success
    case InternalServerError = 999; // Internal Server Error
    case MemberNotExist = 1000; // API member does not exist
    case InsufficientBalance = 1001; // API member balance is insufficient
    case ProxyKeyError = 1002; // API proxy key error
    case DuplicateTransaction = 1003; // Duplicate API transactions
    case InvalidSignature = 1004; // API signature is invalid
    case GameListNotFound = 1005; // API not getting game list
    case BetNotExist = 1006; // API bet does not exist
    case ProductUnderMaintenance = 2000; // API product is under maintenance
    case InvalidCurrency = 2001; // API invalid currency

}
