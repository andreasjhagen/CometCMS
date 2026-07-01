<?php

declare(strict_types=1);

function comet_api_controller_test_bootstrap_require_snippet(): string
{
    return 'require ' . var_export(__DIR__ . '/bootstrap.php', true) . ';';
}

test('singleton content collection route returns the fixed entry', function (): void {
    $output = comet_test_run_php(['-r',
        comet_api_controller_test_bootstrap_require_snippet() .
        'comet_test_reset_storage();' .
        '(new \\CometCMS\\Content\\ContentTypeRepository())->save(["name" => "homepage", "singleton" => true]);' .
        '\\CometCMS\\Content\\ContentRepository::make()->save("homepage", ["title" => "Home", "status" => "published"], ["id" => "admin"]);' .
        '$_SERVER["REQUEST_METHOD"] = "GET";' .
        '$_SERVER["REQUEST_URI"] = "/api/v1/workspaces/default/content/homepage";' .
        '$_SERVER["SCRIPT_NAME"] = "/index.php";' .
        '(new \\CometCMS\\Controllers\\ApiController(new \\CometCMS\\Core\\Http()))->useWorkspace("default")->contentIndex("homepage");'
    ]);

    assert_true(str_contains($output, '"data": {'));
    assert_true(str_contains($output, '"slug": "homepage"'));
    assert_false(str_contains($output, '"meta": {'));
});
