import {Calendar} from "@fullcalendar/core";
import interactionPlugin from "@fullcalendar/interaction";
import dayGridPlugin from "@fullcalendar/daygrid";
import listPlugin from "@fullcalendar/list";
import timeGridPlugin from "@fullcalendar/timegrid";

document.addEventListener( "DOMContentLoaded", function() {
	let calendar = null;
	let calendarEl = document.getElementById( "wp-zoom-calendar" );

	if ( ! calendarEl ) {
		return;
	}

	let args = JSON.parse( calendarEl.getAttribute( "data-args" ) );
	let calendarOpts = {
		height     : 'auto',
		plugins    : [interactionPlugin, dayGridPlugin, listPlugin, timeGridPlugin],
		editable   : false,
		aspectRatio: 1.8,
		eventClick : function( info ) {
			if ( info.event.url === "#" ) {
				info.jsEvent.preventDefault();
			}
		},
		headerToolbar: {
			left  : args.headerToolbarLeft,
			center: args.headerToolbarCenter,
			right : args.headerToolbarRight,
		},
		initialView: args.initialView,
	};

	let customCalendarOpts = jQuery( document.body ).triggerHandler( 'wp_zoom_calendar.before_render', [calendarOpts] );

	if ( typeof customCalendarOpts === 'object' ) {
		calendarOpts = customCalendarOpts;
	}

	calendar = new Calendar( calendarEl, calendarOpts );
	calendar.render();

	jQuery.ajax( {
		url : wp_zoom.ajax_url,
		data: {
			action  : 'wp_zoom_get_calendar_webinars',
			_wpnonce: wp_zoom.nonce
		},
		success: function ( response ) {
			response.data.forEach( event => calendar.addEvent( event ) );
		}
	} );

	/*
	jQuery( document.body ).on( 'wp_zoom_calendar.before_render', function ( e, calendarOpts ) {
		console.log( calendarOpts );
		calendarOpts.locale = 'fr';
  
		return calendarOpts;
	} );
    */
   
} );
