<?php

declare(strict_types=1);

namespace BambooHRConnector;

/**
 * Class BambooHRAbsences
 *
 * @package BambooHRConnector
 * @author Georgi Damyanov <georgi.damyanov@bestit-online.at>
 */
class BambooHRAbsences
{
    /**
     * Instance of SendMail
     *
     * @var $sendmail SendMail
     */
    private $sendmail;

    /**
     * Instance of Logger
     *
     * @var $logger Logger
     */
    private $logger;

    /**
     * The employee data
     *
     * @var $employeesXML \SimpleXMLElement
     */
    private $employeesXML;

    /**
     * Instance of BambooAPI
     *
     * @var $bambooAPI BambooAPI
     */
    private $bambooAPI;

    /**
     * Instance of Config
     *
     * @var $config Config
     */
    private $config;

    /**
     * The employee absences
     *
     * @var $absencesArray array
     */
    private $absencesArray = [];

    /**
     * The number of employees
     *
     * @var $employeeCounter int
     */
    private $employeeCounter;

    /**
     * BambooHRAbsences constructor.
     *
     * @param Config $config Config Instance
     * @param BambooAPI $bambooAPI BambooAPI Instance
     * @param Logger $logger Logger Instance
     * @param SendMail $sendmail SendMail Instance
     */
    public function __construct(Config $config, BambooAPI $bambooAPI, Logger $logger, SendMail $sendmail)
    {
        $this->config = $config;
        $this->bambooAPI = $bambooAPI;
        $this->logger = $logger;
        $this->sendmail = $sendmail;
    }

    /**
     * Function getBambooHRAbsences()
     *
     * Fetch the absences from BambooHR
     *  - Go through all the employees
     *  - Get all the absences (with set date) for all the employees
     *
     * @return array Returns an array
     */
    public function getBambooHRAbsences(): array
    {
        $this->bambooAPI->setSecretKey($this->config->getConfig('bamboohr', 'api_token'));
        $employeeDirectoryRequestArray = $this->bambooAPI->getDirectory();

        $httpcode = $employeeDirectoryRequestArray->statusCode;
        if ($httpcode < 200 || $httpcode > 299) {
            $this->logger->writeErrorToFile('Connection to BambooHR could not be established. Error Code: '.$httpcode);
        }

        $this->employeesXML = $employeeDirectoryRequestArray->getContentXML();
        $this->employeeCounter = $this->getNumberOfEmployees($this->employeesXML);

        while ($this->employeeCounter > -1) {
            $employeeExternalID = $this->getEmployeeExternalID($this->employeesXML, $this->employeeCounter);

            $timeOffRequestsArray = $this->bambooAPI
                ->getTimeOffRequests($this->config->getConfig('bamboohr', 'date'), $this->config
                    ->getConfig('bamboohr', 'date'), $this->config
                    ->getConfig('bamboohr', 'status'), '', $employeeExternalID);
            $timeOffRequestsXML = $timeOffRequestsArray->getContentXML();

            for ($i = 0; $i < sizeof($timeOffRequestsXML->request); $i++) {
                $absence = [
                    'id' => $employeeExternalID,
                    'start' => $this->config->getConfig('bamboohr', 'date'),
                    'end' => $this->config->getConfig('bamboohr', 'date'),
                    'type' => (string) $timeOffRequestsXML->request[$i]->type,
                    'unit' => (string) $timeOffRequestsXML->request[$i]->amount->attributes()['unit'],
                    'amount' => (string) $timeOffRequestsXML->request[$i]->amount,
                    'full_day' => false
                ];

                if ($this->config->getConfig('bamboohr', 'filter_enabled') === false ||
                    !in_array($absence['type'], $this->config->getConfig('bamboohr', 'filter_array'))
                ) {
                    $this->absencesArray[] = $absence;
                }
            }
            $this->employeeCounter--;
        }
        return $this->absencesArray;
    }

    /**
     * Function getNumberOfEmployees
     *
     * Get the number of employees
     *
     * @param \SimpleXMLElement $simplexml Array with the employees
     *
     * @return int returns (number of employees - 1) (because count starts from 0)
     */
    private function getNumberOfEmployees($simplexml): int
    {
        return count($simplexml->employees->employee)-1;
    }

    /**
     * Function getEmployeeExternalID
     *
     * Get the external ID of an employee
     *
     * @param \SimpleXMLElement $simplexml Array with the employees
     * @param int $index employee counter
     *
     * @return int returns the externalID of an employee
     */
    private function getEmployeeExternalID($simplexml, $index): int
    {
        return (int) $simplexml->employees->employee[$index]->attributes()['id'];
    }
}
