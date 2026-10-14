<?php

declare(strict_types=1);

namespace EduSphere\Core;

class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    public static function make(array $data, array $rules): self
    {
        $validator = new self($data, $rules);
        $validator->validate();
        return $validator;
    }

    public function validate(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $fieldRules) {
            $rulesList = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;
            $value = $this->data[$field] ?? null;

            foreach ($rulesList as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                if ($rule === 'required' && (empty($value) && $value !== '0' && $value !== 0)) {
                    $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . " is required.");
                    break;
                }

                if ($value !== null && $value !== '') {
                    if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $this->addError($field, "Please enter a valid email address.");
                    } elseif ($rule === 'numeric' && !is_numeric($value)) {
                        $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . " must be a number.");
                    } elseif ($rule === 'min' && strlen((string)$value) < (int)$params[0]) {
                        $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . " must have at least {$params[0]} characters.");
                    } elseif ($rule === 'max' && strlen((string)$value) > (int)$params[0]) {
                        $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . " cannot exceed {$params[0]} characters.");
                    } elseif ($rule === 'in' && !in_array($value, $params, true)) {
                        $this->addError($field, "Selected value is invalid.");
                    }
                }
            }
        }

        return empty($this->errors);
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        if (empty($this->errors)) {
            return null;
        }
        $firstField = array_key_first($this->errors);
        return $this->errors[$firstField][0] ?? null;
    }
}
