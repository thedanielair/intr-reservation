$(window).load(function() {
    var today = new Date();
    var futureDate = new Date();
    futureDate.setDate(futureDate.getDate() + 30);

    var leadsByDate = {};
    calendarData.forEach(function(item) {
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
            dayData.leads.forEach(function(lead) {
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

    var specialDates = calendarData.map(function(item) {
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
        onClick: function(el, cell, date, data) {
            $('.gldp-flatwhite .core').removeClass('selected');
            $(cell).addClass('selected');
            showLeads(date);
            return false;
        },
        onRender: function() {
            calendarData.forEach(function(item) {
                var dateKey = item.date[0] + '-' + item.date[1] + '-' + item.date[2];
                var $cell = $('.gldp-flatwhite .core[date="' + dateKey + '"]');
                if ($cell.length && item.count > 0) {
                    $cell.append('<div class="day-badge">' + item.count + '</div>');
                }
            });

            var todayKey = today.getFullYear() + '-' + today.getMonth() + '-' + today.getDate();
            var $todayCell = $('.gldp-flatwhite .core[date="' + todayKey + '"]');
            if ($todayCell.length) {
                $todayCell.addClass('selected');
                showLeads(today);
            }
        }
    });
});