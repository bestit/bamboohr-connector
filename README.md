# BAMBOOHR-CONNECTOR

## Introduction

This repository contains the code for the project `BambooHR-connector`, which automatically imports the absences for all employees from BambooHR to Papershift.
## Key Facts

Product Owner / Project Manager: [Christoph Batik](mailto:christoph.batik@bestit-online.at)

Developer: [Georgi Damyanov](mailto:georgi.damyanov@bestit-online.at)

## Development Setup

### Requirements

- MAMP or XAMPP
- PHP 7.1

### Download

- `Git clone` this repository into `MAMP/XAMPP`
- Run `composer install`

### Configuration

To use the connector first you have to configure it.

- Open Config.php
- Fill the missing `keys`.

- `bamboohr`
    - `api_token` - The API key for BambooHR
    - `company` - company name
    - `filter_enabled` - `enables/disables` the absences filter (look `filter_array`)
    - `filter_array` - which absences to be `filtered/removed`
    - `date` - Only the absences from this date will be taken. You can choose between `yesterday`, `today`, `tomorrow` or give `custom Y-m-d`.
    - `status` - Which absences to get. You can choose from `approved`, `denied`, `superceded`, `requested`, `canceled`

- `papershift`
    - `api_token` - The API key for Papershift
    - `api_absences_url` - The Papershift API absences URL
    - `api_absences_confirm_url` - The Papershift API absences/confirm URL
    
    Papershift requires `start` and `end time` for the absences
    
    - `start_time_days` - Start time for the absence
    - `end_time_days` - End time for the absence
    - `full_day` - Full day
    
    Papershift does not allow to enter the absence hours. That is why every time there are  hours (2,3,4 hours) they are counted from `00:00:00` onward. ( the hours are calculated inside `PapershiftAbsences.php`)
    
    - `start_time_hours` - The start time for the hours 
    
- `mail`
    - `smtp_host` - Your e-mail host
    - `smtp_auth` - If Authentication should be enabled
    - `username` - Your e-mail
    - `password` - Your e-mail password
    - `smtp_secure` - Security
    - `port` - Port
    - `send_from` - Sender (e-mail)
    - `send_to` - Recipient (e-mail)

### Run Script

Once you are done with the configuration, to run the script:

- Open `Connector.php` inside your browser
- It will take couple of minutes, which highly depends on how many absences there are
- You will be notified per e-mail if the import was successful or failed

## Testing

There are no tests at this point, you have to test manually.

## Additional Documentation

### Error Logs

There are two logs inside the `log` folder.

- `tmp_error_log.txt` which is used to temporarily store the errors.
- `global_error_log.txt` contains all the errors.

Errors from `tmp_error_log.txt` are send via e-mail. Then it appends the errors to the `global_error_log.txt` and clears itself. Next time the script runs `tmp_error_log` will be empty. That way you don't get all errors per e-mail, but only the errors from the current day.