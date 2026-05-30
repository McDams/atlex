<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Validateur de formulaires simple basé sur des règles textuelles.
 *
 * Exemple de règles : 'required|email', 'required|min:3|max:80'.
 */
final class Validator
{
    /** @var array<string,array<int,string>> */
    private array $errors = [];

    /** @var array<string,mixed> */
    private array $data;

    /**
     * @param array<string,mixed> $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Valide les données contre un jeu de règles.
     *
     * @param array<string,string> $rules    champ => règles pipe-séparées
     * @param array<string,string> $messages messages personnalisés "champ.regle"
     */
    public function validate(array $rules, array $messages = []): bool
    {
        foreach ($rules as $field => $ruleString) {
            $value = $this->data[$field] ?? null;

            foreach (explode('|', $ruleString) as $rule) {
                [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);
                $this->applyRule($field, $value, $name, $param, $messages);
            }
        }

        return $this->passes();
    }

    private function applyRule(
        string $field,
        mixed $value,
        string $name,
        ?string $param,
        array $messages
    ): void {
        $isEmpty = $value === null || $value === '';

        switch ($name) {
            case 'required':
                if ($isEmpty) {
                    $this->addError($field, $name, $messages, "Le champ « $field » est obligatoire.");
                }
                break;

            case 'email':
                if (!$isEmpty && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, $name, $messages, "L'adresse email « $field » est invalide.");
                }
                break;

            case 'min':
                if (!$isEmpty && mb_strlen((string) $value) < (int) $param) {
                    $this->addError($field, $name, $messages, "Le champ « $field » doit contenir au moins $param caractères.");
                }
                break;

            case 'max':
                if (!$isEmpty && mb_strlen((string) $value) > (int) $param) {
                    $this->addError($field, $name, $messages, "Le champ « $field » ne doit pas dépasser $param caractères.");
                }
                break;

            case 'numeric':
                if (!$isEmpty && !is_numeric($value)) {
                    $this->addError($field, $name, $messages, "Le champ « $field » doit être numérique.");
                }
                break;

            case 'in':
                $allowed = explode(',', (string) $param);
                if (!$isEmpty && !in_array((string) $value, $allowed, true)) {
                    $this->addError($field, $name, $messages, "La valeur du champ « $field » est invalide.");
                }
                break;
        }
    }

    private function addError(string $field, string $rule, array $messages, string $default): void
    {
        $this->errors[$field][] = $messages["$field.$rule"] ?? $default;
    }

    public function passes(): bool
    {
        return $this->errors === [];
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * @return array<string,array<int,string>>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * @return array<int,string>
     */
    public function flatErrors(): array
    {
        return array_merge(...array_values($this->errors)) ?: [];
    }
}
