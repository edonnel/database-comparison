<?
	ini_set('display_errors',1);

	define('BASE_DIR', $_SERVER['DOCUMENT_ROOT']);

	error_reporting(E_ALL);

	const THIS_DIR = __DIR__;

	require_once THIS_DIR.'/src/php/functions.php';

	// start the session
	\database_comparison\start_the_session();

	define('THIS_URL', '?a='.$_GET['a']);
	define('THIS_URL_FULL', returnURL().'/admin/'.THIS_URL);

    require_once THIS_DIR.'/_config.php';
	require_once THIS_DIR.'/lib/result/result.class.php';
	require_once THIS_DIR.'/lib/changes/changes.class.php';
	require_once THIS_DIR.'/lib/csrf/csrf.class.php';
	require_once THIS_DIR.'/lib/alerts/alerts.class.php';
	require_once THIS_DIR.'/src/php/classes/database.class.php';
	require_once THIS_DIR.'/src/php/classes/mysqli_builder.class.php';

	$database_prod = new \database_comparison\database(db_cred_prod['host'], db_cred_prod['user'], db_cred_prod['pass'], db_cred_prod['name']);
	$database_stag = new \database_comparison\database(db_cred_stag['host'], db_cred_stag['user'], db_cred_stag['pass'], db_cred_stag['name']);

	$GLOBALS['database_prod'] = $database_prod;
	$GLOBALS['database_stag'] = $database_stag;

	// start csrf
	\csrf::init();

    require_once THIS_DIR.'/process.php';
    require_once THIS_DIR.'/listing.php';