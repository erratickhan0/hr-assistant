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
    | CV Upload Disk
    |--------------------------------------------------------------------------
    |
    | Resume uploads should use an explicit disk so they do not accidentally
    | depend on the application's default filesystem disk.
    |
    */

    'cv_upload_disk' => env('CV_UPLOAD_DISK', 'cv_uploads'),

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

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            // Prefer an access point ARN here when using S3 Access Points:
            // arn:aws:s3:REGION:ACCOUNT_ID:accesspoint/AP_NAME
            // Otherwise use the bucket name.
            'bucket' => env('AWS_BUCKET_ACCESS_POINT_ARN') ?: env('AWS_BUCKET'),
            // Optional public base URL for Storage::url() (often CloudFront or a static site domain).
            'url' => env('AWS_URL'),
            // Optional custom S3-compatible endpoint (MinIO, LocalStack, etc.). Leave empty for real AWS.
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

        'cv_uploads' => [
            'driver' => 's3',
            'key' => env('CV_UPLOAD_AWS_ACCESS_KEY_ID', env('AWS_ACCESS_KEY_ID')),
            'secret' => env('CV_UPLOAD_AWS_SECRET_ACCESS_KEY', env('AWS_SECRET_ACCESS_KEY')),
            'region' => env('CV_UPLOAD_AWS_DEFAULT_REGION', env('AWS_DEFAULT_REGION')),
            'bucket' => env('CV_UPLOAD_AWS_BUCKET')
                ?: (env('AWS_BUCKET_ACCESS_POINT_ARN') ?: env('AWS_BUCKET')),
            'url' => env('CV_UPLOAD_AWS_URL', env('AWS_URL')),
            'endpoint' => env('CV_UPLOAD_AWS_ENDPOINT', env('AWS_ENDPOINT')),
            'use_path_style_endpoint' => env(
                'CV_UPLOAD_AWS_USE_PATH_STYLE_ENDPOINT',
                env('AWS_USE_PATH_STYLE_ENDPOINT', false)
            ),
            'throw' => false,
            'report' => false,
        ],

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
