<?php
// app/Core/Validator.php
declare(strict_types=1);

namespace App\Core;

class Validator
{
    private array $errors = [];
    private array $data   = [];

    public function __construct(array $input)
    {
        // Strip all keys and values defensively
        foreach ($input as $k => $v) {
            $this->data[self::sanitizeKey($k)] = is_string($v) ? trim($v) : $v;
        }
    }

    // ── Rules ───────────────────────────────────

    public function required(string $field, string $label = ''): static
    {
        $label = $label ?: ucfirst($field);
        if (empty($this->data[$field]) && $this->data[$field] !== '0') {
            $this->errors[$field] = "{$label} is required.";
        }
        return $this;
    }

    public function email(string $field): static
    {
        if (!empty($this->data[$field]) &&
            !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = 'Invalid email address.';
        }
        return $this;
    }

    public function min(string $field, int $min): static
    {
        if (!empty($this->data[$field]) && mb_strlen((string)$this->data[$field]) < $min) {
            $this->errors[$field] = "Must be at least {$min} characters.";
        }
        return $this;
    }

    public function max(string $field, int $max): static
    {
        if (!empty($this->data[$field]) && mb_strlen((string)$this->data[$field]) > $max) {
            $this->errors[$field] = "Must not exceed {$max} characters.";
        }
        return $this;
    }

    public function integer(string $field): static
    {
        if (!empty($this->data[$field]) &&
            filter_var($this->data[$field], FILTER_VALIDATE_INT) === false) {
            $this->errors[$field] = 'Must be a whole number.';
        }
        return $this;
    }

    public function range(string $field, int $min, int $max): static
    {
        $val = (int)($this->data[$field] ?? 0);
        if ($val < $min || $val > $max) {
            $this->errors[$field] = "Must be between {$min} and {$max}.";
        }
        return $this;
    }

    public function in(string $field, array $allowed): static
    {
        if (!empty($this->data[$field]) && !in_array($this->data[$field], $allowed, true)) {
            $this->errors[$field] = 'Invalid value selected.';
        }
        return $this;
    }

    // ── Results ─────────────────────────────────

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Get sanitized value — strips tags and encodes HTML entities.
     */
    public function get(string $field, mixed $default = null): mixed
    {
        $val = $this->data[$field] ?? $default;
        return is_string($val) ? htmlspecialchars(strip_tags($val), ENT_QUOTES, 'UTF-8') : $val;
    }

    /**
     * Get raw value (for passwords — do NOT escape before hashing).
     */
    public function raw(string $field): mixed
    {
        return $this->data[$field] ?? null;
    }

    // ── Helpers ─────────────────────────────────

    public static function sanitizeKey(string $key): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $key);
    }

    public static function e(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
