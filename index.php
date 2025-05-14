<?php
require_once(__DIR__ . '/autoload.php');

Introvert\Configuration::getDefaultConfiguration()->setHost('https://api.yadrocrm.ru/tmp')->setApiKey('key', '23bc075b710da43f0ffb50ff9e889aed');

$currentDate = new DateTime();
$dateFrom = $currentDate->format('d.m.Y');
$dateTo = $currentDate->modify('+30 days')->format('d.m.Y');

$dateFromTimestamp = (new DateTime())->setTimestamp(strtotime($dateFrom))->getTimestamp();
$dateToTimestamp = (new DateTime())->setTimestamp(strtotime($dateTo))->setTime(23, 59, 59)->getTimestamp();

$api = new Introvert\ApiClient();
$status = [47654035];
$count = 50;
$offset = 0;
$targetCustomFieldId = 974927;

print_r('==============START==============');
print_r('DATE FROM: ' . date('Y-m-d H:i:s', $dateFromTimestamp));
print_r('DATE TO: ' . date('Y-m-d H:i:s', $dateToTimestamp));

$filteredLeads = [];

do {
    try {
        $result = $api->lead->getAll([null], $status, [null], [null], $count, $offset);
        
        if (empty($result['result'])) {
            print_r('No more results');
            break;
        }

        foreach ($result['result'] as $lead) {
            if (!empty($lead['custom_fields'])) {
                foreach ($lead['custom_fields'] as $field) {
                    if ($field['id'] == $targetCustomFieldId && !empty($field['values'][0]['value'])) {
                        $appointmentDate = strtotime($field['values'][0]['value']);
                        
                        if ($appointmentDate >= $dateFromTimestamp && $appointmentDate <= $dateToTimestamp) {
                            $filteredLeads[] = $lead;
                        }
                    }
                }
            }
        }

        print_r('Processed: ' . count($result['result']));
        print_r('Filtered count: ' . count($filteredLeads));
        print_r('Offset: ' . $offset);

        if (count($result['result']) < $count) {
            break;
        }

        $offset += $count;
        sleep(1);
        
    } catch (Exception $e) {
        echo 'Exception when calling LeadApi->getAll: ', $e->getMessage(), PHP_EOL;
        break;
    }
} while (true);

// Выводим отфильтрованные сделки
print_r('==============FILTERED LEADS==============');
print_r($filteredLeads);

print_r('==============FINISH==============');
print_r('Total filtered leads: ' . count($filteredLeads));