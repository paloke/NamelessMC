<?php 
/*
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */
 
 require ('inc/classes/class.phpmailer.php');	// Send mail via gmail.

if($user->isLoggedIn()) { // User must be logged out to view this page
	Redirect::to("/");
	die();
} else {

$siteemail = $queries->getWhere("settings", array("name", "=", "outgoing_email"));
$siteemail = $siteemail[0]->value;

if(Input::exists()) {
	if(Token::check(Input::get('token'))) {
		$check = $queries->getWhere('users', array('username', '=', Input::get('username')));
		if(count($check)){
			$code = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 60);
			
			// Load SMTP values 
			date_default_timezone_set('Europe/Madrid');	// Timezone

			$mail = new PHPMailer(true); 			// New instance, with exceptions enabled
			$mail->IsSMTP();                           	// We use the SMTP method of the class PHPMailer
			$mail->SMTPDebug = 0;				// 0-wwithout debug, 1-Client debug 2-Client and server debug.

			$mail->Debugoutput = 'html';			// Errors dislplayed in HTML
			$mail->CharSet = 'UTF-8';			// (DB UTF-8 > Email UTF-8)
			$mail->XMailer = ' ';				// Removes the X-Mailer header 

			$mail->SMTPAuth = true				// enable SMTP authentication (Required for GMAIL)
			$mail->Host = "ssl://smtp.gmail.com";		// SMTP server
			$mail->Port = 465;				// SMTP server port (GMAIL)

			$mail->Username = "USER@gmail.com";		// SMTP server User
			$mail->Password = "password";			// SMTP server password

			// Load message vaules

			$to= $check[0]->email;
			$username =  htmlspecialchars($check[0]->username);
			$srv_address = $_SERVER['SERVER_NAME'];

			$mail->AddReplyTo($siteemail,$sitename);		// Where the answers to be sent.
			$mail->From = $siteemail; 				// Mail Sender
			$mail->FromName = $sitename;				// Sender name.
			$mail->AddAddress($to, $newuser); 			// Destination.
			$mail->Subject  = 'Password Reset ' . $sitename . '!';	// Subject.

			// Load mail template
			$HTML_file = file_get_contents('forgot_password.html', dirname(__FILE__));
			//Replaces the document data.
			$marcadores = array("%USER_NAME%", "%USER_MAIL%", "%USER_VALIDATE%", "%SRV_NAME%", "%SRV_ADDRESS%", "%SRV_MAIL%");
			$resultados = array($username, $to, $code, $sitename, $srv_address, $siteemail );
	
			$HTML_msg = str_ireplace($marcadores, $resultados, $HTML_file);

			$mail->MsgHTML($HTML_msg);
			$mail->AddEmbeddedImage(dirname(__FILE__).'/img/logo.jpg', 'mail-logo', '$sitename');

			// Add custom fileds to mail head
			$mail->addCustomHeader("SRV: 01");

			$mail->IsHTML(true);
			$mail->Send();
			
			$queries->update('users', $check[0]->id, array(
				'reset_code' => $code,
				'active' => 0
			));
			
			Session::flash('info', '<div class="alert alert-info">Success. Please check your emails for further instructions.</div>');
			Redirect::to("/");	
		} else {
			Session::flash('error', '<div class="alert alert-info">That username does not exist.</div>');
		}
		
	
	} else {
		Session::flash('error', '<div class="alert alert-info">Error processing your request.</div>');
	}
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Forgot Password">
    <meta name="author" content="Samerton">
    <link rel="icon" href="/favicon.ico">
    <title><?php echo $sitename; ?> &bull; Forgot Password</title> 
	
	<!-- Custom style -->
	<style>
	html {
		overflow-y: scroll;
	}
	</style>
	
	<?php require("inc/templates/header.php"); ?>
	
   </head>
   <body>
	<?php require("inc/templates/navbar.php"); ?>
	
	<div class="container">
		<form action="" method="post">
		<h2>Forgot Password</h2>
		<?php
		if(Session::exists('error')){
			echo Session::flash('error');
		}
		?>
			<input class="form-control" type="text" name="username" id="username" placeholder="Username" autocomplete="off">				
			<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
			<br />
			<center><input class="btn btn-success" type="submit" value="Submit"></center>
		</form>
		<hr>
	  <?php require("inc/templates/footer.php"); ?> 
	  
    </div> <!-- /container -->
		
	<?php require("inc/templates/scripts.php"); ?>
	
   </body>
<?php } ?>
