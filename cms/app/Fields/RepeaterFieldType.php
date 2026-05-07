<?php

declare(strict_types=1);

namespace CometCMS\Fields;

final class RepeaterFieldType extends AbstractFieldType
{
    public function validate(mixed $value, array $config, array $context = []): array
    {
        $required = parent::validate($value, $config, $context);

        if (($required['valid'] ?? false) === false || $this->isEmpty($value)) {
            return $required;
        }

        if (!is_array($value)) {
            return $this->error('Repeater value must be an array of rows.', 'invalid_repeater');
        }

        $subfields = $this->subfieldsByKey($config);

        foreach (array_values($value) as $rowIndex => $row) {
            if (!is_array($row)) {
                return $this->error("Row {$rowIndex} is not a valid object.", 'invalid_repeater_row');
            }

            foreach ($subfields as $key => $subfield) {
                $type = $this->fieldRegistry()->get((string) ($subfield['type'] ?? 'text'));
                $result = $type->validate($row[$key] ?? null, $subfield, $context);

                if (($result['valid'] ?? false) === false) {
                    return $this->error(
                        "Row " . ($rowIndex + 1) . ": \"{$key}\": " . (string) ($result['message'] ?? 'Invalid value.'),
                        (string) ($result['code'] ?? 'invalid_subfield')
                    );
                }
            }
        }

        return ['valid' => true];
    }

    public function normalize(mixed $value, array $config, array $context = []): mixed
    {
        if (!is_array($value)) {
            return [];
        }

        $subfieldsByKey = $this->subfieldsByKey($config);

        return array_values(array_map(function (mixed $row) use ($subfieldsByKey): array {
            if (!is_array($row)) {
                return [];
            }

            // Only keep declared subfield keys in the stored row
            $clean = [];

            foreach ($subfieldsByKey as $key => $subfield) {
                $clean[$key] = $this->normalizeSubfield($row[$key] ?? null, $subfield);
            }

            return $clean;
        }, $value));
    }

    public function renderInput(string $name, mixed $value, array $config, array $context = []): string
    {
        return '<div data-repeater="' . htmlspecialchars($name, ENT_QUOTES) . '">(repeater)</div>';
    }

    private function normalizeSubfield(mixed $value, array $config): mixed
    {
        return $this->fieldRegistry()->get((string) ($config['type'] ?? 'text'))->normalize($value, $config);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function subfieldsByKey(array $config): array
    {
        $subfieldsByKey = [];

        foreach (is_array($config['subfields'] ?? null) ? $config['subfields'] : [] as $subfield) {
            if (!is_array($subfield)) {
                continue;
            }

            $key = (string) ($subfield['key'] ?? '');

            if ($key !== '') {
                $subfieldsByKey[$key] = $subfield;
            }
        }

        return $subfieldsByKey;
    }

    private function fieldRegistry(): FieldRegistry
    {
        static $registry = null;

        if (!$registry instanceof FieldRegistry) {
            $registry = FieldRegistry::builtins();
        }

        return $registry;
    }
}
