<?php

if ( PHP_SAPI !== 'cli' ) {
	die( 'Not an entry point' );
}

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'MediaWiki is not available for the test environment' );
}

$mwVendorPath = __DIR__ . '/../../../vendor/autoload.php';
$localVendorPath = __DIR__ . '/../vendor/autoload.php';

if ( is_readable( $localVendorPath ) ) {
	require $localVendorPath;
} elseif ( is_readable( $mwVendorPath ) ) {
	require $mwVendorPath;
}
