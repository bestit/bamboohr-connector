<?php

declare(strict_types=1);

namespace BambooHRConnector;

use Exception;

/**
 * Class PapershiftAbsences
 *
 * @package BambooHRConnector
 * @author Georgi Damyanov <georgi.damyanov@bestit-online.at>
 */
class PapershiftAbsences
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
     * Instance of Config
     *
     * @var $config Config
     */
    private $config;

    /**
     * PapershiftAbsences constructor.
     *
     * @param Config $config Config Instance
     * @param Logger $logger Logger Instance
     * @param SendMail $sendmail SendMail Instance
     */
    public function __construct(Config $config, Logger $logger, SendMail $sendmail)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->sendmail = $sendmail;
    }

    /**
     * Function addAbsencesToPapershift
     *
     * - Go through all the absences
     * - Convert them to json
     * - Add them to Papershift
     *
     * @param array $array Contains all absences from Bamboo
     *
     * @return void
     * @throws Exception
     */
    public function addAbsencesToPapershift($array): void
    {
        $ch = curl_init($this->config->getConfig('papershift', 'api_absences_url'));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        for ($i = 0; $i < sizeof($array); $i++) {
            $convertedArrayElem = $this->convertTimeToISO8601($array[$i]);
            $papershiftAbsence = $this->createPapershiftAbsencesArray($convertedArrayElem);

            $JSONpapershiftAbsence = $this->fromArrayToJson($papershiftAbsence);

            curl_setopt($ch, CURLOPT_POSTFIELDS, $JSONpapershiftAbsence);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($JSONpapershiftAbsence),
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

            $result = curl_exec($ch);
            $response = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($response < 200 || $response > 299) {
                $this->logger->writeErrorToFile('Result: '.$result.' | Error Code: '.$response);
            }
        }
    }

    /**
     * Function confirmPapershiftAbsences()
     *
     * - Call getPapershiftAbsences() to get all absences for the selected date
     * - Get the ID's for all absences
     * - Confirm
     *
     * @return void
     * @throws Exception
     */
    public function confirmPapershiftAbsences(): void
    {
        $absencesArray = $this->getPapershiftAbsences();
        $ch = curl_init($this->config->getConfig('papershift', 'api_absences_confirm_url'));

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');

        for($i = 0; $i < sizeof($absencesArray['absences']); $i++) {
            $absenceJSON = $this->fromArrayToJson([
                'api_token' => $this->config->getConfig('papershift', 'api_token'),
                'absence' => [
                    'id' => $absencesArray['absences'][$i]['id']
                ]
            ]);

            curl_setopt($ch, CURLOPT_POSTFIELDS, $absenceJSON);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($absenceJSON),
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

            $result = curl_exec($ch);
            $response = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($response < 200 || $response > 299) {
                $this->logger->writeErrorToFile('Result: '.$result.' | Error Code: '.$response);
            }
        }
    }

    /**
     * Function getPapershiftAbsences()
     *
     * Get all absences between range_start and range_end (ISO) and return them as an array
     *
     * @return array
     */
    public function getPapershiftAbsences(): array
    {
        $ch = curl_init($this->config->getConfig('papershift', 'api_absences_url'));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        $requestAbsence = $this->fromArrayToJson([
            'api_token' => $this->config->getConfig('papershift','api_token'),
            'range_start' => $this->config->getConfig('bamboohr', 'date'),
            'range_end' => $this->config->getConfig('bamboohr', 'date')
        ]);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestAbsence);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($requestAbsence),
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        $result = curl_exec($ch);
        $resultArray = $this->fromJsonToArray($result);

        return $resultArray;
    }

    /**
     * Function convertTimeToISO8601
     *
     * Convert absence time from Y-m-d to ISO8601 (required by Papershift)
     *
     * The function gmdate converts hours to h:i:s (f.e 2.5556h = 02:33:20)
     *
     * @param array $array Takes the Bamboo-Absences array
     *
     * @return array Returns same array with ISO8601 time
     */
    public function convertTimeToISO8601($array): array
    {
        if ($array['unit'] === 'days') {
            $array['start'] = (string) $array['start'].$this->config->getConfig('papershift', 'start_time_days');
            $array['end'] = (string) $array['end'].$this->config->getConfig('papershift', 'end_time_days');
            $array['full_day'] = true;
        } elseif ($array['unit'] === 'hours') {
            $array['start'] = (string) $array['start'].$this->config->getConfig('papershift', 'start_time_hours');
            $array['end'] = (string) $array['end'].'T'.gmdate('H:i:s', (int) floor($array['amount'] * 3600)).'+01:00';
        }
        return $array;
    }

    /**
     * Function createPapershiftAbsencesArray
     *
     * Convert BambooHR array to Papershift array with the correct structure
     *
     * @param array $array Takes the Bamboo-Absences array
     *
     * @return array Returns a valid structured Papershift array
     */
    public function createPapershiftAbsencesArray($array): array
    {
        return [
            'api_token' => $this->config->getConfig('papershift', 'api_token'),
            'absence' => [
                'absence_type_external_id' => $array['type'],
                'user_external_id' => (string) $array['id'],
                'starts_at' => (string) $array['start'],
                'ends_at' => $array['end'],
                'full_day' => $array['full_day']
            ]
        ];
    }

    /**
     * Function fromArrayToJson
     *
     * Convert an array to json
     *
     * @param array $array Takes an array
     *
     * @return string Returns a json string
     */
    public function fromArrayToJson($array): string
    {
        return json_encode($array, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Function fromJsonToArray()
     *
     * Convert a JSON string to an array
     *
     * @param string $json Takes a JSON string
     *
     * @return array Returns an array
     */
    public function fromJsonToArray($json): array
    {
        return json_decode($json, true);
    }
}
