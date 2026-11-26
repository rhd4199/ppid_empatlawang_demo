<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'local_public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        // "public" drives every upload in the app (Storage::disk('public'),
        // ->store($path, 'public')). Backed by Cloudflare R2 when
        // CLOUDFLARE_R2_ACCOUNT_ID is set (served from CLOUDFLARE_R2_PUBLIC_URL),
        // else IDCloudHost S3-compatible storage when S3_ENDPOINT is set;
        // otherwise (local dev/preview) or with PUBLIC_DISK_DRIVER=local it
        // falls back to storage/app/public served at /storage (run
        // `php artisan storage:link`). Local: no 'url' key, storage_url()
        // makes it absolute from the current request host.
        // Existing local files → R2/S3: `php artisan storage:push-public`.
        'public' => env('PUBLIC_DISK_DRIVER', env('CLOUDFLARE_R2_ACCOUNT_ID') || env('S3_ENDPOINT') ? 's3' : 'local') === 'local' ? [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ] : (env('CLOUDFLARE_R2_ACCOUNT_ID') ? [
            'driver' => 's3',
            'key' => env('CLOUDFLARE_R2_ACCESS_KEY_ID'),
            'secret' => env('CLOUDFLARE_R2_SECRET_ACCESS_KEY'),
            'region' => 'auto',
            'bucket' => env('CLOUDFLARE_R2_BUCKET_NAME'),
            'endpoint' => 'https://'.env('CLOUDFLARE_R2_ACCOUNT_ID').'.r2.cloudflarestorage.com',
            'url' => env('CLOUDFLARE_R2_PUBLIC_URL'),
            'root' => env('CLOUDFLARE_R2_ROOT_DIR'),
            'use_path_style_endpoint' => true,
            // R2 has no object ACLs: public access comes from the bucket's public domain
            'throw' => false,
            'report' => false,
        ] : [
            'driver' => 's3',
            'key' => env('S3_ACCESS_KEY_ID'),
            'secret' => env('S3_SECRET_ACCESS_KEY'),
            'region' => env('S3_REGION'),
            'bucket' => env('S3_BUCKET_NAME'),
            'endpoint' => env('S3_ENDPOINT'),
            'root' => env('S3_ROOT_DIR'),
            'use_path_style_endpoint' => env('S3_USE_PATH_STYLE_ENDPOINT', true),
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ]),

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
