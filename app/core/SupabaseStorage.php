<?php
// FILE: /app/core/SupabaseStorage.php

/**
 * SupabaseStorage
 *
 * Uploads files to a Supabase Storage bucket via the REST API.
 * Returns a full public URL that's stored directly in the DB.
 *
 * Required environment variables:
 *   SUPABASE_URL          e.g. https://xxxxx.supabase.co
 *   SUPABASE_SERVICE_KEY  service_role key (or anon key if bucket is public)
 *   SUPABASE_BUCKET       e.g. tour-images   (default: tour-images)
 *
 * The bucket MUST be marked public in the Supabase dashboard for the URLs
 * returned here to load in the viewer without auth.
 */
class SupabaseStorage {

    /**
     * Whether Supabase Storage is configured & should be used
     */
    public static function isEnabled() {
        return !empty(self::url()) && !empty(self::key());
    }

    private static function url() {
        $raw = $_ENV['SUPABASE_URL'] ?? getenv('SUPABASE_URL') ?? '';
        // Defensive cleaning — env vars set via dashboard sometimes carry
        // stray whitespace or pasted-in garbage. Find the first "http" and
        // start from there, then trim trailing whitespace + slash.
        $raw = trim((string)$raw);
        $pos = stripos($raw, 'http');
        if ($pos !== false && $pos > 0) {
            $raw = substr($raw, $pos);
        }
        return rtrim($raw, "/ \t\n\r\0\x0B");
    }

    private static function key() {
        // Trim only — keep JWT exactly as issued
        return trim((string)($_ENV['SUPABASE_SERVICE_KEY'] ?? getenv('SUPABASE_SERVICE_KEY') ?? ''));
    }

    private static function bucket() {
        $b = trim((string)($_ENV['SUPABASE_BUCKET'] ?? getenv('SUPABASE_BUCKET') ?? ''));
        return $b !== '' ? $b : 'tour-images';
    }

    /**
     * Upload a single file (from $_FILES style array) to Supabase Storage
     *
     * @param array  $file       PHP $_FILES style: ['name'=>..,'tmp_name'=>..,'type'=>..,'size'=>..]
     * @param string $folder     Folder inside the bucket (e.g. 'scenes')
     * @return string|false      Full public URL on success, false on failure
     */
    public static function uploadFile(array $file, $folder = 'scenes') {
        if (!self::isEnabled()) {
            return false;
        }
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return false;
        }

        $url    = rtrim(self::url(), '/');
        $key    = self::key();
        $bucket = self::bucket();

        // Generate unique remote filename
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $ext = preg_replace('/[^a-z0-9]/', '', $ext) ?: 'jpg';
        $remoteName = uniqid('', true) . '_' . time() . '.' . $ext;
        $remotePath = trim($folder, '/') . '/' . $remoteName;

        $endpoint = $url . '/storage/v1/object/' . rawurlencode($bucket) . '/' . $remotePath;

        $mime = $file['type'] ?: 'application/octet-stream';
        $body = file_get_contents($file['tmp_name']);
        if ($body === false) return false;

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $key,
                'apikey: ' . $key,
                'Content-Type: ' . $mime,
                'x-upsert: true',
                'Cache-Control: public, max-age=31536000',
            ],
            CURLOPT_TIMEOUT        => 60,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300) {
            error_log("SupabaseStorage upload failed [$httpCode]: $response | $err");
            return false;
        }

        // Build the public URL
        return $url . '/storage/v1/object/public/' . rawurlencode($bucket) . '/' . $remotePath;
    }

    /**
     * Create a one-time signed upload URL the BROWSER can PUT a file to
     * directly — so big 360° photos never touch our Vercel function (which
     * has a 4.5MB request cap).
     *
     * @param string $originalFilename
     * @param string $folder
     * @return array{signedUploadUrl:string,publicUrl:string,token:string,path:string}|false
     */
    public static function createSignedUploadUrl($originalFilename, $folder = 'scenes') {
        if (!self::isEnabled()) return false;

        $url    = rtrim(self::url(), '/');
        $key    = self::key();
        $bucket = self::bucket();

        // Sanitize ext and build a unique remote path
        $ext = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));
        $ext = preg_replace('/[^a-z0-9]/', '', $ext) ?: 'jpg';
        $remoteName = uniqid('', true) . '_' . time() . '.' . $ext;
        $remotePath = trim($folder, '/') . '/' . $remoteName;

        // Ask Supabase for a signed URL
        $signEndpoint = $url . '/storage/v1/object/upload/sign/' . rawurlencode($bucket) . '/' . $remotePath;

        $ch = curl_init($signEndpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $key,
                'apikey: ' . $key,
                'Content-Type: application/json',
                'Content-Length: 0',
            ],
            CURLOPT_TIMEOUT => 20,
        ]);
        $resp     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300) {
            error_log("Supabase signed-upload-url failed [$httpCode]: $resp");
            return ['__error' => 'HTTP ' . $httpCode . ': ' . substr($resp, 0, 400), '__endpoint' => $signEndpoint];
        }

        $data = json_decode($resp, true);
        if (empty($data['url'])) {
            error_log("Supabase signed-upload-url malformed response: $resp");
            return ['__error' => 'No url in response: ' . substr($resp, 0, 400), '__endpoint' => $signEndpoint];
        }

        // Supabase returns a relative URL like:
        //   /object/upload/sign/{bucket}/{path}?token=...
        // We need to prefix with the Supabase domain + /storage/v1
        $relativeUrl = $data['url'];
        if (strncmp($relativeUrl, 'http', 4) !== 0) {
            $relativeUrl = $url . '/storage/v1' . $relativeUrl;
        }

        return [
            'signedUploadUrl' => $relativeUrl,
            'publicUrl'       => $url . '/storage/v1/object/public/' . rawurlencode($bucket) . '/' . $remotePath,
            'token'           => $data['token'] ?? '',
            'path'            => $remotePath,
        ];
    }

    /**
     * Delete a file from Supabase Storage by its full public URL
     *
     * @param string $publicUrl
     * @return bool
     */
    public static function deleteByUrl($publicUrl) {
        if (!self::isEnabled() || empty($publicUrl)) {
            return false;
        }

        $url    = rtrim(self::url(), '/');
        $key    = self::key();
        $bucket = self::bucket();

        $marker = '/storage/v1/object/public/' . rawurlencode($bucket) . '/';
        $pos = strpos($publicUrl, $marker);
        if ($pos === false) return false;

        $remotePath = substr($publicUrl, $pos + strlen($marker));
        $endpoint = $url . '/storage/v1/object/' . rawurlencode($bucket) . '/' . $remotePath;

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $key,
                'apikey: ' . $key,
            ],
            CURLOPT_TIMEOUT        => 30,
        ]);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode >= 200 && $httpCode < 300;
    }
}
