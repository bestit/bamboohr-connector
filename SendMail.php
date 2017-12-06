<?php

declare(strict_types=1);

namespace BambooHRConnector;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Class SendMail
 *
 * @package BambooHRConnector
 * @author Georgi Damyanov <georgi.damyanov@bestit-online.at>
 */
class SendMail
{
    /**
     * Instance of PHPMailer
     *
     * @var $mail PHPMailer
     */
    private $mail;

    /**
     * Instance of Config
     *
     * @var $config Config
     */
    private $config;

    /**
     * SendMail constructor.
     *
     * @param Config $config Config Instance
     * @param PHPMailer $mail PHPMailer Instance
     */
    public function __construct(Config $config, PHPMailer $mail)
    {
        $this->mail = $mail;
        $this->config = $config;
    }

    /**
     * Function sendEmail
     *
     * Send e-mail depending on if tmp_error.txt is empty or not. (empty = no errors)
     *
     * @return void
     */
    public function sendEmail(): void
    {
        try {
            if (filesize('./log/tmp_error.txt') === 0) {

                $this->setEmailServerSettings();
                $this->mail->isHTML(true);
                $this->mail->Subject = 'BambooHR Connector - Successfully imported';
                $this->mail->Body    = 'Absences were successfully imported!';

                $this->mail->send();
                echo 'Message has been sent';
            } else {
                $this->setEmailServerSettings();
                $this->mail->isHTML(true);
                $this->mail->Subject = 'BambooHR Connector - Error Log';
                $this->mail->Body    = 'An <span style="color: red;">Error</span> has occurred. Please check the
                                        <strong>tmp_error.txt</strong>. <br/>
                                        You can see previous errors inside the <strong>global_error.txt</strong> file';

                $this->mail->addAttachment('./log/tmp_error.txt');
                $this->mail->addAttachment('./log/global_error.txt');

                $this->mail->send();
                echo 'Message has been sent';
            }
        } catch (Exception $e) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $this->mail->ErrorInfo;
        }
    }

    /**
     * Function setEmailServerSettings
     *
     * Sets the e-mail server settings
     *
     * @return void
     */
    public function setEmailServerSettings(): void
    {
        $this->mail->SMTPDebug = 2;
        $this->mail->isSMTP();
        $this->mail->Host = $this->config->getConfig('mail', 'smtp_host');
        $this->mail->SMTPAuth = $this->config->getConfig('mail', 'smtp_auth');
        $this->mail->Username = $this->config->getConfig('mail', 'username');
        $this->mail->Password = $this->config->getConfig('mail', 'password');
        $this->mail->SMTPSecure = $this->config->getConfig('mail', 'smtp_secure');
        $this->mail->Port = $this->config->getConfig('mail', 'port');

        //Recipients
        $this->mail->setFrom($this->config->getConfig('mail','send_from'), 'Sender');
        $this->mail->addAddress($this->config->getConfig('mail', 'send_to'), 'Recipient');     // Add a recipient
    }
}
