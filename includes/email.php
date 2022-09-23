<?php

use PHPMailer\PHPMailer;


function send_email ($to, $subject, $message) {
	$from = 'noreply@rhlabs.net';
	$fromname = 'Ansible Reports';

	if (read_setting('disable_email', 0)) {
		return true;
	}

	if (!is_array($to) && !strstr($to, '@')) {
		return;
	}

	$mail = new PHPMailer\PHPMailer(true);

	try {
		//$mail->SMTPDebug  = 4;
		$mail->isSMTP();
		$mail->Host	    = SMTP_SERVER;
		$mail->SMTPAuth   = true;
		$mail->Username   = SMTP_USER;
		$mail->Password   = SMTP_PASSWORD;
		$mail->SMTPSecure = 'tls';
		$mail->Port	    = 587;
		$mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);

		$mail->setFrom($from, $fromname);
		if (is_array($to)) {
			foreach ($to as $t) {
				$mail->addAddress($t);
			}
		} else {
			$mail->addAddress($to);
		}
		$mail->addReplyTo('noreply@rhlabs.net', 'Ansible Reports');

		$mail->isHTML(true);
		$mail->Subject = $subject;
		$mail->Body	= $message;
		$mail->AltBody = strip_tags(str_replace('<br>', "\n", $message));

		$mail->send();
	} catch (Exception $e) {
		// Need to create a log in the DB
	}
}