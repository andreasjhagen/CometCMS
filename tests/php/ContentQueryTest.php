<?php

declare(strict_types=1);

use CometCMS\Content\ContentQuery;
use CometCMS\Content\ContentTypeRepository;

test('content query helper searches configured text fields', function (): void {
    $types = new ContentTypeRepository();
    $types->save([
        'name' => 'posts',
        'fields' => [
            'summary' => ['type' => 'textarea'],
            'views' => ['type' => 'number'],
        ],
    ]);

    $entries = [
        ['id' => 'alpha', 'title' => 'Alpha', 'summary' => 'Public guide'],
        ['id' => 'beta', 'title' => 'Beta', 'summary' => 'Draft note'],
    ];

    assert_same(['alpha'], array_column(ContentQuery::applySearch($types, $entries, 'guide', 'posts'), 'id'));
});

test('content query helper filters and sorts values consistently', function (): void {
    $types = new ContentTypeRepository();
    $types->save([
        'name' => 'posts',
        'fields' => [
            'views' => ['type' => 'number'],
            'featured' => ['type' => 'boolean'],
        ],
    ]);

    $entries = [
        ['id' => 'low', 'views' => 2, 'featured' => false],
        ['id' => 'high', 'views' => 10, 'featured' => true],
    ];
    $filters = ContentQuery::filtersFromParams($types, ['filter' => ['views' => ['gte' => 10], 'bad' => 'x']], 'posts');
    $filtered = ContentQuery::applyFilters($entries, $filters);

    assert_same(['high'], array_column($filtered, 'id'));
    assert_true(ContentQuery::compareSortValues('2024-01-01T00:00:00Z', '2024-01-02T00:00:00Z') < 0);
    assert_same('high', ContentQuery::valueAt(['id' => 'slug', 'uid' => 'high'], 'id'));
});
