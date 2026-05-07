<?php

declare(strict_types=1);

use CometCMS\Controllers\Admin\AuthController;
use CometCMS\Controllers\Admin\BackupsController;
use CometCMS\Controllers\Admin\ContentController;
use CometCMS\Controllers\Admin\ContentTypesController;
use CometCMS\Controllers\Admin\DashboardController;
use CometCMS\Controllers\Admin\MediaController;
use CometCMS\Controllers\Admin\RolesController;
use CometCMS\Controllers\Admin\TokensController;
use CometCMS\Controllers\Admin\TrashController;
use CometCMS\Controllers\Admin\UsersController;
use CometCMS\Controllers\Admin\WebhooksController;
use CometCMS\Controllers\AdminController;
use CometCMS\Controllers\ApiController;
use CometCMS\Core\Http;
use CometCMS\Logging\Logger;

require __DIR__ . '/app/bootstrap.php';

$http = new Http();
$admin = new AdminController($http);
$api = new ApiController($http);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = $http->path();

try {
    if ($path === '/' && $method === 'GET') {
        $http->redirect($http->url('/admin'));
    }

    // -------------------------------------------------------------------------
    // Admin API routes  (session-auth JSON endpoints for the Vue SPA)
    // -------------------------------------------------------------------------

    // Auth
    if ($path === '/admin/api/me' && $method === 'GET') {
        (new AuthController($http))->me();
    }

    if ($path === '/admin/api/login' && $method === 'POST') {
        (new AuthController($http))->login();
    }

    if ($path === '/admin/api/logout' && $method === 'POST') {
        (new AuthController($http))->logout();
    }

    if ($path === '/admin/api/setup' && $method === 'POST') {
        (new AuthController($http))->setup();
    }

    // Dashboard
    if ($path === '/admin/api/app' && $method === 'GET') {
        (new DashboardController($http))->appInfo();
    }

    if ($path === '/admin/api/update' && $method === 'GET') {
        (new DashboardController($http))->updateStatus();
    }

    if ($path === '/admin/api/update/check' && $method === 'POST') {
        (new DashboardController($http))->updateCheck();
    }

    if ($path === '/admin/api/update/download' && $method === 'POST') {
        (new DashboardController($http))->updateDownload();
    }

    if ($path === '/admin/api/update/install' && $method === 'POST') {
        (new DashboardController($http))->updateInstall();
    }

    if ($path === '/admin/api/dashboard' && $method === 'GET') {
        (new DashboardController($http))->dashboard();
    }

    if ($path === '/admin/api/activity' && $method === 'GET') {
        (new DashboardController($http))->activityLog();
    }

    // Content types
    if ($path === '/admin/api/content-types' && $method === 'GET') {
        (new ContentTypesController($http))->index();
    }

    if ($path === '/admin/api/content-types' && $method === 'POST') {
        (new ContentTypesController($http))->store();
    }

    if ($path === '/admin/api/content-types/order' && $method === 'PATCH') {
        (new ContentTypesController($http))->reorder();
    }

    if (preg_match('#^/admin/api/content-types/([A-Za-z0-9_-]+)$#', $path, $m)) {
        if ($method === 'GET') {
            (new ContentTypesController($http))->show($m[1]);
        }
        if ($method === 'PUT') {
            (new ContentTypesController($http))->update($m[1]);
        }
        if ($method === 'DELETE') {
            (new ContentTypesController($http))->destroy($m[1]);
        }
    }

    // Content
    if (preg_match('#^/admin/api/content/([A-Za-z0-9_-]+)$#', $path, $m)) {
        if ($method === 'GET') {
            (new ContentController($http))->index($m[1]);
        }
        if ($method === 'POST') {
            (new ContentController($http))->store($m[1]);
        }
    }

    if (preg_match('#^/admin/api/content/([A-Za-z0-9_-]+)/bulk$#', $path, $m) && $method === 'PATCH') {
        (new ContentController($http))->bulkUpdate($m[1]);
    }

    if (preg_match('#^/admin/api/content/([A-Za-z0-9_-]+)/bulk$#', $path, $m) && $method === 'DELETE') {
        (new ContentController($http))->bulkDelete($m[1]);
    }

    if (preg_match('#^/admin/api/content/([A-Za-z0-9_-]+)/([A-Za-z0-9_-]+)$#', $path, $m)) {
        if ($method === 'GET') {
            (new ContentController($http))->show($m[1], $m[2]);
        }
        if ($method === 'PUT') {
            (new ContentController($http))->update($m[1], $m[2]);
        }
        if ($method === 'DELETE') {
            (new ContentController($http))->destroy($m[1], $m[2]);
        }
    }

    if (preg_match('#^/admin/api/content/([A-Za-z0-9_-]+)/([A-Za-z0-9_-]+)/duplicate$#', $path, $m) && $method === 'POST') {
        (new ContentController($http))->duplicate($m[1], $m[2]);
    }

    if (preg_match('#^/admin/api/content/([A-Za-z0-9_-]+)/([A-Za-z0-9_-]+)/revisions$#', $path, $m) && $method === 'GET') {
        (new ContentController($http))->revisions($m[1], $m[2]);
    }

    if (preg_match('#^/admin/api/content/([A-Za-z0-9_-]+)/([A-Za-z0-9_-]+)/revisions/([A-Za-z0-9_-]+)/restore$#', $path, $m) && $method === 'POST') {
        (new ContentController($http))->revisionRestore($m[1], $m[2], $m[3]);
    }

    if (preg_match('#^/admin/api/content/([A-Za-z0-9_-]+)/([A-Za-z0-9_-]+)/translations/([A-Za-z0-9_-]+)$#', $path, $m) && $method === 'DELETE') {
        (new ContentController($http))->destroyTranslation($m[1], $m[2], $m[3]);
    }

    // Trash
    if (preg_match('#^/admin/api/trash/([A-Za-z0-9_-]+)$#', $path, $m) && $method === 'GET') {
        (new TrashController($http))->index($m[1]);
    }

    if (preg_match('#^/admin/api/trash/([A-Za-z0-9_-]+)$#', $path, $m) && $method === 'DELETE') {
        (new TrashController($http))->empty($m[1]);
    }

    if (preg_match('#^/admin/api/trash/([A-Za-z0-9_-]+)/([A-Za-z0-9_-]+)/restore$#', $path, $m) && $method === 'POST') {
        (new TrashController($http))->restore($m[1], $m[2]);
    }

    if (preg_match('#^/admin/api/trash/([A-Za-z0-9_-]+)/([A-Za-z0-9_-]+)$#', $path, $m) && $method === 'DELETE') {
        (new TrashController($http))->purge($m[1], $m[2]);
    }

    // Media
    if ($path === '/admin/api/media' && $method === 'GET') {
        (new MediaController($http))->index();
    }

    if ($path === '/admin/api/media/usages' && $method === 'GET') {
        (new MediaController($http))->usages();
    }

    if ($path === '/admin/api/media/thumbnails/regenerate' && $method === 'POST') {
        (new MediaController($http))->regenerateThumbnails();
    }

    if ($path === '/admin/api/media' && $method === 'POST') {
        (new MediaController($http))->store();
    }

    if ($path === '/admin/api/media/categories' && $method === 'POST') {
        (new MediaController($http))->categoryStore();
    }

    if (preg_match('#^/admin/api/media/categories/(.+)$#', $path, $m) && $method === 'PUT') {
        (new MediaController($http))->categoryRename($m[1]);
    }

    if (preg_match('#^/admin/api/media/categories/(.+)$#', $path, $m) && $method === 'DELETE') {
        (new MediaController($http))->categoryDelete($m[1]);
    }

    if ($path === '/admin/api/media/bulk-category' && $method === 'PUT') {
        (new MediaController($http))->bulkCategoryUpdate();
    }

    if ($path === '/admin/api/media/bulk-delete' && $method === 'POST') {
        (new MediaController($http))->bulkDelete();
    }

    if (preg_match('#^/admin/api/media/(.+)/category$#', $path, $m) && $method === 'PUT') {
        (new MediaController($http))->categoryUpdate($m[1]);
    }

    if (preg_match('#^/admin/api/media/(.+)/meta$#', $path, $m) && ($method === 'PUT' || $method === 'PATCH')) {
        (new MediaController($http))->updateMeta($m[1]);
    }

    if (preg_match('#^/admin/api/media/(.+)/visibility$#', $path, $m) && ($method === 'PUT' || $method === 'PATCH')) {
        (new MediaController($http))->updateVisibility($m[1]);
    }

    if ($path === '/admin/api/media/bulk-visibility' && $method === 'PUT') {
        (new MediaController($http))->bulkUpdateVisibility();
    }

    if (preg_match('#^/admin/api/media/(.+)/rename$#', $path, $m) && $method === 'PUT') {
        (new MediaController($http))->rename($m[1]);
    }

    if (preg_match('#^/admin/api/media/(.+)$#', $path, $m) && $method === 'DELETE') {
        (new MediaController($http))->destroy($m[1]);
    }

    // Users
    if ($path === '/admin/api/users' && $method === 'GET') {
        (new UsersController($http))->index();
    }

    if ($path === '/admin/api/users' && $method === 'POST') {
        (new UsersController($http))->store();
    }

    if (preg_match('#^/admin/api/users/([A-Za-z0-9_-]+)$#', $path, $m) && $method === 'DELETE') {
        (new UsersController($http))->destroy($m[1]);
    }

    if (preg_match('#^/admin/api/users/([A-Za-z0-9_-]+)$#', $path, $m) && $method === 'PUT') {
        (new UsersController($http))->update($m[1]);
    }

    // API tokens
    if ($path === '/admin/api/tokens' && $method === 'GET') {
        (new TokensController($http))->index();
    }

    if ($path === '/admin/api/tokens' && $method === 'POST') {
        (new TokensController($http))->store();
    }

    if (preg_match('#^/admin/api/tokens/([A-Za-z0-9_-]+)$#', $path, $m) && $method === 'DELETE') {
        (new TokensController($http))->destroy($m[1]);
    }

    // Roles
    if ($path === '/admin/api/roles' && $method === 'GET') {
        (new RolesController($http))->index();
    }

    if ($path === '/admin/api/roles' && $method === 'POST') {
        (new RolesController($http))->store();
    }

    if (preg_match('#^/admin/api/roles/([A-Za-z0-9_-]+)$#', $path, $m) && $method === 'PUT') {
        (new RolesController($http))->update($m[1]);
    }

    if (preg_match('#^/admin/api/roles/([A-Za-z0-9_-]+)$#', $path, $m) && $method === 'DELETE') {
        (new RolesController($http))->destroy($m[1]);
    }

    // Backups
    if ($path === '/admin/api/backups' && $method === 'GET') {
        (new BackupsController($http))->index();
    }

    if ($path === '/admin/api/backups' && $method === 'POST') {
        (new BackupsController($http))->store();
    }

    if ($path === '/admin/api/backups/upload' && $method === 'POST') {
        (new BackupsController($http))->upload();
    }

    if (preg_match('#^/admin/api/backups/([A-Za-z0-9_.-]+\.zip)/inspect$#', $path, $m) && $method === 'GET') {
        (new BackupsController($http))->inspect($m[1]);
    }

    if (preg_match('#^/admin/api/backups/([A-Za-z0-9_.-]+\.zip)/restore$#', $path, $m) && $method === 'POST') {
        (new BackupsController($http))->restore($m[1]);
    }

    if (preg_match('#^/admin/api/backups/([A-Za-z0-9_.-]+\.zip)/note$#', $path, $m) && $method === 'PUT') {
        (new BackupsController($http))->note($m[1]);
    }

    if (preg_match('#^/admin/api/backups/([A-Za-z0-9_.-]+\.zip)/download$#', $path, $m) && $method === 'GET') {
        (new BackupsController($http))->download($m[1]);
    }

    if (preg_match('#^/admin/api/backups/([A-Za-z0-9_.-]+\.zip)$#', $path, $m) && $method === 'DELETE') {
        (new BackupsController($http))->destroy($m[1]);
    }

    // Webhooks
    if ($path === '/admin/api/webhooks' && $method === 'GET') {
        (new WebhooksController($http))->index();
    }

    if ($path === '/admin/api/webhooks' && $method === 'PUT') {
        (new WebhooksController($http))->update();
    }

    if ($path === '/admin/api/webhooks/run' && $method === 'POST') {
        (new WebhooksController($http))->run();
    }

    // Profile & avatar
    if ($path === '/admin/api/profile/avatar' && $method === 'POST') {
        (new UsersController($http))->avatarUpload();
    }

    if ($path === '/admin/api/profile/avatar' && $method === 'DELETE') {
        (new UsersController($http))->avatarDelete();
    }

    if ($path === '/admin/api/profile' && $method === 'PUT') {
        (new UsersController($http))->profileUpdate();
    }

    if (preg_match('#^/admin/api/users/([A-Za-z0-9_-]+)/avatar$#', $path, $m) && $method === 'GET') {
        (new UsersController($http))->avatarServe($m[1]);
    }

    // -------------------------------------------------------------------------
    // Admin SPA shell — serve Vue app for all /admin/* HTML routes
    // -------------------------------------------------------------------------

    if ($path === '/admin' || str_starts_with($path, '/admin/')) {
        $admin->shell();
    }

    if ($path === '/api/v1/health' && $method === 'GET') {
        $api->health();
    }

    if ($path === '/api/v1/content-types' && $method === 'GET') {
        $api->contentTypes();
    }

    if ($path === '/api/v1/content-types' && $method === 'POST') {
        $api->contentTypeStore();
    }

    if (preg_match('#^/api/v1/content-types/([A-Za-z0-9_-]+)$#', $path, $m) && $method === 'GET') {
        $api->contentTypeShow($m[1]);
    }

    if (preg_match('#^/api/v1/content-types/([A-Za-z0-9_-]+)$#', $path, $m) && $method === 'PUT') {
        $api->contentTypeUpdate($m[1]);
    }

    if (preg_match('#^/api/v1/content-types/([A-Za-z0-9_-]+)$#', $path, $m) && $method === 'DELETE') {
        $api->contentTypeDelete($m[1]);
    }

    if (preg_match('#^/api/v1/content/([A-Za-z0-9_-]+)$#', $path, $m)) {
        if ($method === 'GET') {
            $api->contentIndex($m[1]);
        }

        if ($method === 'POST') {
            $api->contentStore($m[1]);
        }
    }

    if (preg_match('#^/api/v1/content/([A-Za-z0-9_-]+)/([A-Za-z0-9_-]+)$#', $path, $m)) {
        if ($method === 'GET') {
            $api->contentShow($m[1], $m[2]);
        }

        if ($method === 'PUT' || $method === 'PATCH') {
            $api->contentUpdate($m[1], $m[2]);
        }

        if ($method === 'DELETE') {
            $api->contentDelete($m[1], $m[2]);
        }
    }

    if ($path === '/api/v1/media' && $method === 'GET') {
        $api->mediaIndex();
    }

    if ($path === '/api/v1/media' && $method === 'POST') {
        $api->mediaStore();
    }

    if ($path === '/api/v1/media/categories' && $method === 'POST') {
        $api->mediaCategoryStore();
    }

    if (preg_match('#^/api/v1/media/categories/(.+)$#', $path, $m) && ($method === 'PUT' || $method === 'PATCH')) {
        $api->mediaCategoryRename($m[1]);
    }

    if (preg_match('#^/api/v1/media/categories/(.+)$#', $path, $m) && $method === 'DELETE') {
        $api->mediaCategoryDelete($m[1]);
    }

    if (preg_match('#^/api/v1/media/(.+)/category$#', $path, $m) && ($method === 'PUT' || $method === 'PATCH')) {
        $api->mediaCategoryUpdate($m[1]);
    }

    if (preg_match('#^/api/v1/media/(.+)/meta$#', $path, $m) && ($method === 'PUT' || $method === 'PATCH')) {
        $api->mediaUpdateMeta($m[1]);
    }

    if (preg_match('#^/api/v1/media/(.+)/visibility$#', $path, $m) && ($method === 'PUT' || $method === 'PATCH')) {
        $api->mediaUpdateVisibility($m[1]);
    }

    if ($path === '/api/v1/media/bulk-visibility' && $method === 'PUT') {
        $api->mediaBulkUpdateVisibility();
    }

    if (preg_match('#^/api/v1/media/(.+)$#', $path, $m) && $method === 'DELETE') {
        $api->mediaDelete($m[1]);
    }

    if (preg_match('#^/media-thumbs/(.+)$#', $path, $m) && $method === 'GET') {
        $api->mediaThumbShow($m[1]);
    }

    if (preg_match('#^/media/(.+)$#', $path, $m) && $method === 'GET') {
        $api->mediaShow($m[1]);
    }

    $http->notFound();
} catch (Throwable $e) {
    (new Logger())->error('server_error', [
        'message' => $e->getMessage(),
        'type' => $e::class,
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'path' => $path,
        'method' => $method,
    ]);
    $message = (bool) comet_config('app.debug', false) ? $e->getMessage() : 'An internal server error occurred.';
    $http->json(['error' => ['code' => 'server_error', 'message' => $message]], 500);
}
