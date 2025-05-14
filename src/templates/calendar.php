<!DOCTYPE html>
<html>
<head>
    <title>CRM Leads Calendar</title>
    <link href="/src/styles/glDatePicker.default.css" rel="stylesheet">
    <link href="/src/styles/glDatePicker.flatwhite.css" rel="stylesheet">
    <link href="/src/styles/main.css" rel="stylesheet">
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
    <script src="/src/script/main.js"></script>
    
    <script type="text/javascript">
        var calendarData = <?php echo json_encode($calendarData); ?>;
    </script>
</body>
</html>