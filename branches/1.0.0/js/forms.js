$ = jQuery;

$('document').ready(function() {

	//	Set up Date Pickers
	$('.datepicker').datepicker({
		dateFormat: 'yy-mm-dd', 
		changeMonth: true, 
		changeYear: true, 
		gotoCurrent: true,
		showOn: 'button',
		buttonImage: jobman_config.url + '/images/calendar.gif',
		buttonImageOnly: true
	});

});

