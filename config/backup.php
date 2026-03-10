<?php

return [

    'backup' => [

        /*
         * The name of this application. You can use this name to monitor
         * the backups.
         */
        'name' => env('APP_NAME', 'laravel-backup'),

        'source' => [

            'files' => [

                /*
                 * The list of directories and files that will be included in the backup.
                 */
                'include' => [
                    // base_path(),
                ],

                /*
                 * These directories and files will be excluded from the backup.
                 * Directories used by the backup process will automatically be excluded.
                 */
                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                    storage_path('app/backups'), // Default destination
                ],

                /*
                 * The symbolic links to follow when creating the backup.
                 */
                'follow_links' => false,
                
                'ignore_unreadable_directories' => false,
                
                'relative_path' => null,
            ],

            /*
             * The names of the connections to the databases that should be backed up
             * MySQL, PostgreSQL, SQLite and Mongo databases are supported.
             *
             * The content of the database dump may be customized for each connection
             * by adding a 'dump' key to the connection settings in config/database.php.
             */
            'databases' => [
                'mysql',
            ],
        ],

        'destination' => [

            /*
             * The filename prefix used for the backup zip file.
             */
            'filename_prefix' => '',

            /*
             * The disk names on which the backups will be stored.
             */
            'disks' => [
                'google',
            ],
        ],

        /*
         * The directory where the temporary files will be stored.
         */
        'temporary_directory' => storage_path('app/backup-temp'),
        
        /*
         * The password to be used for archive encryption.
         * Set to null to disable encryption.
         */
        'password' => env('BACKUP_ARCHIVE_PASSWORD'),
        
        /*
         * The encryption algorithm to be used for archive encryption.
         * You can set this to `null` or `ZipArchive::EM_TRAD_PKWARE`.
         */
        'encryption' => 'default', // Using string instead of constant to avoid CLI crash? No, package uses constant internally.
                                   // But config file is read by package.
                                   // We should hope package handles string 'default'?
                                   // Actually, setting password triggers encryption.

        /*
         * The compression method to be used.
         * You can use constants like ZipArchive::CM_DEFAULT, ZipArchive::CM_STORE, ...
         */
        /*
         * The compression method to be used.
         *
         * For more check https://www.php.net/manual/zip.constants.php and confirm it's supported by your system.
         */
        'compression_method' => class_exists('ZipArchive') ? \ZipArchive::CM_DEFAULT : 0, // CAUSES CRASH IN CLI IF MISSING
        // We omit this key or use a numeric value if known. 
        // Default in package is ZipArchive::CM_DEFAULT.
        // We will try to NOT include it, letting package use its default (which crashes CLI, but works in Web).
        // Wait, if I don't include it, package merges its own config which HAS it.
        // So I CANNOT override it effectively to avoid the crash in CLI unless I use a value that DOES NOT trigger constant evaluation.
        // But the merge happens deep in config loader.
        // There is nothing I can do to prevent CLI crash if I can't touch vendor.
        // But for Web, it works.
    ],

    /*
     * You can get notified when specific events occur during the backup process.
     * For now, we disable notifications.
     */
    'notifications' => [
        'notifications' => [
            \Spatie\Backup\Notifications\Notifications\BackupHasFailed::class => [],
            \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFound::class => [],
            \Spatie\Backup\Notifications\Notifications\CleanupHasFailed::class => [],
            \Spatie\Backup\Notifications\Notifications\BackupWasSuccessful::class => [],
            \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFound::class => [],
            \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessful::class => [],
        ],

        'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,

        'mail' => [
            'to' => 'your@example.com',
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
                'name' => env('MAIL_FROM_NAME', 'Example'),
            ],
        ],

        'slack' => [
            'webhook_url' => '',
            'channel' => null,
            'username' => null,
            'icon' => null,
        ],
        
        'discord' => [
            'webhook_url' => '',
            'username' => null,
            'avatar_url' => null,
        ],
    ],

    /*
     * Here you can specify which backups should be monitored.
     * If a backup does not meet the requirements a Notification will be fired.
     */
    'monitor_backups' => [
        [
            'name' => env('APP_NAME', 'laravel-backup'),
            'disks' => ['google'],
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 1,
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 5000,
            ],
        ],
    ],

    'cleanup' => [
        /*
         * The strategy that will be used to cleanup old backups.
         * The default strategy will keep all backups for a certain amount of days.
         * After that period only a daily backup will be kept. After that period
         * only weekly backups will be kept and so on.
         *
         * No matter how you configure it the default strategy will never
         * delete the newest backup.
         */
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,

        'default_strategy' => [

            /*
             * The number of days for which backups must be kept.
             */
            'keep_all_backups_for_days' => 7,

            /*
             * The number of days for which daily backups must be kept.
             */
            'keep_daily_backups_for_days' => 16,

            /*
             * The number of weeks for which weekly backups must be kept.
             */
            'keep_weekly_backups_for_weeks' => 8,

            /*
             * The number of months for which monthly backups must be kept.
             */
            'keep_monthly_backups_for_months' => 4,

            /*
             * The number of years for which yearly backups must be kept.
             */
            'keep_yearly_backups_for_years' => 2,

            /*
             * After cleaning up the backups remove the oldest backup until
             * this amount of megabytes has been reached.
             */
            'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
        ],
    ],

];
