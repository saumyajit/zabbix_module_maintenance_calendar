<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Calendar</title>

    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/daygrid/main.min.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/timegrid/main.min.css' rel='stylesheet' />

    <style>
        .fc-time { display: none !important; }
        html, body { margin: 0; padding: 0; height: 100%; }

        #calendar {
            width: 80%;
            margin: 0 auto;
            height: calc(100% - 60px);
        }

        .maintenance-details {
            position: fixed;
            top: 0;
            right: -50%;
            width: 50%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            transition: right 0.3s ease;
            z-index: 999;
        }
        .maintenance-details-content {
            background-color: #fff;
            height: 100%;
            overflow-y: auto;
            padding: 20px;
        }
        .maintenance-title {
            background-color: #333;
            color: #fff;
            padding: 10px;
            font-weight: bold;
            font-size: 1.2em;
        }
        .maintenance-info {
            background-color: #666;
            color: #fff;
            padding: 20px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            cursor: pointer;
        }

        #calendarTitle {
            text-align: center;
            background-color: #076699;
            color: white;
            padding: 10px;
            font-weight: bold;
            font-size: 1.5em;
        }
    </style>
</head>
<body>

    <div id="calendarTitle">Maintenance Calendar</div>
    <div id="calendar"></div>

    <div class="maintenance-details">
        <div class="maintenance-details-content">
            <span class="close" onclick="closeDetails()">&times;</span>
            <div class="maintenance-title">Event Details</div>
            <div class="maintenance-info" id="infoContent"></div>
        </div>
    </div>

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/daygrid/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/timegrid/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/interaction/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/moment/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.min.js'></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

    <script>

        function fetchMaintenanceData() {
            return new Promise(function(resolve, reject) {
                var requestData = {
                    jsonrpc: "2.0",
                    method: "maintenance.get",
                    params: {
                        output: "extend",
                        selectHostGroups: "extend",
                        selectHosts: "extend",
                        selectTimeperiods: "extend",
                        selectTags: "extend"
                    },
                    auth: "b0cfaa1c8d4f323c03a70aca86644f91b386fffbd0e33734a2b9f6a279d96653",
                    id: 1
                };

                $.ajax({
                    url: "https://127.0.0.1:8080/zabbix/api_jsonrpc.php",
                    method: "POST",
                    contentType: "application/json",
                    data: JSON.stringify(requestData),
                    success: function(response) {
                        if (response.result) resolve(response.result);
                        else reject("Error fetching maintenance.");
                    },
                    error: function(error) {
                        console.error("API error: " + JSON.stringify(error));
                        reject("Error fetching maintenance.");
                    }
                });
            });
        }

        function determineEventColor(startDate, endDate) {
            const now = moment();

            if (now.isBetween(startDate, endDate)) {
                const timeToEnd = endDate.diff(now, 'minutes');
                return timeToEnd <= 20 ? '#FFA500' : '#008000'; // Orange, Green
            }
            return '#FF0000'; // Red
        }

        var calendar;

        function initializeCalendar(maintenanceData) {
            var events = [];

            maintenanceData.forEach(function(maintenance) {
                maintenance.timeperiods.forEach(function(period) {
                    var startDate = moment.unix(period.start_date);
                    var endDate = moment.unix(period.start_date).add(period.period, 'seconds');

                    events.push({
                        title: `${maintenance.name} (${startDate.format('L')} - ${endDate.format('L')})`,
                        start: startDate.toISOString(),
                        end: endDate.toISOString(),
                        description: maintenance.hosts.length > 0 ?
                                     maintenance.hosts.map(host => host.name).join(', ') : '',
                        maintenanceDescription: maintenance.description || '',
                        collectingData: maintenance.maintenance_type === "0" ? 'Yes' : 'No',
                        backgroundColor: determineEventColor(startDate, endDate)
                    });
                });
            });

            if (calendar) calendar.destroy();

            calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                initialView: 'dayGridMonth',
                locale: 'en',   // 🔥 Hard-coded to English
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: events,
                eventClick: function(info) { openDetails(info.event); },
                eventDidMount: function(info) {
                    if (info.el.querySelector('.fc-list-event-title')) {
                        info.el.querySelector('.fc-list-event-title').style.backgroundColor =
                            info.event.backgroundColor;
                    }
                },
                dayMaxEvents: true,
                height: '100%',
                expandRows: true
            });

            calendar.render();
        }

        function openDetails(event) {
            var start = event.start ? moment(event.start).format('MMMM Do YYYY, h:mm:ss a') : '';
            var end = event.end ? moment(event.end).format('MMMM Do YYYY, h:mm:ss a') : '';

            document.getElementById('infoContent').innerHTML = `
                <div class="maintenance-info">
                    <p><strong>Title:</strong> ${event.title}</p>
                    <p><strong>Start:</strong> ${start}</p>
                    <p><strong>End:</strong> ${end}</p>
                    <p><strong>Hosts in Maintenance:</strong> ${event.extendedProps.description}</p>
                    <p><strong>Description:</strong> ${event.extendedProps.maintenanceDescription}</p>
                    <p><strong>Data Collection:</strong> ${event.extendedProps.collectingData}</p>  
                </div>
            `;

            document.querySelector('.maintenance-details').style.right = '0';
        }

        function closeDetails() {
            document.querySelector('.maintenance-details').style.right = '-50%';
        }

        document.addEventListener('DOMContentLoaded', function() {
            fetchMaintenanceData()
                .then(initializeCalendar)
                .catch(err => console.error(err));
        });

    </script>
</body>
</html>
