<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->boot();

$report = \DB::table('report')->orderBy('report_id','desc')->first();
echo "Report: {$report->name} | looks_generated: {$report->looks_generated}\n";

// ── Raw Snowflake login test ──────────────────────────────────────────────────
$host      = trim(env('SNOWFLAKE_HOST'));
$account   = trim(env('SNOWFLAKE_ACCOUNT'));
$username  = trim(env('SNOWFLAKE_USERNAME'));
$password  = trim(env('SNOWFLAKE_PASSWORD'));
$warehouse = trim(env('SNOWFLAKE_WAREHOUSE'));
$database  = trim(env('SNOWFLAKE_DATABASE'));
$role      = trim(env('SNOWFLAKE_ROLE'));

echo "\n--- Snowflake config ---\n";
echo "HOST:      $host\n";
echo "ACCOUNT:   $account\n";
echo "USERNAME:  $username\n";
echo "PASSWORD:  " . (empty($password) ? '(empty!)' : str_repeat('*', strlen($password))) . "\n";
echo "WAREHOUSE: $warehouse\n";
echo "DATABASE:  $database\n";
echo "ROLE:      $role\n\n";

$url = "https://{$host}/session/v1/login-request"
     . "?warehouse=" . urlencode($warehouse)
     . "&roleName="  . urlencode($role)
     . "&databaseName=" . urlencode($database);

$body = json_encode([
    'data' => [
        'CLIENT_APP_ID'      => 'JavaScript',
        'CLIENT_APP_VERSION' => '1.0.0',
        'SVN_REVISION'       => '',
        'ACCOUNT_NAME'       => $account,
        'LOGIN_NAME'         => $username,
        'PASSWORD'           => $password,
        'CLIENT_ENVIRONMENT' => ['APPLICATION' => 'PHP', 'OS' => 'Linux', 'OCSP_MODE' => 'FAIL_OPEN'],
    ],
]);

echo "Login URL: $url\n\n";

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $body,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_SSL_VERIFYPEER => false,   // disable SSL check for local testing
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
    CURLOPT_VERBOSE        => false,
]);

$response = curl_exec($ch);
$err      = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
if ($err) echo "cURL Error: $err\n";

$decoded = json_decode($response, true);
echo "Success:   " . ($decoded['success'] ? 'true' : 'false') . "\n";

if (!($decoded['success'] ?? false)) {
    $msg = $decoded['message']
        ?? $decoded['data']['errorMessage']
        ?? $decoded['data']['authnMethod']
        ?? $response;
    echo "Error Msg: $msg\n";
    echo "\nRaw response (first 500 chars):\n" . substr($response, 0, 500) . "\n";
} else {
    $token = $decoded['data']['token'] ?? null;
    echo "Token:     " . ($token ? substr($token, 0, 30) . '...' : '(none)') . "\n";
    echo "\nSnowflake login SUCCESS!\n";

    // Try a simple query
    $reqUuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff),
        mt_rand(0,0x0fff)|0x4000, mt_rand(0,0x3fff)|0x8000,
        mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff));
    $queryUrl = "https://{$host}/queries/v1/query-request?requestId=" . $reqUuid;
    $qBody = json_encode([
        'sqlText'            => "SELECT CURRENT_USER() as usr, CURRENT_WAREHOUSE() as wh, CURRENT_ROLE() as role",
        'asyncExec'          => false,
        'sequenceId'         => 1,
        'isInternal'         => false,
        'querySubmissionTime'=> round(microtime(true) * 1000),
    ]);
    $qch = curl_init($queryUrl);
    curl_setopt_array($qch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $qBody,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Snowflake Token="' . $token . '"',
        ],
    ]);
    $qResponse = curl_exec($qch);
    $qCode     = curl_getinfo($qch, CURLINFO_HTTP_CODE);
    curl_close($qch);
    echo "Query HTTP: $qCode\n";
    echo "Query response (first 800 chars):\n" . substr($qResponse, 0, 800) . "\n";
    $qDecoded = json_decode($qResponse, true);
    if (!empty($qDecoded['data']['errorMessage'])) echo "Query error: " . $qDecoded['data']['errorMessage'] . "\n";
    if (!empty($qDecoded['message'])) echo "Query message: " . $qDecoded['message'] . "\n";
}
