<?php
require_once(__DIR__ . '/autoload.php');
require_once(__DIR__ . '/api_functions.php');

ini_set('display_errors', false);

$calendarData = getLeadsCalendarData();

include(__DIR__ . '/src/templates/calendar.php');
?>