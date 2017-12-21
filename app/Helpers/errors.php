<?php

abstract class APIError
{
    const METHOD_NOT_ALLOWED = ['code' => 8, 'message' => 'Method not allowed'];
    const INVALID_DATA_PROVIDED = ['code' => 9, 'message' => 'Invalid data provided'];

    const INVALID_USER = ['code' => 10, 'message' => 'Selected user does not exists'];
    const INVALID_PIN = ['code' => 11, 'message' => 'Sent PIN is invalid'];
    const INVALID_OLD_PIN = ['code' => 12, 'message' => 'Old PIN is invalid'];

    const WITHDRAW_ERROR = ['code' => 20, 'message' => 'Withdrawal error'];
    const WITHDRAW_ERROR_NETWORK_FEE = ['code' => 21, 'message' => 'Withdrawal error insuficient active balance'];

    const CODE_ALREADY_SENT = ['code' => 30, 'message' => 'Code already sent'];
    const WRONG_CODE = ['code' => 31, 'message' => 'Wrong code provided'];
    const USER_ALREADY_EXISTS = ['code' => 32, 'message' => 'User already exists'];
}