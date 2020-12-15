<?php
/**
 * Retrieves and creates the trm-setup.php file.
 *
 * The permissions for the base directory must allow for writing files in order
 * for the trm-setup.php to be created using this page.
 *
 * @internal This file must be parsable by PHP4.
 *
 * @package Trnder
 * @subpackage Administration
 */

/**
 * We are installing.
 *
 * @package Trnder
 */
define('TRM_INSTALLING', true);

/**
 * We are blissfully unaware of anything.
 */
define('TRM_SETUP_CONFIG', true);

/**
 * Disable error reporting
 *
 * Set this to error_reporting( E_ALL ) or error_reporting( E_ALL | E_STRICT ) for debugging
 */
error_reporting(0);

/**#@+
 * These three defines are required to allow us to use require_trm_db() to load
 * the database class while being trm-src/db.php aware.
 * @ignore
 */
define('ABSPATH', dirname(dirname(__FILE__)).'/');
define('TRMINC', 'Source-zACHAvU6As28quwr-trendr');
define('TRM_CONTENT_DIR', ABSPATH . 'trm-src');
define('TRM_DEBUG', false);
/**#@-*/

require_once(ABSPATH . TRMINC . '/load.php');
require_once(ABSPATH . TRMINC . '/version.php');
trm_check_php_mysql_versions();

require_once(ABSPATH . TRMINC . '/compat.php');
require_once(ABSPATH . TRMINC . '/functions.php');
require_once(ABSPATH . TRMINC . '/class-trm-error.php');

if (!file_exists(ABSPATH . 'trm-setup-sample.php'))
	trm_die('Sorry, I need a trm-setup-sample.php file to work from. Please re-upload this file from your Trnder installation.');

$configFile = file(ABSPATH . 'trm-setup-sample.php');

// Check if trm-setup.php has been created
if (file_exists(ABSPATH . 'trm-setup.php'))
	trm_die("<p>The file 'trm-setup.php' already exists. If you need to reset any of the configuration items in this file, please delete it first. You may try <a href='install.php'>installing now</a>.</p>");

// Check if trm-setup.php exists above the root directory but is not part of another install
if (file_exists(ABSPATH . '../trm-setup.php') && ! file_exists(ABSPATH . '../trm-main.php'))
	trm_die("<p>The file 'trm-setup.php' already exists one level above your Trnder installation. If you need to reset any of the configuration items in this file, please delete it first. You may try <a href='install.php'>installing now</a>.</p>");

if (isset($_GET['step']))
	$step = $_GET['step'];
else
	$step = 0;

/**
 * Display setup trm-setup.php file header.
 *
 * @ignore
 * @since 2.3.0
 * @package Trnder
 * @subpackage Installer_TRM_Config
 */
function display_header() {
	header( 'Content-Type: text/html; charset=utf-8' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Trnder &rsaquo; Setup Configuration File</title>
<link rel="stylesheet" href="css/install.css" type="text/css" />

</head>
<body>
<h1 id="logo"><img alt="Trnder" src="images/trendr-logo.png" /></h1>
<?php
}//end function display_header();

switch($step) {
	case 0:
		display_header();
?>


<ol>
	<li>Database name</li>
	<li>Database username</li>
	<li>Database password</li>
	<li>Database host</li>
	<li>Table prefix (if you want to run more than one Trnder in a single database) </li>
</ol>


<p class="step"><a href="setup-config.php?step=1<?php if ( isset( $_GET['noapi'] ) ) echo '&amp;noapi'; ?>" class="button">Let&#8217;s go!</a></p>
<?php
	break;

	case 1:
		display_header();
	?>
<form method="post" action="setup-config.php?step=2">
	<p>Below you should enter your database connection details. If you're not sure about these, contact your host. </p>
	<table class="form-table">
		<tr>
			<th scope="row"><label for="dbname">Database Name</label></th>
			<td><input name="dbname" id="dbname" type="text" size="25" value="trendr" /></td>
			<td>The name of the database you want to run TRM in. </td>
		</tr>
		<tr>
			<th scope="row"><label for="uname">User Name</label></th>
			<td><input name="uname" id="uname" type="text" size="25" value="username" /></td>
			<td>Your MySQL username</td>
		</tr>
		<tr>
			<th scope="row"><label for="pwd">Password</label></th>
			<td><input name="pwd" id="pwd" type="text" size="25" value="password" /></td>
			<td>...and MySQL password.</td>
		</tr>
		<tr>
			<th scope="row"><label for="dbhost">Database Host</label></th>
			<td><input name="dbhost" id="dbhost" type="text" size="25" value="localhost" /></td>
			<td>You should be able to get this info from your web host, if <code>localhost</code> does not work.</td>
		</tr>
		<tr>
			<th scope="row"><label for="prefix">Table Prefix</label></th>
			<td><input name="prefix" id="prefix" type="text" id="prefix" value="trm_" size="25" /></td>
			<td>If you want to run multiple Trnder installations in a single database, change this.</td>
		</tr>
	</table>
	<?php if ( isset( $_GET['noapi'] ) ) { ?><input name="noapi" type="hidden" value="true" /><?php } ?>
	<p class="step"><input name="submit" type="submit" value="Submit" class="button" /></p>
</form>
<?php
	break;

	case 2:
	$dbname  = trim($_POST['dbname']);
	$uname   = trim($_POST['uname']);
	$passwrd = trim($_POST['pwd']);
	$dbhost  = trim($_POST['dbhost']);
	$prefix  = trim($_POST['prefix']);
	if ( empty($prefix) )
		$prefix = 'trm_';

	// Validate $prefix: it can only contain letters, numbers and underscores
	if ( preg_match( '|[^a-z0-9_]|i', $prefix ) )
		trm_die( /*TRM_I18N_BAD_PREFIX*/' "Table Prefix" can only contain numbers, letters, and underscores.'/*/TRM_I18N_BAD_PREFIX*/ );

	// Test the db connection.
	/**#@+
	 * @ignore
	 */
	define('DB_NAME', $dbname);
	define('DB_USER', $uname);
	define('DB_PASSWORD', $passwrd);
	define('DB_HOST', $dbhost);
	/**#@-*/

	// We'll fail here if the values are no good.
	require_trm_db();
	if ( ! empty( $trmdb->error ) ) {
		$back = '<p class="step"><a href="setup-config.php?step=1" onclick="javascript:history.go(-1);return false;" class="button">Try Again</a></p>';
		trm_die( $trmdb->error->get_error_message() . $back );
	}

	// Fetch or generate keys and salts.
	$no_api = isset( $_POST['noapi'] );
	require_once( ABSPATH . TRMINC . '/plugin.php' );
	require_once( ABSPATH . TRMINC . '/lan.php' );
	require_once( ABSPATH . TRMINC . '/pomo/translations.php' );
	if ( ! $no_api ) {
		require_once( ABSPATH . TRMINC . '/class-http.php' );
		require_once( ABSPATH . TRMINC . '/http.php' );
		trm_fix_server_vars();
		/**#@+
		 * @ignore
		 */
		function get_bloginfo() {
			return ( ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . str_replace( $_SERVER['PHP_SELF'], '/Backend-WeaprEcqaKejUbRq-trendr/setup-config.php', '' ) );
		}
		/**#@-*/
		$secret_keys = trm_remote_get( 'https://api.trendr.org/secret-key/1.1/salt/' );
	}

	if ( $no_api || is_trm_error( $secret_keys ) ) {
		$secret_keys = array();
		require_once( ABSPATH . TRMINC . '/pluggable.php' );
		for ( $i = 0; $i < 8; $i++ ) {
			$secret_keys[] = trm_generate_password( 64, true, true );
		}
	} else {
		$secret_keys = explode( "\n", trm_remote_retrieve_body( $secret_keys ) );
		foreach ( $secret_keys as $k => $v ) {
			$secret_keys[$k] = substr( $v, 28, 64 );
		}
	}
	$key = 0;

	foreach ($configFile as $line_num => $line) {
		switch (substr($line,0,16)) {
			case "define('DB_NAME'":
				$configFile[$line_num] = str_replace("database_name_here", $dbname, $line);
				break;
			case "define('DB_USER'":
				$configFile[$line_num] = str_replace("'username_here'", "'$uname'", $line);
				break;
			case "define('DB_PASSW":
				$configFile[$line_num] = str_replace("'password_here'", "'$passwrd'", $line);
				break;
			case "define('DB_HOST'":
				$configFile[$line_num] = str_replace("localhost", $dbhost, $line);
				break;
			case '$table_prefix  =':
				$configFile[$line_num] = str_replace('trm_', $prefix, $line);
				break;
			case "define('AUTH_KEY":
			case "define('SECURE_A":
			case "define('LOGGED_I":
			case "define('NONCE_KE":
			case "define('AUTH_SAL":
			case "define('SECURE_A":
			case "define('LOGGED_I":
			case "define('NONCE_SA":
				$configFile[$line_num] = str_replace('put your unique phrase here', $secret_keys[$key++], $line );
				break;
		}
	}
	if ( ! is_writable(ABSPATH) ) :
		display_header();
?>
<p>Sorry, but I can't write the <code>trm-setup.php</code> file.</p>
<p>You can create the <code>trm-setup.php</code> manually and paste the following text into it.</p>
<textarea cols="98" rows="15" class="code"><?php
		foreach( $configFile as $line ) {
			echo htmlentities($line, ENT_COMPAT, 'UTF-8');
		}
?></textarea>
<p>After you've done that, click "Run the install."</p>
<p class="step"><a href="install.php" class="button">Run the install</a></p>
<?php
	else :
		$handle = fopen(ABSPATH . 'trm-setup.php', 'w');
		foreach( $configFile as $line ) {
			fwrite($handle, $line);
		}
		fclose($handle);
		chmod(ABSPATH . 'trm-setup.php', 0666);
		display_header();
?>

<p class="step"><a href="install.php" class="button">Run the install</a></p>
<?php
	endif;
	break;
}
?>
</body>
</html>
