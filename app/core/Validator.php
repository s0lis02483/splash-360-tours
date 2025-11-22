<?php
// FILE: /app/core/Validator.php

/**
 * Validator Class
 *
 * Handles input validation
 */
class Validator {
    private $errors = [];
    private $data = [];

    /**
     * Constructor
     *
     * @param array $data Data to validate
     */
    public function __construct($data = []) {
        $this->data = $data;
    }

    /**
     * Validate required field
     *
     * @param string $field Field name
     * @param string $message Error message
     * @return Validator
     */
    public function required($field, $message = null) {
        $value = $this->data[$field] ?? null;

        if (empty($value) && $value !== '0') {
            $message = $message ?? ucfirst($field) . ' is required';
            $this->errors[$field][] = $message;
        }

        return $this;
    }

    /**
     * Validate email format
     *
     * @param string $field Field name
     * @param string $message Error message
     * @return Validator
     */
    public function email($field, $message = null) {
        $value = $this->data[$field] ?? null;

        if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $message = $message ?? ucfirst($field) . ' must be a valid email address';
            $this->errors[$field][] = $message;
        }

        return $this;
    }

    /**
     * Validate minimum length
     *
     * @param string $field Field name
     * @param int $min Minimum length
     * @param string $message Error message
     * @return Validator
     */
    public function min($field, $min, $message = null) {
        $value = $this->data[$field] ?? null;

        if ($value && strlen($value) < $min) {
            $message = $message ?? ucfirst($field) . " must be at least $min characters";
            $this->errors[$field][] = $message;
        }

        return $this;
    }

    /**
     * Validate maximum length
     *
     * @param string $field Field name
     * @param int $max Maximum length
     * @param string $message Error message
     * @return Validator
     */
    public function max($field, $max, $message = null) {
        $value = $this->data[$field] ?? null;

        if ($value && strlen($value) > $max) {
            $message = $message ?? ucfirst($field) . " must not exceed $max characters";
            $this->errors[$field][] = $message;
        }

        return $this;
    }

    /**
     * Validate numeric value
     *
     * @param string $field Field name
     * @param string $message Error message
     * @return Validator
     */
    public function numeric($field, $message = null) {
        $value = $this->data[$field] ?? null;

        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $message = $message ?? ucfirst($field) . ' must be a number';
            $this->errors[$field][] = $message;
        }

        return $this;
    }

    /**
     * Validate value is in list
     *
     * @param string $field Field name
     * @param array $options Valid options
     * @param string $message Error message
     * @return Validator
     */
    public function in($field, $options, $message = null) {
        $value = $this->data[$field] ?? null;

        if ($value && !in_array($value, $options)) {
            $message = $message ?? ucfirst($field) . ' must be one of: ' . implode(', ', $options);
            $this->errors[$field][] = $message;
        }

        return $this;
    }

    /**
     * Validate field matches another field
     *
     * @param string $field Field name
     * @param string $matchField Field to match
     * @param string $message Error message
     * @return Validator
     */
    public function match($field, $matchField, $message = null) {
        $value = $this->data[$field] ?? null;
        $matchValue = $this->data[$matchField] ?? null;

        if ($value !== $matchValue) {
            $message = $message ?? ucfirst($field) . ' must match ' . ucfirst($matchField);
            $this->errors[$field][] = $message;
        }

        return $this;
    }

    /**
     * Validate unique value in database
     *
     * @param string $field Field name
     * @param string $table Table name
     * @param string $column Column name
     * @param int $excludeId ID to exclude (for updates)
     * @param string $message Error message
     * @return Validator
     */
    public function unique($field, $table, $column = null, $excludeId = null, $message = null) {
        $value = $this->data[$field] ?? null;
        $column = $column ?? $field;

        if ($value) {
            $db = Database::getInstance()->getConnection();

            $sql = "SELECT COUNT(*) as count FROM $table WHERE $column = ?";
            $params = [$value];

            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();

            if ($result['count'] > 0) {
                $message = $message ?? ucfirst($field) . ' already exists';
                $this->errors[$field][] = $message;
            }
        }

        return $this;
    }

    /**
     * Check if validation passed
     *
     * @return bool
     */
    public function passes() {
        return empty($this->errors);
    }

    /**
     * Check if validation failed
     *
     * @return bool
     */
    public function fails() {
        return !$this->passes();
    }

    /**
     * Get validation errors
     *
     * @return array
     */
    public function errors() {
        return $this->errors;
    }

    /**
     * Get first error message
     *
     * @param string $field Field name
     * @return string|null
     */
    public function first($field = null) {
        if ($field) {
            return $this->errors[$field][0] ?? null;
        }

        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0] ?? null;
        }

        return null;
    }
}
