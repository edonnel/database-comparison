<?
	ini_set('display_errors',1);

	define('BASE_DIR', $_SERVER['DOCUMENT_ROOT']);

	const THIS_DIR = __DIR__;
	define('THIS_URL', '?a='.$_GET['a']);
	define('THIS_URL_FULL', returnURL().'/admin/'.THIS_URL);

    require_once THIS_DIR.'/_config.php';
	require_once dirname(THIS_DIR).'/_src/php/result.class.php';
	require_once dirname(THIS_DIR).'/_src/php/changes.class.php';
	require_once THIS_DIR.'/src/php/database.class.php';
	require_once THIS_DIR.'/src/php/mysqli_builder.class.php';

	$database_prod = new \database\database(db_cred_prod['host'], db_cred_prod['user'], db_cred_prod['pass'], db_cred_prod['name']);
	$database_stag = new \database\database(db_cred_stag['host'], db_cred_stag['user'], db_cred_stag['pass'], db_cred_stag['name']);

	$GLOBALS['database_prod'] = $database_prod;
	$GLOBALS['database_stag'] = $database_stag;

	require_once THIS_DIR.'/functions.php';

    require_once THIS_DIR.'/process.php';
    require_once THIS_DIR.'/listing.php';