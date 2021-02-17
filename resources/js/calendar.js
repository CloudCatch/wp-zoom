import { Calendar } from '@fullcalendar/core';
import interactionPlugin from '@fullcalendar/interaction';
import dayGridPlugin from '@fullcalendar/daygrid';
import listPlugin from '@fullcalendar/list';
import timeGridPlugin from '@fullcalendar/timegrid';

document.addEventListener('DOMContentLoaded', function () {
    let calendar = null;
    let calendarEl = document.getElementById('calendar');
    let calendarOpts = {
        plugins: [interactionPlugin, dayGridPlugin, listPlugin, timeGridPlugin],
        editable: false,
        aspectRatio: 1.8,
        eventClick: function (info) {
            if (info.event.url === '#') {
                info.jsEvent.preventDefault();
            }
        },
        headerToolbar: {
            left: 'today prev,next',
            center: 'title',
            right: 'timeGridWeek,dayGridMonth,listWeek'
        },
        initialView: 'dayGridMonth'
    };

    jQuery.ajax({
        url: wp_zoom.ajax_url,
        data: {
            action: 'wp_zoom_get_calendar_webinars',
            _wpnonce: wp_zoom.nonce
        },
        success: function (response) {
            calendarOpts.events = response.data;

            let customCalendarOpts = jQuery(document.body).triggerHandler('wp_zoom_calendar.before_render', [calendarOpts]);

            if (typeof customCalendarOpts === 'object') {
                calendarOpts = customCalendarOpts;
            }

            calendar = new Calendar(calendarEl, calendarOpts);
            calendar.render();
        }
    });

    /*
    jQuery(document.body).on('wp_zoom_calendar.before_render', function (e, calendarOpts) {
        console.log(calendarOpts);
        calendarOpts.locale = 'fr';

        return calendarOpts;
    });
    */

});