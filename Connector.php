<?php

declare(strict_types=1);

namespace BambooHRConnector;

include 'Config.php';
include 'Logger.php';
include 'BambooHRAbsences.php';
include 'BambooHR/API/API.php';
include 'PapershiftAbsences.php';
include 'SendMail.php';

use PHPMailer\PHPMailer\PHPMailer;

require 'vendor/autoload.php';

/**
 * Class Connector
 *
 * @version 2.1
 *
 * @package BambooHRConnector
 * @author Georgi Damyanov <georgi.damyanov@bestit-online.at>
 */
class Connector
{
    /**
     * Instance of PHPMailer
     *
     * @var $mailer PHPMailer
     */
    private $mailer;

    /**
     * Instance of SendMail
     *
     * @var $sendmail SendMail
     */
    private $sendmail;

    /**
     * Instance of Config
     *
     * @var $config Config
     */
    private $config;

    /**
     * Instance of BambooHRAbsences
     *
     * @var $bamboo BambooHRAbsences
     */
    private $bamboo;

    /**
     * Instance of BambooAPI
     *
     * @var $bambooAPI BambooAPI
     */
    private $bambooAPI;

    /**
     * Instance of PapershiftAbsences
     *
     * @var $papershift PapershiftAbsences
     */
    private $papershift;

    /**
     * Instance of Logger
     *
     * @var $logger Logger
     */
    private $logger;

    /**
     * Connector constructor. Create all instances.
     */
    public function __construct()
    {
        $this->logger = new Logger();
        $this->config = new Config($this->logger);
        $this->mailer = new PHPMailer(true);
        $this->sendmail = new SendMail($this->config, $this->mailer);
        $this->bambooAPI = new BambooAPI($this->config->getConfig('bamboohr', 'company'));
        $this->bamboo = new BambooHRAbsences($this->config, $this->bambooAPI, $this->logger, $this->sendmail);
        $this->papershift = new PapershiftAbsences($this->config, $this->logger, $this->sendmail);
    }

    /**
     * Function addAbsencesFromBambooHRToPapershift
     *
     * - Cut/paste error messages from tmp_error.txt to global_error.txt
     * - Get absences from BambooHR
     * - Add the absences to Papershift
     * - Confirm the absences
     * - Send email. (either error or confirmation)
     *
     * @return void
     */
    public function addAbsencesFromBambooHRToPapershift(): void
    {
//        $this->logger->transferDailyLogToGlobal();
        $bambooAbsencesArray = $this->bamboo->getBambooHRAbsences();
        $this->papershift->addAbsencesToPapershift($bambooAbsencesArray);
        $this->papershift->confirmPapershiftAbsences();
        $this->sendmail->sendEmail();
    }
}

$connector = new Connector();
$connector->addAbsencesFromBambooHRToPapershift();
