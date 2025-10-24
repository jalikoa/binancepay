<?php
namespace Tests\Unit\Utils;

use App\Utils\SignatureVerifier;
use Tests\TestCase;

class SignatureVerifierTest extends TestCase
{
    public function test_generate_signature_matches_binance_spec()
    {
        $secret = 'my_secret_key';
        $timestamp = '1729800000000';
        $nonce = 'abcd1234';
        $body = '{"amount":10}';

        $signature = SignatureVerifier::generateSignature($secret, $timestamp, $nonce, $body);

        // Expected: HMAC-SHA512 of "1729800000000\nabcd1234\n{\"amount\":10}\n"
        $expected = strtoupper(hash_hmac('sha512', "1729800000000\nabcd1234\n{\"amount\":10}\n", $secret));

        $this->assertEquals($expected, $signature);
    }

    public function test_verify_signature_returns_true_for_valid_data()
    {
        $secret = 'secret';
        $timestamp = '1000';
        $nonce = 'test';
        $body = '{"ok":1}';
        $signature = SignatureVerifier::generateSignature($secret, $timestamp, $nonce, $body);

        $valid = SignatureVerifier::verify($secret, $timestamp, $nonce, $body, $signature);
        $this->assertTrue($valid);
    }

    public function test_verify_signature_returns_false_for_tampered_body()
    {
        $secret = 'secret';
        $timestamp = '1000';
        $nonce = 'test';
        $body = '{"ok":1}';
        $signature = SignatureVerifier::generateSignature($secret, $timestamp, $nonce, $body);

        $tampered = '{"ok":2}';
        $valid = SignatureVerifier::verify($secret, $timestamp, $nonce, $tampered, $signature);
        $this->assertFalse($valid);
    }
}