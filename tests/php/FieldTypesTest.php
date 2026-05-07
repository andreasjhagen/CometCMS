<?php

declare(strict_types=1);

use CometCMS\Fields\FieldRegistry;
use CometCMS\Fields\NumberFieldType;
use CometCMS\Fields\SelectFieldType;

test('field registry exposes built-in field types', function (): void {
    $registry = FieldRegistry::builtins();

    assert_same(
        [
            'text',
            'textarea',
            'markdown',
            'number',
            'range',
            'boolean',
            'select',
            'date',
            'datetime',
            'media',
            'slug',
            'json',
            'relation',
            'repeater',
            'color',
        ],
        $registry->names()
    );
});

test('number fields validate and normalize numeric input', function (): void {
    $field = new NumberFieldType();

    assert_true($field->validate('42.5', [])['valid']);
    assert_false($field->validate('forty-two', [])['valid']);
    assert_same(42.5, $field->normalize('42.5', []));
    assert_same(42, $field->normalize('42', []));
    assert_null($field->normalize('', []));
});

test('select fields enforce configured options', function (): void {
    $field = new SelectFieldType();

    assert_true($field->validate('draft', ['options' => ['draft', 'published']])['valid']);
    assert_false($field->validate('archived', ['options' => ['draft', 'published']])['valid']);
});

test('multi-select fields normalize unique non-empty values', function (): void {
    $field = new SelectFieldType();

    assert_same(['news', 'launch'], $field->normalize([' news ', '', 'launch', 'news'], ['multiple' => true]));
});
