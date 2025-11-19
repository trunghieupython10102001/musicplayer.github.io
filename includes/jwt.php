<?php
/**
 * Simple JWT Implementation
 * 
 * Handles encoding and decoding of JSON Web Tokens
 */

class JWT {
    /**
     * Encode payload to JWT
     * 
     * @param array $payload Data to encode
     * @param string $secret Secret key
     * @return string JWT token
     */
    public static function encode($payload, $secret) {
        // Header
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        
        // Base64 Url Encode Header and Payload
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));
        
        // Create Signature
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        // Create JWT
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
    
    /**
     * Decode JWT and verify signature
     * 
     * @param string $token JWT token
     * @param string $secret Secret key
     * @return array|null Payload if valid, null otherwise
     */
    public static function decode($token, $secret) {
        $tokenParts = explode('.', $token);
        
        if (count($tokenParts) != 3) {
            return null;
        }
        
        $header = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[0]));
        $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1]));
        $signatureProvided = $tokenParts[2];
        
        // Check signature
        $signature = hash_hmac('sha256', $tokenParts[0] . "." . $tokenParts[1], $secret, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        if ($base64UrlSignature === $signatureProvided) {
            return json_decode($payload, true);
        }
        
        return null;
    }
}

