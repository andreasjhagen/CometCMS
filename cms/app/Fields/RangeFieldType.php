<?php

declare(strict_types=1);

namespace CometCMS\Fields;

use CometCMS\Core\Security;

final class RangeFieldType extends AbstractFieldType
{
    public function validate(mixed $value, array $config, array $context = []): array
    {
        $required = parent::validate($value, $config, $context);
        $value = $this->isEmpty($value) ? ($config['default'] ?? null) : $value;
        $step = $this->configNumber($config, 'step');

        if ($step !== null && $step <= 0) {
            return $this->error('Step must be greater than 0.', 'invalid_step');
        }

        if (($required['valid'] ?? false) === false && $this->isEmpty($value)) {
            return $required;
        }

        if ($this->isEmpty($value)) {
            return ['valid' => true];
        }

        if (!is_numeric($value)) {
            return $this->error('Enter a valid number.', 'invalid_number');
        }

        $number = (float) $value;
        $min = $this->configNumber($config, 'min');
        $max = $this->configNumber($config, 'max');

        if ($min !== null && $number < $min) {
            return $this->error('Enter a value greater than or equal to ' . $this->formatNumber($min) . '.', 'range_min');
        }

        if ($max !== null && $number > $max) {
            return $this->error('Enter a value less than or equal to ' . $this->formatNumber($max) . '.', 'range_max');
        }

        return ['valid' => true];
    }

    public function normalize(mixed $value, array $config, array $context = []): mixed
    {
        if ($this->isEmpty($value)) {
            $value = $config['default'] ?? null;
        }

        if ($this->isEmpty($value)) {
            return null;
        }

        return str_contains((string) $value, '.') ? (float) $value : (int) $value;
    }

    public function renderInput(string $name, mixed $value, array $config, array $context = []): string
    {
        $value = $this->normalize($value, $config, $context);
        $attrs = [
            'type' => 'range',
            'name' => $name,
            'value' => (string) ($value ?? $this->configNumber($config, 'min') ?? 0),
            'step' => $this->formatNumber($this->configStep($config)),
        ];

        foreach (['min', 'max'] as $key) {
            $number = $this->configNumber($config, $key);

            if ($number !== null) {
                $attrs[$key] = $this->formatNumber($number);
            }
        }

        $html = '<input';

        foreach ($attrs as $key => $attrValue) {
            $html .= ' ' . $key . '="' . Security::e($attrValue) . '"';
        }

        return $html . '>';
    }

    private function configNumber(array $config, string $key): ?float
    {
        return is_numeric($config[$key] ?? null) ? (float) $config[$key] : null;
    }

    private function configStep(array $config): float
    {
        $step = $this->configNumber($config, 'step');

        return $step !== null && $step > 0 ? $step : 1.0;
    }

    private function formatNumber(float $value): string
    {
        return rtrim(rtrim(sprintf('%.12F', $value), '0'), '.');
    }
}
