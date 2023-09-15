<?php

// change the next line to points to your wordpress dir
define( 'ABSPATH', '/home/gogo/projects/wordpress/' );

define( 'WP_DEBUG', false );

// WARNING WARNING WARNING!
// tests DROPS ALL TABLES in the database. DO NOT use a production database

define( 'DB_NAME', 'wp_media_db2' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', 'root' );
define( 'DB_HOST', '127.0.0.1' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

$table_prefix = 'wp_'; // Only numbers, letters, and underscores please!

define( 'WP_TESTS_DOMAIN', 'localhost' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test WP Crawler' );

define( 'WP_PHP_BINARY', 'php' );

define( 'WPLANG', '' );
