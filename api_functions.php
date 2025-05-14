<?php
function getLeadsCalendarData() {
    Introvert\Configuration::getDefaultConfiguration()
        ->setHost('https://api.yadrocrm.ru/tmp')
        ->setApiKey('key', '23bc075b710da43f0ffb50ff9e889aed');

    $api = new Introvert\ApiClient();
    $status = [47654035];
    $count = 50;
    $offset = 0;
    $targetCustomFieldId = 974927;

    $leadDates = [];

    do {
        try {
            $result = $api->lead->getAll([null], $status, [null], [null], $count, $offset);

            if (empty($result['result'])) {
                break;
            }

            foreach ($result['result'] as $lead) {
                if (!empty($lead['custom_fields'])) {
                    foreach ($lead['custom_fields'] as $field) {
                        if (isset($field['id']) && $field['id'] == $targetCustomFieldId && !empty($field['values'][0]['value'])) {
                            $date = date('Y-m-d', strtotime($field['values'][0]['value']));
                            if (!isset($leadDates[$date])) {
                                $leadDates[$date] = [];
                            }
                            $leadDates[$date][] = $lead;
                        }
                    }
                }
            }

            if (count($result['result']) < $count) {
                break;
            }

            $offset += $count;
            sleep(1);

        } catch (Exception $e) {
            error_log('API Error: ' . $e->getMessage());
            break;
        }
    } while (true);

    $calendarData = [];
    foreach ($leadDates as $date => $leads) {
        $dateParts = explode('-', $date);
        $calendarData[] = [
            'date' => [(int)$dateParts[0], (int)$dateParts[1]-1, (int)$dateParts[2]],
            'count' => count($leads),
            'leads' => array_map(function($lead) {
                return [
                    'id' => $lead['id'],
                    'name' => $lead['name'],
                    'price' => $lead['price'],
                    'status_id' => $lead['status_id']
                ];
            }, $leads)
        ];
    }

    return $calendarData;
}