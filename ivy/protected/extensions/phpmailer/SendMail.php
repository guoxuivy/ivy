<?php
class SendMail
{
	/**
	 * 使用smtp发送邮件
	 * @param $to 接收邮件的人
	 * @param $subject 邮件标题
	 * @param $body 邮件内容
	 */
	public function SmtpSendMail($to, $subject, $body) {
		date_default_timezone_set('Asia/Shanghai');
		require_once 'PHPMailerAutoload.php';
		require_once 'mailconfig.php';
	
		//Create a new PHPMailer instance
		$mail = new PHPMailer();
		//Tell PHPMailer to use SMTP
		$mail->isSMTP();
		//Enable SMTP debugging
		// 0 = off (for production use)
		// 1 = client messages
		// 2 = client and server messages
		$mail->SMTPDebug = 0;
		//Ask for HTML-friendly debug output
		$mail->Debugoutput = 'html';
		//Set the hostname of the mail server
		$mail->Host = SMTP_HOST;
		//Set the SMTP port number - likely to be 25, 465 or 587
		$mail->Port = SMTP_PORT;
		//Whether to use SMTP authentication
		$mail->SMTPAuth = SMTP_AUTH;
		//Username to use for SMTP authentication
		$mail->Username = SMTP_USERNAME;
		//Password to use for SMTP authentication
		$mail->Password = SMTP_PASSWORD;
		//Set who the message is to be sent from
		$mail->setFrom(SMTP_FROM, SMTP_FROMNAME);
		//设置utf-8编码
		$mail->CharSet = 'utf-8';
		//Set who the message is to be sent to
		$mail->addAddress($to);
		//Set the subject line
		$mail->Subject = $subject;
		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		$mail->msgHTML($body);
	
		//send the message, check for errors
		if (!$mail->send()) {
			return false;
		} else {
			return true;
		}
	}
}

