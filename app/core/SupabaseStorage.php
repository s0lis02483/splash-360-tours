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
        return $_ENV['SUPABASE_URL'] ?? getenv('SUPABASE_URL') ?? '';
    }

    private static function key() {
        return $_ENV['SUPABASE_SERVICE_KEY'] ?? getenv('SUPABASE_SERVICE_KEY') ?? '';
    }

    private static function bucket() {
        $b = $_ENV['SUPABASE_BUCKET'] ?? getenv('SUPABASE_BUCKET');
        return $b ?: 'tour-images';
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
