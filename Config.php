<?php

declare(strict_types=1);

namespace BambooHRConnector;

/**
 * Class Config
 *
 * @package BambooHRConnector
 * @author Georgi Damyanov <georgi.damyanov@bestit-online.at>
 */
class Config
{
    /**
     * Config array
     *
     * @var $config array
     */
    private $config;

    /**
     * Logger array
     *
     * @var $logger Logger
     */
    private $logger;

    /**
     * Config constructor.
     *
     * @param Logger $logger Logger Instance
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;

        $this->config = [
            'bamboohr' => [
                'api_token' => '8f912006d3d165af282ea16be488d668d2d41915',
                'company' => 'bestitonline',
                'filter_enabled' => true,
                'filter_array' => ['Abwesend ','Abwesend', 'Geschäftsreise', 'Homeoffice'],
                'date' => date('Y-m-d', strtotime('today')),
                'status' => 'approved'
            ],
            'papershift' => [
                'api_token' => '8f912006d3d165af282ea16be488d668d2d41915',
                'api_absences_url' => 'https://app.papershift.com/public_api/v1/absences',
                'api_absences_confirm_url' => 'https://app.papershift.com/public_api/v1/absences/confirm',
                'start_time_days' => 'T08:00:00+01:00',
                'end_time_days' => 'T17:00:00+01:00',
                'start_time_hours' => 'T00:00:00+01:00',
                'full_day' => true,
            ],
            'mail' => [
                'smtp_host' => 'dsadasdsa',
                'smtp_auth' => true,
                'username' => 'dasdsadsa',
                'password' => 'dsadsadsa',
                'smtp_secure' => 'dasdsadsa',
                'port' => 'dsadasdas',
                'send_from' => 'dsadsadsa',
                'send_to' => 'dsadsadsa'
            ]
        ];
    }

    /**
     * Function getConfig
     *
     * Get the config array.
     *
     * @param String $category Name of the category (bamboohr/papershift/mail)
     * @param String $key Name of the key
     *
     * @return Mixed mixed If there is no error it returns Array
     */
    public function getConfig($category, $key)
    {
        if (!array_key_exists($category, $this->config)) {
            $this->logger->writeErrorToFile("The category '".$category."' does not exist!");
            die("The category '".$category."' does not exist!");
        }

        if (!array_key_exists($key, $this->config[$category])) {
            $this->logger->writeErrorToFile("The key '".$key."' inside category '".$category."' does not exist!");
            die("The key '".$key."' inside category '".$category."' does not exist!");
        } else {
            if ($this->config[$category][$key] === '' || is_null($this->config[$category][$key])) {
                $this->logger->writeErrorToFile("The key '".$key."' does not contain any value!");
                die("The key '".$key."' does not contain any value!");
            }
        }
        return $this->config[$category][$key];
    }
}
