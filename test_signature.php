<?php

// Test signature generation
$operator_code = 'a3h1';
$request_time = 1753517924;
$secret_key = 'shana3h1';

$sign = md5($operator_code.$request_time.'getbalance'.$secret_key);

echo 'Operator Code: '.$operator_code."\n";
echo 'Request Time: '.$request_time."\n";
echo 'Secret Key: '.$secret_key."\n";
echo 'Generated Signature: '.$sign."\n";

// Expected signature from logs
$expected_sign = '0f99a5f789667aaffea8fa867069b97d';
echo 'Expected Signature: '.$expected_sign."\n";
echo 'Match: '.($sign === $expected_sign ? 'YES' : 'NO')."\n";
