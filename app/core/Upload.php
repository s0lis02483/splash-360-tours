<?php
// FILE: /app/core/Upload.php

/**
 * Upload Class
 *
 * Handles file uploads securely
 */
class Upload {
    private $file;
    private $errors = [];
    private $allowedTypes = [];
    private $maxSize = 10485760; // 10MB default
    private $uploadPath = '';

    /**
     * Constructor
     *
     * @param array $file $_FILES array
     */
    public function __construct($file = null) {
        $this->file = $file;
    }

    /**
     * Set allowed file types
     *
     * @param array $types Allowed MIME types or extensions
     * @return Upload
     */
    public function setAllowedTypes($types) {
        $this->allowedTypes = $types;
        return $this;
    }

    /**
     * Set maximum file size in bytes
     *
     * @param int $size Maximum size
     * @return Upload
     */
    public function setMaxSize($size) {
        $this->maxSize = $size;
        return $this;
    }

    /**
     * Set upload path
     *
     * @param string $path Upload directory path
     * @return Upload
     */
    public function setUploadPath($path) {
        $this->uploadPath = rtrim($path, '/');
        return $this;
    }

    /**
     * Validate uploaded file
     *
     * @return bool
     */
    public function validate() {
        if (!$this->file || !isset($this->file['tmp_name'])) {
            $this->errors[] = 'No file uploaded';
            return false;
        }

        if ($this->file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = 'File upload error: ' . $this->getUploadErrorMessage($this->file['error']);
            return false;
        }

        // Check file size
        if ($this->file['size'] > $this->maxSize) {
            $maxSizeMB = $this->maxSize / 1048576;
            $this->errors[] = "File size must not exceed {$maxSizeMB}MB";
            return false;
        }

        // Check file type
        if (!empty($this->allowedTypes)) {
            $extension = strtolower(pathinfo($this->file['name'], PATHINFO_EXTENSION));
            $mimeType = mime_content_type($this->file['tmp_name']);

            $typeAllowed = in_array($extension, $this->allowedTypes) ||
                          in_array($mimeType, $this->allowedTypes);

            if (!$typeAllowed) {
                $this->errors[] = 'File type not allowed. Allowed types: ' . implode(', ', $this->allowedTypes);
                return false;
            }
        }

        // Check for valid image if it's supposed to be an image
        $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower(pathinfo($this->file['name'], PATHINFO_EXTENSION));

        if (in_array($extension, $imageTypes)) {
            $imageInfo = @getimagesize($this->file['tmp_name']);
            if ($imageInfo === false) {
                $this->errors[] = 'Invalid image file';
                return false;
            }
        }

        return true;
    }

    /**
     * Upload file
     *
     * @param string $newName Optional new filename
     * @return string|false Uploaded filename or false
     */
    public function upload($newName = null) {
        if (!$this->validate()) {
            return false;
        }

        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }

        // Generate unique filename
        if ($newName) {
            $filename = $newName;
        } else {
            $extension = pathinfo($this->file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $extension;
        }

        // Sanitize filename
        $filename = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $filename);

        $destination = $this->uploadPath . '/' . $filename;

        // Prevent directory traversal
        $realUploadPath = realpath($this->uploadPath);
        $realDestination = realpath(dirname($destination)) . '/' . basename($destination);

        if (strpos($realDestination, $realUploadPath) !== 0) {
            $this->errors[] = 'Invalid upload path';
            return false;
        }

        // Move uploaded file
        if (move_uploaded_file($this->file['tmp_name'], $destination)) {
            return $filename;
        }

        $this->errors[] = 'Failed to move uploaded file';
        return false;
    }

    /**
     * Get upload errors
     *
     * @return array
     */
    public function errors() {
        return $this->errors;
    }

    /**
     * Get first error message
     *
     * @return string|null
     */
    public function first() {
        return $this->errors[0] ?? null;
    }

    /**
     * Get upload error message from error code
     *
     * @param int $code Error code
     * @return string
     */
    private function getUploadErrorMessage($code) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'No temporary directory',
            UPLOAD_ERR_CANT_WRITE => 'Cannot write to disk',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by extension',
        ];

        return $errors[$code] ?? 'Unknown error';
    }

    /**
     * Delete uploaded file
     *
     * @param string $filename Filename to delete
     * @return bool
     */
    public function delete($filename) {
        $filepath = $this->uploadPath . '/' . $filename;

        if (file_exists($filepath) && is_file($filepath)) {
            return unlink($filepath);
        }

        return false;
    }
}
