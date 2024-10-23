(function ($, Drupal, drupalSettings) {

    // convert normal format date to UTC format date
    // date object format -> yyyymmddThhmmss
    function convertDateToUTC(date)
    {
        var year = ("0000" + (date.getFullYear().toString())).slice(-4);
        var month = ("00" + ((date.getMonth() + 1).toString())).slice(-2);
        var day = ("00" + ((date.getDate()).toString())).slice(-2);
        var hours = ("00" + (date.getHours().toString())).slice(-2);
        var minutes = ("00" + (date.getMinutes().toString())).slice(-2);
        var seconds = ("00" + (date.getSeconds().toString())).slice(-2);

        var time = 'T' + hours + minutes + seconds;

        var date_UTC = year + month + day + time;

        return date_UTC;
    }

    function converDateTimeForOutlookLive(datetime) {
      var split_date_time = datetime.split('T');
      var tmp_date = split_date_time[0], tmp_time = split_date_time[1];

      var tmp_date = [tmp_date.slice(0, 4), '-', tmp_date.slice(4)].join('');
      tmp_date = [tmp_date.slice(0, 7), '-', tmp_date.slice(7)].join('');

      tmp_time = [tmp_time.slice(0, 2), ':', tmp_time.slice(2)].join('');
      tmp_time = [tmp_time.slice(0, 5), ':', tmp_time.slice(5)].join('');

      return tmp_date + 'T' + tmp_time;
    }

    function converDateString(datetime) {
      //This function will convert yyyyMMddTHHmmssZ to yyyy-MM-ddTHH:mm:ssZ
      var split_date_time = datetime.split('T');
      var tmp_date = split_date_time[0], tmp_time = split_date_time[1];

      var tmp_date = [tmp_date.slice(0, 4), '-', tmp_date.slice(4)].join('');
      tmp_date = [tmp_date.slice(0, 7), '-', tmp_date.slice(7)].join('');

      tmp_time = [tmp_time.slice(0, 2), ':', tmp_time.slice(2)].join('');
      tmp_time = [tmp_time.slice(0, 5), ':', tmp_time.slice(5)].join('');

      return tmp_date + 'T' + tmp_time;
    }

    function convertUtcTimeToUserTimezone(datetime) {
      let date_obj = new Date(converDateString(datetime));
      let year = date_obj.getFullYear().toString();
      let month= (date_obj.getMonth() + 1).toString();
      let date = date_obj.getDate().toString();
      let hour = date_obj.getHours().toString();
      let minutes = date_obj.getMinutes().toString();
      let seconds = date_obj.getSeconds().toString();
      return year + str_pad(month) + str_pad(date) + 'T' + str_pad(hour) + str_pad(minutes) + str_pad(seconds);
    }

    function str_pad(n) {
      return String("00" + n).slice(-2);
    }

    function generateCalendarLink(id, type, subject, details, dateStart, dateEnd, locations) {
        var now_day = convertDateToUTC(new Date());

        // example ics file
        var calendarEvent = [
            'BEGIN:VCALENDAR',
            'PRODID:Calendar',
            'VERSION:2.0',
            'BEGIN:VEVENT',
            'UID:' + 'default',
            'CLASS:PUBLIC',
            'DESCRIPTION:' + details,
            'DTSTAMP;VALUE=DATE-TIME:' + now_day,
            'DTSTART;VALUE=DATE-TIME:' + dateStart,
            'DTEND;VALUE=DATE-TIME:' + dateEnd,
            'LOCATION:' + locations,
            'SUMMARY;LANGUAGE=en-us:' + subject,
            'TRANSP:TRANSPARENT',
            'END:VEVENT',
            'END:VCALENDAR'
        ];
        // get calendar content for desktop calendar file
        calendarEvent = calendarEvent.join("\n");

        var hreflink = '';

        if (configuration[type]['link_type'] == 'link') {

          switch (type) {
            case 'yahoo':
              dateStart = convertUtcTimeToUserTimezone(dateStart);
              dateEnd = convertUtcTimeToUserTimezone(dateEnd);
              break;
            case 'outlook_online':
              dateStart = converDateTimeForOutlookLive(dateStart);
              dateEnd = converDateTimeForOutlookLive(dateEnd);
              details = details.replace(/(?:\r\n|\r|\n)/g, '<br>');
              break;
          }

          // encode
          subject = encodeURI(subject);
          details = encodeURI(details);
          locations = encodeURIComponent(locations);

          hreflink = configuration[type]['link']['value'] +
              configuration[type]['link']['param1'] + (subject) +
              configuration[type]['link']['param2'] + (details) +
              configuration[type]['link']['param3'] + (dateStart) +
              configuration[type]['link']['param4'] + (dateEnd) +
              configuration[type]['link']['param5'] + (locations);
        } else if (configuration[type]['link_type'] == 'link_download') {
            hreflink = configuration[type]['link']['value'] + "/" + encodeURIComponent(id) +
                "/" + encodeURIComponent(subject) +
                "/" + encodeURIComponent(dateStart) +
                "/" + encodeURIComponent(dateEnd) +
                "/" + encodeURIComponent(details.replaceAll('/', '|')) +
                "/" + encodeURIComponent(locations);
            $('#' + id).attr("download", "event.ics");
        }

        $('#' + id).attr("href", hreflink);
    }
    var configuration = {
        google: {
            link: {
                value: "https://calendar.google.com/calendar/r/eventedit?",
                param1: "text=",
                param2: "&details=",
                param3: "&dates=",
                param4: "/",
                param5: "&location="
            },
            link_type: "link",
        },
        outlook_online: {
            link: {
                value: "https://outlook.live.com/owa?path=/calendar/view/month&rru=addevent",
                param1: "&subject=",
                param2: "&body=",
                param3: "&startdt=",
                param4: "&enddt=",
                param5: "&location="
            },
            link_type: "link",
        },
        yahoo: {
            link: {
                value: "https://calendar.yahoo.com/?v=60&view=d&type=20",
                param1: "&TITLE=",
                param2: "&DESC=",
                param3: "&ST=",
                param4: "&ET=",
                param5: "&in_loc="
            },
            link_type: "link",
        },
        outlook: {
            link: {
                // value: "data:text/plain",
                value: "/webcal",
            },
            link_type: "link_download",
        },
        apple: {
            link: {
                // value: "webcal://" + window.location.hostname + "/webcal",
                value: "/webcal",
            },
            link_type: "link_download",
        }
    }

    var isEventsDroped = {};
    var listEvents = [];

    for (var id_event in drupalSettings.event_params) {
        if (drupalSettings.event_params.hasOwnProperty(id_event)) {
            isEventsDroped[id_event] = false;
            listEvents.push(id_event);

            var params = JSON.parse(drupalSettings.event_params[id_event]);

            var id_event = params.id_event;
            var subject = params.subject;
            var dateStart = params.date_start;
            var dateEnd = params.date_end;
            var details = params.details;
            var locations = params.locations;

            generateCalendarLink(id_event + '-appleical', 'apple', subject, details, dateStart, dateEnd, locations);
            generateCalendarLink(id_event + '-google', 'google', subject, details, dateStart, dateEnd, locations);
            // generateCalendarLink(id_event + '-outlook', 'outlook', subject, details, dateStart, dateEnd, locations);
            generateCalendarLink(id_event + '-outlookcom', 'outlook_online', subject, details, dateStart, dateEnd, locations);
            generateCalendarLink(id_event + '-yahoo', 'yahoo', subject, details, dateStart, dateEnd, locations);
        }
    }

    // toggle drop down list block
    function toggleDropDown(id_event)
    {
        if (!isEventsDroped[id_event]) {
            // drop list block
            $('#' + id_event + "-drop").attr("class", "addeventatc_dropdown topdown addeventatc-selected");
        } else {
            // close list block
            $('#' + id_event + "-drop").attr("class", "addeventatc_dropdown topdown addeventatc-hide");
        }
        isEventsDroped[id_event] = !isEventsDroped[id_event];
    }

    listEvents.forEach(function(id_event) {
        $('#' + id_event).click(function() {
            toggleDropDown(id_event);
        });

        $('#' + id_event).click(function(event){
            event.stopPropagation();
        });
    });

    // run when click outside drop block
    $('html').click(function() {
        listEvents.forEach(function(id_event) {
            if (isEventsDroped[id_event] == true) {
                $('#' + id_event + "-drop").attr("class", "addeventatc_dropdown topdown addeventatc-hide");
                isEventsDroped[id_event] = false;
            }
        });
    });
}(jQuery, Drupal, drupalSettings));
