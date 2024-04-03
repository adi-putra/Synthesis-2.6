<?php 

include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

$hostid = $_GET["hostid"];

$hostArr = "";
foreach ($hostid as $hostID) {
  $hostArr .= "hostid[]=" . $hostID . "&";
}

//time variables
$timefrom = $_GET['timefrom'] ?? strtotime("today");
$timetill = $_GET['timetill'] ?? strtotime("now");

?>

<html>
    <head>
        <style>
          #calendar {
              height: 700px;
          }
          .fc-month-view span.fc-title{
              white-space: normal;
          }
          .popover {
            position: absolute;
            z-index: 9999;
          }
        </style>  
    </head>
    <body>
        <div id='calendar'></div>
    </body>
</html>

<script>

var hostArr = '<?php echo $hostArr; ?>';

var calendarEl = document.getElementById('calendar');

var calendar = new FullCalendar.Calendar(calendarEl, {
  themeSystem: 'standard',
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay'
  },
  events: 'application/veeam_overview/backup/cal_data.php?' + hostArr,
  eventDisplay: 'block',
  eventTimeFormat: { 
    hour: '2-digit',
    minute: '2-digit',
    meridiem: "short"
  },
  scrollTime: '00:00:00',
  initialView: 'dayGridMonth',
  showNonCurrentDates: false,
  dayMaxEvents: true,
  initialDate: '<?php echo date("Y-m-d", $timefrom); ?>',
  eventDidMount: function(info) {
    $(info.el).popover({
        placement:'auto',
        trigger : 'hover',
        content: info.event.extendedProps.desc,
        html: true,
        container:'body'
    })
  },
});

calendar.render();

</script>