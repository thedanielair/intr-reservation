<?php
require_once(__DIR__ . '/autoload.php');
ini_set('display_errors', false);

Introvert\Configuration::getDefaultConfiguration()
    ->setHost('https://api.yadrocrm.ru/tmp')
    ->setApiKey('key', '23bc075b710da43f0ffb50ff9e889aed');

$api = new Introvert\ApiClient();

$status = [47654035];
$count = 50;
$offset = 0;
$targetCustomFieldId = 974927;

$filteredLeads = [];
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
        'date' => [(int) $dateParts[0], (int) $dateParts[1] - 1, (int) $dateParts[2]],
        'count' => count($leads),
        'leads' => array_map(function ($lead) {
            return [
                'id' => $lead['id'],
                'name' => $lead['name'],
                'price' => $lead['price'],
                'status_id' => $lead['status_id']
            ];
        }, $leads)
    ];
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>CRM Leads Calendar</title>
    <link href="/src/styles/glDatePicker.default.css" rel="stylesheet" type="text/css">
    <link href="/src/styles/glDatePicker.flatwhite.css" rel="stylesheet" type="text/css">
    <link href="/src/styles/glDatePicker.darkneon.css" rel="stylesheet" type="text/css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
            padding-top: 10%;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            height: 800px;
        }

        .calendar-wrapper {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .calendar-container {
            flex: 1;
            min-width: 400px;
        }

        .leads-container {
            flex: 1;
            min-width: 400px;
            border-left: 1px solid #eee;
            padding-left: 20px;
        }

        .legend {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .legend-item {
            display: flex;
            align-items: center;
        }

        .legend-color {
            width: 16px;
            height: 16px;
            margin-right: 8px;
            border-radius: 3px;
        }

        .day-title {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
            text-transform: uppercase;
        }

        .lead-item {
            padding: 8px;
            margin-bottom: 10px;
            background: #f9f9f9;
            border-radius: 4px;
            border-left: 3px solid #4CAF50;
        }

        .lead-item h4 {
            margin: 0 0 5px 0;
            color: #333;
        }

        .lead-item p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }

        /* Стили для календаря */

        .day-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            background: #f44336;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gldp-flatwhite .has-leads {
            background: #c8e6c9;
        }

        .gldp-flatwhite .overbooked {
            background: #ffcdd2;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Leads Calendar</h1>
        <div class="legend">
            <div class="legend-item">
                <div class="legend-color" style="background-color: #c8e6c9;"></div>
                <span>Days with leads (1-4)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #ffcdd2;"></div>
                <span>Overbooked days (5+)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #222222;"></div>
                <span>Today</span>
            </div>
        </div>

        <div class="calendar-wrapper">
            <div class="calendar-container">
                <div id="mainCalendar"></div>
            </div>
            <div class="leads-container" id="leadsContainer">
                <div class="day-title">Select a day to view leads</div>
                <div id="leadsList"></div>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
    <script src="/src/script/glDatePicker.min.js"></script>

    <script type="text/javascript">
        $(window).load(function () {
            var calendarData = <?php echo json_encode($calendarData); ?>;
            var today = new Date();
            var futureDate = new Date();
            futureDate.setDate(futureDate.getDate() + 30);

            var leadsByDate = {};
            calendarData.forEach(function (item) {
                var dateKey = item.date.join('-');
                leadsByDate[dateKey] = item;
            });

            function formatDate(date) {
                var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                return date.toLocaleDateString('ru-RU', options);
            }

            function showLeads(date) {
                var dateKey = date.getFullYear() + '-' + date.getMonth() + '-' + date.getDate();
                var dayData = leadsByDate[dateKey];
                var $leadsList = $('#leadsList');
                var $dayTitle = $('.day-title');

                $dayTitle.text(formatDate(date));
                $leadsList.empty();

                if (dayData && dayData.leads.length > 0) {
                    dayData.leads.forEach(function (lead) {
                        $leadsList.append(
                            '<div class="lead-item">' +
                            '<h4>' + lead.name + '</h4>' +
                            '<p>ID: ' + lead.id + ' | Price: ' + (lead.price || '0') + '</p>' +
                            '</div>'
                        );
                    });
                } else {
                    $leadsList.append('<p>No leads for this day</p>');
                }
            }

            var specialDates = calendarData.map(function (item) {
                return {
                    date: new Date(item.date[0], item.date[1], item.date[2]),
                    data: { count: item.count, leads: item.leads },
                    cssClass: item.count >= 5 ? 'overbooked' : 'has-leads'
                };
            });

            $('#mainCalendar').glDatePicker({
                showAlways: true,
                cssName: 'flatwhite',
                selectedDate: today,
                selectableDateRange: [
                    { from: today, to: futureDate }
                ],
                specialDates: specialDates,
                onClick: function (el, cell, date, data) {
                    $('.gldp-default .core').removeClass('selected');
                    $(cell).addClass('selected');
                    showLeads(date);
                    return false;
                },
                onRender: function () {
                    calendarData.forEach(function (item) {
                        var dateKey = item.date.join('-');
                        var $cell = $('.gldp-default .core[date="' + dateKey + '"]');
                        if ($cell.length && item.count > 0) {
                            $cell.append('<div class="day-badge">' + item.count + '</div>');
                        }
                    });

                    var todayKey = today.getFullYear() + '-' + today.getMonth() + '-' + today.getDate();
                    var $todayCell = $('.gldp-default .core[date="' + todayKey + '"]');
                    if ($todayCell.length) {
                        $todayCell.addClass('selected');
                        showLeads(today);
                    }
                }
            });
        });
    </script>
</body>

</html>