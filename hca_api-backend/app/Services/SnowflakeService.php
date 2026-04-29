<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * SnowflakeService
 *
 * Direct connection to Snowflake using the REST SQL API v1.
 * Authenticates with username + password via the login endpoint,
 * caches the session token per process, then runs queries.
 *
 * Config (all from .env):
 *   SNOWFLAKE_HOST       psb29308.us-east-1.snowflakecomputing.com
 *   SNOWFLAKE_ACCOUNT    psb29308.us-east-1
 *   SNOWFLAKE_USERNAME
 *   SNOWFLAKE_PASSWORD
 *   SNOWFLAKE_DATABASE   DB_ANALYTICS
 *   SNOWFLAKE_WAREHOUSE  WH_LOOKER_STUDIO
 *   SNOWFLAKE_ROLE       ANALYTICS_USER
 */
class SnowflakeService
{
    private string $host;
    private string $account;
    private string $username;
    private string $password;
    private string $warehouse;
    private string $database;
    private string $role;

    /** Session token cached for the lifetime of this object */
    private ?string $token      = null;
    private ?string $masterToken = null;

    public function __construct()
    {
        $this->host      = trim(env('SNOWFLAKE_HOST',     ''));
        $this->account   = trim(env('SNOWFLAKE_ACCOUNT',  ''));
        $this->username  = trim(env('SNOWFLAKE_USERNAME', '') ?: env('SNOWFLAKE_USER', ''));
        $this->password  = trim(env('SNOWFLAKE_PASSWORD', ''));
        $this->warehouse = trim(env('SNOWFLAKE_WAREHOUSE',''));
        $this->database  = trim(env('SNOWFLAKE_DATABASE', ''));
        $this->role      = trim(env('SNOWFLAKE_ROLE',     ''));
    }

    // =========================================================================
    // Public: run a single query, return rows as array of assoc arrays
    // =========================================================================
    public function query(string $sql, string $schema): ?array
    {
        if (!$this->ensureToken()) return null;

        $url  = "https://{$this->host}/queries/v1/query-request"
              . "?requestId=" . $this->uuid()
              . ($schema ? "&schemaName=" . urlencode("{$this->database}.{$schema}") : '');

        $body = json_encode([
            'sqlText'            => $sql,
            'asyncExec'          => false,
            'sequenceId'         => 1,
            'isInternal'         => false,
            'querySubmissionTime'=> round(microtime(true) * 1000),
            'parameters'         => [
                'TIMESTAMP_OUTPUT_FORMAT' => 'YYYY-MM-DD HH24:MI:SS',
            ],
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Snowflake Token="' . $this->token . '"',
            ],
        ]);

        $response = curl_exec($ch);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($err || !$response) {
            Log::error("Snowflake query error: $err");
            return null;
        }

        return $this->parseQueryResponse($response);
    }

    // =========================================================================
    // Public: run multiple queries in parallel, returns [ key => rows ]
    // =========================================================================
    public function queryParallel(array $queries, string $schema): array
    {
        if (empty($queries) || !$this->ensureToken()) return [];

        $mh      = curl_multi_init();
        $handles = [];

        foreach ($queries as $key => $sql) {
            $url  = "https://{$this->host}/queries/v1/query-request"
                  . "?requestId=" . $this->uuid()
                  . ($schema ? "&schemaName=" . urlencode("{$this->database}.{$schema}") : '');
            $body = json_encode([
                'sqlText'            => $sql,
                'asyncExec'          => false,
                'sequenceId'         => 1,
                'isInternal'         => false,
                'querySubmissionTime'=> round(microtime(true) * 1000),
            ]);

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $body,
                CURLOPT_TIMEOUT        => 120,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'Authorization: Snowflake Token="' . $this->token . '"',
                ],
            ]);

            $handles[$key] = $ch;
            curl_multi_add_handle($mh, $ch);
        }

        $running = null;
        do {
            curl_multi_exec($mh, $running);
            curl_multi_select($mh, 0.5);
        } while ($running > 0);

        $results = [];
        foreach ($handles as $key => $ch) {
            $response     = curl_multi_getcontent($ch);
            $results[$key] = $response ? $this->parseQueryResponse($response) : null;
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
        curl_multi_close($mh);

        return $results;
    }

    // =========================================================================
    // Auth: login and cache session token
    // =========================================================================
    private function ensureToken(): bool
    {
        if ($this->token) return true;

        if (!$this->host || !$this->username || !$this->password) {
            Log::error('SnowflakeService: SNOWFLAKE_HOST, USERNAME or PASSWORD not set in .env');
            return false;
        }

        $url = "https://{$this->host}/session/v1/login-request"
             . "?warehouse=" . urlencode($this->warehouse)
             . "&roleName="  . urlencode($this->role)
             . "&databaseName=" . urlencode($this->database);

        $body = json_encode([
            'data' => [
                'CLIENT_APP_ID'       => 'JavaScript',
                'CLIENT_APP_VERSION'  => '1.0.0',
                'SVN_REVISION'        => '',
                'ACCOUNT_NAME'        => $this->account,
                'LOGIN_NAME'          => $this->username,
                'PASSWORD'            => $this->password,
                'CLIENT_ENVIRONMENT'  => [
                    'APPLICATION' => 'Kairos',
                    'OS'          => 'Linux',
                    'OS_VERSION'  => 'Linux',
                    'OCSP_MODE'   => 'FAIL_OPEN',
                ],
            ],
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
        ]);

        $response = curl_exec($ch);
        $err      = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err || !$response) {
            Log::error("Snowflake login cURL error: $err");
            return false;
        }

        $decoded = json_decode($response, true);

        if (!($decoded['success'] ?? false)) {
            $msg = $decoded['message'] ?? $decoded['data']['errorMessage'] ?? $response;
            Log::error("Snowflake login failed (HTTP $httpCode): $msg");
            return false;
        }

        $this->token       = $decoded['data']['token']       ?? null;
        $this->masterToken = $decoded['data']['masterToken'] ?? null;

        if (!$this->token) {
            Log::error('Snowflake login succeeded but no token returned');
            return false;
        }

        return true;
    }

    // =========================================================================
    // Parse the query response into rows
    // =========================================================================
    private function parseQueryResponse(string $raw): ?array
    {
        $decoded = json_decode($raw, true);

        if (!($decoded['success'] ?? false)) {
            $msg = $decoded['message'] ?? $decoded['data']['errorMessage'] ?? 'unknown error';
            Log::warning("Snowflake query failed: $msg");
            return null;
        }

        $data      = $decoded['data'] ?? [];
        $rowset    = $data['rowset']    ?? [];
        $rowtype   = $data['rowtype']   ?? [];

        if (empty($rowset) || empty($rowtype)) return [];

        // Map column names from rowtype
        $columns = array_map(fn($col) => strtolower($col['name']), $rowtype);

        $rows = [];
        foreach ($rowset as $row) {
            $rows[] = array_combine($columns, $row);
        }

        return $rows;
    }

    // =========================================================================
    // Inject USE SCHEMA before the query so the right schema is active
    // =========================================================================
    private function injectSchema(string $sql, string $schema): string
    {
        if (!$schema) return $sql;
        // Wrap in a multi-statement block: USE SCHEMA then the actual query
        return "USE SCHEMA {$this->database}.{$schema}; " . $sql;
    }

    private function uuid(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
