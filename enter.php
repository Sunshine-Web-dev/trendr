<?php
/**
 * Trnder User Page
 *
 * Handles authentication, registering, resetting passwords, forgot password,
 * and other user handling.
 *
 * @package Trnder
 */

/** Make sure that the Trnder bootstrap has run before continuing. */
require( dirname(__FILE__) . '/initiate.php' );

// Redirect to https login if forced to use SSL
if ( force_ssl_admin() && !is_ssl() ) {
	if ( 0 === strpos($_SERVER['REQUEST_URI'], 'http') ) {
		trm_redirect(preg_replace('|^http://|', 'https://', $_SERVER['REQUEST_URI']));
		exit();
	} else {
		trm_redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
		exit();
	}
}

/**
 * Outputs the header for the login page.
 *
 * @uses do_action() Calls the 'login_head' for outputting HTML in the Log In
 *		header.
 * @uses apply_filters() Calls 'login_headerurl' for the top login link.
 * @uses apply_filters() Calls 'login_headertitle' for the top login title.
 * @uses apply_filters() Calls 'login_message' on the message to display in the
 *		header.
 * @uses $error The error global, which is checked for displaying errors.
 *
 * @param string $title Optional. Trnder Log In Page title to display in
 *		<title/> element.
 * @param string $message Optional. Message to display in header.
 * @param TRM_Error $trm_error Optional. Trnder Error Object
 */
function login_header($title = 'Login', $message = '', $trm_error = '') {
	global $error, $is_iphone, $interim_login, $current_site;

	// Don't index any of these forms
	add_filter( 'pre_option_blog_public', '__return_zero' );
	add_action( 'login_head', 'noindex' );

	if ( empty($trm_error) )
		$trm_error = new TRM_Error();

	// Shake it!
	
	?><body <?php body_class( 'login-page' ); ?>>
	<?php get_header(); ?>	

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

		<div id="all">		</body>
<html xmlns="http://www.w3.org/1999/xhtml" >
 <?php

 
	$message = apply_filters('login_message', $message);
	if ( !empty( $message ) ) echo $message . "\n";

	// In case a plugin uses $error rather than the $trm_errors object
	if ( !empty( $error ) ) {
		$trm_error->add('error', $error);
		unset($error);
	}

	if ( $trm_error->get_error_code() ) {
		$errors = '';
		$messages = '';
		foreach ( $trm_error->get_error_codes() as $code ) {
			$severity = $trm_error->get_error_data($code);
			foreach ( $trm_error->get_error_messages($code) as $error ) {
				if ( 'message' == $severity )
					$messages .= '	' . $error . "<br />\n";
				else
					$errors .= '	' . $error . "<br />\n";
			}
		}
		if ( !empty($errors) )
			echo '<div id="login_error">' . apply_filters('login_errors', $errors) . "</div>\n";
		if ( !empty($messages) )
			echo '<p class="message">' . apply_filters('login_messages', $messages) . "</p>\n";
	}
} // End of login_header()

/**
 * Handles sending password retrieval email to user.
 *
 * @uses $trmdb Trnder Database object
 *
 * @return bool|TRM_Error True: when finish. TRM_Error on error
 */
function retrieve_password() {
	global $trmdb, $current_site;

	$errors = new TRM_Error();

	if ( empty( $_POST['user_login'] ) && empty( $_POST['user_email'] ) )
		$errors->add('empty_username', __(' Enter a username or e-mail address.'));

	if ( strpos($_POST['user_login'], '@') ) {
		$user_data = get_user_by_email(trim($_POST['user_login']));
		if ( empty($user_data) )
			$errors->add('invalid_email', __(' There is no user registered with that email address.'));
	} else {
		$login = trim($_POST['user_login']);
		$user_data = get_userdatabylogin($login);
	}

	do_action('lostpassword_post');

	if ( $errors->get_error_code() )
		return $errors;

	if ( !$user_data ) {
		$errors->add('invalidcombo', __(' Invalid username or e-mail.'));
		return $errors;
	}

	// redefining user_login ensures we return the right case in the email
	$user_login = $user_data->user_login;
	$user_email = $user_data->user_email;

	do_action('retreive_password', $user_login);  // Misspelled and deprecated
	do_action('retrieve_password', $user_login);

	$allow = apply_filters('allow_password_reset', true, $user_data->ID);

	if ( ! $allow )
		return new TRM_Error('no_password_reset', __('Password reset is not allowed for this user'));
	else if ( is_trm_error($allow) )
		return $allow;

	$key = $trmdb->get_var($trmdb->prepare("SELECT user_activation_key FROM $trmdb->users WHERE user_login = %s", $user_login));
	if ( empty($key) ) {
		// Generate something random for a key...
		$key = trm_generate_password(20, false);
		do_action('retrieve_password_key', $user_login, $key);
		// Now insert the new md5 key into the db
		$trmdb->update($trmdb->users, array('user_activation_key' => $key), array('user_login' => $user_login));
	}
	$message = __('You have asked to reset the password,. Click link to validate!.') . "\r\n\r\n";
	$message .= network_site_url() . "\r\n\r\n";
	$message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
	$message .= __('To reset your password visit the following address, otherwise just ignore this email and nothing will happen.') . "\r\n\r\n";
	$message .= network_site_url("enter.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . "\r\n";

	if ( is_multisite() )
		$blogname = $GLOBALS['current_site']->site_name;
	else
		// The blogname option is escaped with esc_html on the way into the database in sanitize_option
		// we want to reverse this for the plain text arena of emails.
		$blogname = trm_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	$title = sprintf( __('[%s] Password Reset'), $blogname );

	$title = apply_filters('retrieve_password_title', $title);
	$message = apply_filters('retrieve_password_message', $message, $key);

	if ( $message && !trm_mail($user_email, $title, $message) )
		trm_die( __('The e-mail could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...') );

	return true;
}

/**
 * Handles resetting the user's password.
 *
 * @uses $trmdb Trnder Database object
 *
 * @param string $key Hash to validate sending user's password
 * @return bool|TRM_Error
 */
function reset_password($key, $login) {
	global $trmdb;

	$key = preg_replace('/[^a-z0-9]/i', '', $key);

	if ( empty( $key ) || !is_string( $key ) )
		return new TRM_Error('invalid_key', __('Invalid key'));

	if ( empty($login) || !is_string($login) )
		return new TRM_Error('invalid_key', __('Invalid key'));

	$user = $trmdb->get_row($trmdb->prepare("SELECT * FROM $trmdb->users WHERE user_activation_key = %s AND user_login = %s", $key, $login));
	if ( empty( $user ) )
		return new TRM_Error('invalid_key', __('Invalid key'));

	// Generate something random for a password...
	$new_pass = trm_generate_password();

	do_action('password_reset', $user, $new_pass);

	trm_set_password($new_pass, $user->ID);
	update_user_option($user->ID, 'default_password_nag', true, true); //Set up the Password change nag.
	$message  = sprintf(__('Username: %s'), $user->user_login) . "\r\n";
	$message .= sprintf(__('Password: %s'), $new_pass) . "\r\n";
	$message .= site_url('enter.php', 'login') . "\r\n";

	if ( is_multisite() )
		$blogname = $GLOBALS['current_site']->site_name;
	else
		// The blogname option is escaped with esc_html on the way into the database in sanitize_option
		// we want to reverse this for the plain text arena of emails.
		$blogname = trm_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	$title = sprintf( __('[%s] Your new password'), $blogname );

	$title = apply_filters('password_reset_title', $title);
	$message = apply_filters('password_reset_message', $message, $new_pass);

	if ( $message && !trm_mail($user->user_email, $title, $message) )
  		trm_die( __('The e-mail could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...') );

	trm_password_change_notification($user);

	return true;
}

/**
 * Handles registering a new user.
 *
 * @param string $user_login User's username for logging in
 * @param string $user_email User's email address to send password and add
 * @return int|TRM_Error Either user's ID or error on failure.
 */
function register_new_user( $user_login, $user_email ) {
	$errors = new TRM_Error();

	$sanitized_user_login = sanitize_user( $user_login );
	$user_email = apply_filters( 'user_registration_email', $user_email );

	// Check the username
	if ( $sanitized_user_login == '' ) {
		$errors->add( 'empty_username', __( ' Please enter a username.' ) );
	} elseif ( ! validate_username( $user_login ) ) {
		$errors->add( 'invalid_username', __( ' This username is invalid because it uses illegal characters. Please enter a valid username.' ) );
		$sanitized_user_login = '';
	} elseif ( username_exists( $sanitized_user_login ) ) {
		$errors->add( 'username_exists', __( ' This username is already registered, please choose another one.' ) );
	}

	// Check the e-mail address
	if ( $user_email == '' ) {
		$errors->add( 'empty_email', __( ' Please type your e-mail address.' ) );
	} elseif ( ! is_email( $user_email ) ) {
		$errors->add( 'invalid_email', __( ' The email address isn&#8217;t correct.' ) );
		$user_email = '';
	} elseif ( email_exists( $user_email ) ) {
		$errors->add( 'email_exists', __( ' This email is already registered, please choose another one.' ) );
	}

	do_action( 'register_post', $sanitized_user_login, $user_email, $errors );

	$errors = apply_filters( 'registration_errors', $errors, $sanitized_user_login, $user_email );

	if ( $errors->get_error_code() )
		return $errors;

	$user_pass = trm_generate_password();
	$user_id = trm_create_user( $sanitized_user_login, $user_pass, $user_email );
	if ( ! $user_id ) {
		$errors->add( 'registerfail', sprintf( __( ' Couldn&#8217;t register you... please contact the <a href="mailto:%s">webmaster</a> !' ), get_option( 'admin_email' ) ) );
		return $errors;
	}

	update_user_option( $user_id, 'default_password_nag', true, true ); //Set up the Password change nag.

	trm_new_user_notification( $user_id, $user_pass );

	return $user_id;
}

//
// Main
//

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'login';
$errors = new TRM_Error();

if ( isset($_GET['key']) )
	$action = 'resetpass';

// validate action so as to default to the login screen
if ( !in_array($action, array('logout', 'lostpassword', 'retrievepassword', 'resetpass', 'rp', 'register', 'login'), true) && false === has_filter('login_form_' . $action) )
	$action = 'login';

nocache_headers();

header('Content-Type: '.get_bloginfo('html_type').'; charset='.get_bloginfo('charset'));

if ( defined('RELOCATE') ) { // Move flag is set
	if ( isset( $_SERVER['PATH_INFO'] ) && ($_SERVER['PATH_INFO'] != $_SERVER['PHP_SELF']) )
		$_SERVER['PHP_SELF'] = str_replace( $_SERVER['PATH_INFO'], '', $_SERVER['PHP_SELF'] );

	$schema = is_ssl() ? 'https://' : 'http://';
	if ( dirname($schema . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']) != get_option('siteurl') )
		update_option('siteurl', dirname($schema . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']) );
}

//Set a cookie now to see if they are supported by the browser.
setcookie(TEST_COOKIE, 'TRM Cookie check', 0, COOKIEPATH, COOKIE_DOMAIN);
if ( SITECOOKIEPATH != COOKIEPATH )
	setcookie(TEST_COOKIE, 'TRM Cookie check', 0, SITECOOKIEPATH, COOKIE_DOMAIN);

// allow plugins to override the default actions, and to add extra actions if they want
do_action('login_form_' . $action);

$http_post = ('POST' == $_SERVER['REQUEST_METHOD']);
switch ($action) {

case 'logout' :
	check_admin_referer('log-out');
	trm_logout();

	$redirect_to = !empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : 'enter.php?loggedout=true';
	trm_safe_redirect( $redirect_to );
	exit();

break;

case 'lostpassword' :
case 'retrievepassword' :
	if ( $http_post ) {
		$errors = retrieve_password();
		if ( !is_trm_error($errors) ) {
			$redirect_to = !empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : 'enter.php?checkemail=confirm';
			trm_safe_redirect( $redirect_to );
			exit();
		}
	}

	if ( isset($_GET['error']) && 'invalidkey' == $_GET['error'] ) $errors->add('invalidkey', __('Sorry, that key does not appear to be valid.'));
	$redirect_to = apply_filters( 'lostpassword_redirect', !empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '' );

	do_action('lost_password');
	login_header(__('Lost Password'), '<p class="message">' . __('Enter your username or e-mail address.') . '</p>', $errors);

	$user_login = isset($_POST['user_login']) ? stripslashes($_POST['user_login']) : '';

?>

<form name="lostpasswordform"   id="lostpasswordform" action="<?php echo site_url('enter.php?action=lostpassword', 'login_post') ?>" method="post">
		<input type="text" placeholder="Enter Username or E-mail" name="user_login" id="user_login" class="input" value="<?php echo esc_attr($user_login); ?>" size="20" tabindex="10" /></label>
<?php do_action('lostpassword_form'); ?>
	<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>" />
	<p class="submit"><input type="submit" width="200px" name="submit" id="submit-login" class="button-primary" value="<?php esc_attr_e('Get New Password'); ?>" tabindex="100" /></p>
</form>

<p id="nav">
<?php if (get_option('users_can_register')) : ?>
<a href="<?php echo site_url('enter.php', 'login') ?>"><?php _e('Login') ?></a> |
<a href="<?php echo site_url('enter.php?action=register', 'login') ?>"><?php _e('Register') ?></a>
<?php else : ?>
<a href="<?php echo site_url('enter.php', 'login') ?>"><?php _e('Login') ?></a>
<?php endif; ?>
</p>

</div>



<script type="text/javascript">
try{document.getElementById('user_login').focus();}catch(e){}
if(typeof trmOnload=='function')trmOnload();
</script>
</body>
</html>
<?php
break;

case 'resetpass' :
case 'rp' :
	$errors = reset_password($_GET['key'], $_GET['login']);

	if ( ! is_trm_error($errors) ) {
		trm_redirect('enter.php?checkemail=netrmass');
		exit();
	}

	trm_redirect('enter.php?action=lostpassword&error=invalidkey');
	exit();

break;

case 'register' :
	if ( is_multisite() ) {
		// Multisite uses trm-signup.php
		trm_redirect( apply_filters( 'trm_signup_location', get_bloginfo('trmurl') . '/trm-signup.php' ) );
		exit;
	}

	if ( !get_option('users_can_register') ) {
		trm_redirect('enter.php?registration=disabled');
		exit();
	}

	$user_login = '';
	$user_email = '';
	if ( $http_post ) {
		require_once( ABSPATH . TRMINC . '/registration.php');

		$user_login = $_POST['user_login'];
		$user_email = $_POST['user_email'];
		$errors = register_new_user($user_login, $user_email);
		if ( !is_trm_error($errors) ) {
			$redirect_to = !empty( $_POST['redirect_to'] ) ? $_POST['redirect_to'] : 'enter.php?checkemail=registered';
			trm_safe_redirect( $redirect_to );
			exit();
		}
	}

	$redirect_to = apply_filters( 'registration_redirect', !empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '' );
	login_header(__('Registration Form'), '<p class="message register">' . __('Register For This Site') . '</p>', $errors);
?>

<form name="registerform" id="registerform" action="<?php echo site_url('enter.php?action=register', 'login_post') ?>" method="post">
	<p>
		<label><?php _e('Username') ?><br />
		<input type="text" placeholder="Username or E-mail" name="user_login" id="user_login" class="input" value="<?php echo esc_attr(stripslashes($user_login)); ?>" size="20" tabindex="10" /></label>
	</p>
	<p>
		<label><?php _e('E-mail') ?><br />
		<input type="text" name="user_email" id="user_email" class="input" value="<?php echo esc_attr(stripslashes($user_email)); ?>" size="25" tabindex="20" /></label>
	</p>
<?php do_action('register_form'); ?>
	<p id="reg_passmail"><?php _e('A password will be e-mailed to you.') ?></p>
	<br class="clear" />
	<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>" />
	<p class="submit"><input type="submit" name="submit" id="submit-register" class="button-primary" value="<?php esc_attr_e('Register'); ?>" tabindex="100" /></p>
</form>

<p id="nav">
<a href="<?php echo site_url('enter.php', 'login') ?>"><?php _e('Login') ?></a> |
<a href="<?php echo site_url('enter.php?action=lostpassword', 'login') ?>" title="<?php _e('Password Lost and Found') ?>"><?php _e('Lost your password?') ?></a>
</p>

</div>



<script type="text/javascript">
try{document.getElementById('user_login').focus();}catch(e){}
if(typeof trmOnload=='function')trmOnload();
</script>
</body>
</html>
<?php
break;

case 'login' :
default:
	$secure_cookie = '';
	$interim_login = isset($_REQUEST['interim-login']);

	// If the user wants ssl but the session is not ssl, force a secure cookie.
	if ( !empty($_POST['log']) && !force_ssl_admin() ) {
		$user_name = sanitize_user($_POST['log']);
		if ( $user = get_userdatabylogin($user_name) ) {
			if ( get_user_option('use_ssl', $user->ID) ) {
				$secure_cookie = true;
				force_ssl_admin(true);
			}
		}
	}

	if ( isset( $_REQUEST['redirect_to'] ) ) {
		$redirect_to = $_REQUEST['redirect_to'];
		// Redirect to https if user wants ssl
		//if ( $secure_cookie && false !== strpos($redirect_to, 'Backend-WeaprEcqaKejUbRq-trendr') )
			//$redirect_to = preg_replace('|^http://|', 'https://', $redirect_to);
	} else {
		$redirect_to = admin_url();
	}

	$reauth = empty($_REQUEST['reauth']) ? false : true;

	// If the user was redirected to a secure login form from a non-secure admin page, and secure login is required but secure admin is not, then don't use a secure
	// cookie and redirect back to the referring non-secure admin page.  This allows logins to always be POSTed over SSL while allowing the user to choose visiting
	// the admin via http or https.
	if ( !$secure_cookie && is_ssl() && force_ssl_login() && !force_ssl_admin() && ( 0 !== strpos($redirect_to, 'https') ) && ( 0 === strpos($redirect_to, 'http') ) )
		$secure_cookie = false;

	$user = trm_signon(array(), $secure_cookie);

	$redirect_to = apply_filters('login_redirect', $redirect_to, isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '', $user);

	if ( !is_trm_error($user) && !$reauth ) {
		if ( $interim_login ) {
			$message = '<p class="message">' . __('You have logged in successfully.') . '</p>';
			login_header( '', $message ); ?>
			<script type="text/javascript">setTimeout( function(){window.close()}, 8000);</script>
			<p class="alignright">
			<input type="button" class="button-primary" value="<?php esc_attr_e('Close'); ?>" onClick="window.close()" /></p>
			</div></body></html>
<?php		exit;
		}
		// If the user can't edit posts, send them to their profile.
		if ( !$user->has_cap('edit_posts') && ( empty( $redirect_to ) || $redirect_to == 'Backend-WeaprEcqaKejUbRq-trendr/' || $redirect_to == admin_url() ) )
			$redirect_to = admin_url('profile.php');
		trm_safe_redirect($redirect_to);
		exit();
	}

	$errors = $user;
	// Clear errors if loggedout is set.
	if ( !empty($_GET['loggedout']) || $reauth )
		$errors = new TRM_Error();

	// If cookies are disabled we can't log in even with a valid user+pass
	//if ( isset($_POST['testcookie']) && empty($_COOKIE[TEST_COOKIE]) )
		//$errors->add('test_cookie', __(" Cookies are blocked or not supported by your browser! "));

	// Some parts of this script use the main login form to display a message
	if		( isset($_GET['loggedout']) && TRUE == $_GET['loggedout'] )
		$errors->add('loggedout', __('You are now logged out.'), 'message');
	elseif	( isset($_GET['registration']) && 'disabled' == $_GET['registration'] )
		$errors->add('registerdisabled', __('User registration is currently not allowed.'));
	elseif	( isset($_GET['checkemail']) && 'confirm' == $_GET['checkemail'] )
		$errors->add('confirm', __('Check your e-mail for the confirmation link.'), 'message');
	elseif	( isset($_GET['checkemail']) && 'netrmass' == $_GET['checkemail'] )
		$errors->add('netrmass', __('Check your e-mail for your new password.'), 'message');
	elseif	( isset($_GET['checkemail']) && 'registered' == $_GET['checkemail'] )
		$errors->add('registered', __('Registration complete. Please check your e-mail.'), 'message');
	elseif	( $interim_login )
		$errors->add('expired', __('Your session has expired. Please log-in again.'), 'message');

	// Clear any stale cookies.
	if ( $reauth )
		trm_clear_auth_cookie();

	login_header(__('Login'), '', $errors);

	if ( isset($_POST['log']) )
		$user_login = ( 'incorrect_password' == $errors->get_error_code() || 'empty_password' == $errors->get_error_code() ) ? esc_attr(stripslashes($_POST['log'])) : '';
	$rememberme = ! empty( $_POST['rememberme'] );
?>

<form name="loginform" id="loginform" action="<?php echo site_url('enter.php', 'login_post') ?>" method="post">
	<p>
		<br />
		<input type="text" placeholder="Username or E-mail" name="log" id="user_login" class="input" value="<?php echo esc_attr($user_login); ?>" size="20" tabindex="10" />

		<br />
		<input type="password" placeholder="Password" name="pwd" id="user_pass" class="input" value="" size="20" tabindex="20" />
<?php do_action('login_form'); ?>
	<p class="forgetmenot"><label><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="90"<?php checked( $rememberme ); ?> /> <?php esc_attr_e('Remember Me'); ?></label></p>
	<p class="submit">
		<input type="submit" name="submit" id="submit-login" class="button-primary" value="<?php esc_attr_e('Login'); ?>" tabindex="100" />
<?php	if ( $interim_login ) { ?>
		<input type="hidden" name="interim-login" value="1" />
<?php	} else { ?>
		<input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirect_to); ?>" />
<?php 	} ?>
		<input type="hidden" name="testcookie" value="1" />
	</p>
</form>

<?php if ( !$interim_login ) { ?>
<p id="nav">
<?php if ( isset($_GET['checkemail']) && in_array( $_GET['checkemail'], array('confirm', 'netrmass') ) ) : ?>
<?php elseif ( get_option('users_can_register') ) : ?>
<a href="<?php echo site_url('enter.php?action=register', 'login') ?>"><?php _e('Register') ?></a> |
<a href="<?php echo site_url('enter.php?action=lostpassword', 'login') ?>" title="<?php _e('Password Lost and Found') ?>"><?php _e('Lost your password?') ?></a>
<?php else : ?>
<a href="<?php echo site_url('enter.php?action=lostpassword', 'login') ?>" title="<?php _e('Password Lost and Found') ?>"><?php _e('Lost your password?') ?></a>
<?php endif; ?>
</p>
</div>

<?php } else { ?>
</div>
<?php } ?>
	<div id="apps">
<img id="app-badges" src="../../../../../../trm-src/858483/TRENDR/a/images/app-stores.png"style="display: block;width:320px;height:31px;
margin-left: auto;
margin-right: auto;">
<script type="text/javascript">
function trm_attempt_focus(){
setTimeout( function(){ try{
<?php if ( $user_login || $interim_login ) { ?>
d = document.getElementById('user_pass');
<?php } else { ?>
d = document.getElementById('user_login');
<?php } ?>
d.value = '';
d.focus();
} catch(e){}
}, 200);
}
<?php if ( !$error ) { ?>
trm_attempt_focus();
<?php } ?>
if(typeof trmOnload=='function')trmOnload();
</script>
</body>
</html>
<?php

break;
} // end action switch

?>
<?php get_footer(); ?>	
