<?php

require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$baseURL      = "https://login.autodarts.io";
$realm        = "autodarts";
$clientID     = "developer-dotty-flow-liga";
$clientSecret = "AyiqXusLNmi9KmjyV5Yj3UQVycZ2js47";
$userName     = "dotty";
$password     = "HMPF9xgyqZQMFzkjwMh3";

$token = null;
// Read contents of file autodarts-token.json
$tokenFilePath = dirname(__DIR__) . '/autodarts-token.json';
if (file_exists($tokenFilePath)) {
    $tokenJson = file_get_contents($tokenFilePath);
    if ($tokenJson !== false) {
        $token = json_decode($tokenJson);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Failed to parse token from file: " . json_last_error_msg());
            $token = null;
        }
    } else {
        error_log("Failed to read token file: $tokenFilePath");
    }
}
$publicKey = null;

function realmURL()
{
    global $baseURL, $realm;
    return "{$baseURL}/realms/{$realm}";
}

function tokenURL()
{
    return realmURL() . "/protocol/openid-connect/token";
}

class TokenResponse
{
    public $access_token;
    public $refresh_token;
    public $expires_in;
    public $refresh_expires_in;
    public $token_type;
    public $scope;
}

function fetchToken()
{
    global $clientID, $clientSecret, $userName, $password, $token;

    $params = [
        'grant_type' => 'password',
        'client_id' => $clientID,
        'client_secret' => $clientSecret,
        'username' => $userName,
        'password' => $password
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, tokenURL());
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo $httpCode;
    if ($httpCode !== 200) {
        throw new Exception("Failed to fetch token");
    }
    $token = json_decode($response);
    $token->refresh_token_expires_at = time() + ($token->refresh_expires_in ?? 0);

    // Write $token to file autodarts-token.json
    global $tokenFilePath;
    $tokenJson = json_encode($token, JSON_PRETTY_PRINT);

    if (file_put_contents($tokenFilePath, $tokenJson) === false) {
        error_log("Failed to write token to file: $tokenFilePath");
    }

    if (!verifyToken($token->access_token)) {
        throw new Exception("Token verification failed");
    }

    return null;
}

function refreshToken()
{
    global $clientID, $clientSecret, $token, $tokenFilePath;

    if (!$token || !isset($token->refresh_token)) {
        throw new Exception("No refresh token available");
    }

    $params = [
        'grant_type' => 'refresh_token',
        'client_id' => $clientID,
        'client_secret' => $clientSecret,
        'refresh_token' => $token->refresh_token
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, tokenURL());
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception("Failed to refresh token");
    }
    $newToken = json_decode($response);
    $newToken->refresh_token_expires_at = time() + ($newToken->refresh_expires_in ?? 0);

    if (!verifyToken($newToken->access_token)) {
        throw new Exception("New token verification failed");
    }

    $token = $newToken;

    // Write new token to file
    $tokenJson = json_encode($token, JSON_PRETTY_PRINT);
    if (file_put_contents($tokenFilePath, $tokenJson) === false) {
        error_log("Failed to write new token to file: $tokenFilePath");
    }

    return $token->access_token;
}

function isRefreshTokenExpired()
{
    global $token;
    
    if (!$token || !isset($token->refresh_token_expires_at)) {
        return true;
    }

    return time() > $token->refresh_token_expires_at;
}

function verifyToken($tokenString)
{
    try {
        $publicKey = fetchPublicKey();

        $token = JWT::decode($tokenString, new Key($publicKey, 'RS256'));

        if ($token->iss !== realmURL()) {
            throw new Exception("Invalid token issuer");
        }

        if (!in_array("account", (array)$token->aud)) {
            throw new Exception("Invalid token audience");
        }

        if (time() > $token->exp - 60) {
            throw new Exception("Token has expired");
        }
        return true;
    } catch (Exception $e) {
        return false;
    }
}


function fetchPublicKey()
{
    global $publicKey;

    if ($publicKey === null) {
        $response = file_get_contents(realmURL());
        $realm = json_decode($response);

        $pemKey = "-----BEGIN PUBLIC KEY-----\n" . $realm->public_key . "\n-----END PUBLIC KEY-----";
        $publicKey = openssl_pkey_get_public($pemKey);
    }

    return $publicKey;
}

function accessToken()
{
    global $token;

    if ($token === null || isRefreshTokenExpired()) {
        fetchToken();
    } elseif (!verifyToken($token->access_token)) {
        try {
            return refreshToken();
        } catch (Exception $e) {
            // If refresh fails, fetch a new token
            fetchToken();
        }
    }

    return $token->access_token ?? null;
}



