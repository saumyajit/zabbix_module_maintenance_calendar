<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title><?php echo _('Maintenance Calendar'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet" />
    <style>
        html, body {
            margin: 0; padding: 0; height: 100%;
            font-family: Arial, sans-serif;
        }
        #calendar {
            max-width: 900px;
            margin: 40px auto;
        }
    </style>
</head>
<body>
    <div id="calendar"></div>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: function(fetchInfo, successCallback, failureCallback) {
                $.ajax({
                    url: 'maintenance.calendar',
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        if(data.error){
                            alert('Error fetching data: ' + data.error);
                            failureCallback(data.error);
                            return;
                        }
                        var events = [];
                        data.forEach(function(item) {
                            item.timeperiods.forEach(function(tp) {
                                events.push({
                                    title: item.name,
                                    start: tp.timeperiod_type === 0 ? new Date(tp.start_date * 1000).toISOString() : null,
                                    end: tp.timeperiod_type === 0 ? new Date(tp.end_date * 1000).toISOString() : null,
                                    allDay: true
                                });
                            });
                        });
                        successCallback(events);
                    },
                    error: function() {
                        alert('Failed to load maintenance data');
                        failureCallback('AJAX error');
                    }
                });
            }
        });
        calendar.render();
    });
    </script>
</body>
</html>
