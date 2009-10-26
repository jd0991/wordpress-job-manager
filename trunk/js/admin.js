jobman_field_template = '';
jobman_new_count = 0;

function jobman_sort_field_up(div) {
	jQuery(div).parent().parent().prev().before(jQuery(div).parent().parent());
}

function jobman_sort_field_down(div) {
	jQuery(div).parent().parent().next().after(jQuery(div).parent().parent());
}

function jobman_field_delete(div) {
	var id = jQuery(div).parent().parent().find('[name^=jobman-fieldid]').attr('value');
	
	var list = jQuery('#jobman-delete-list').attr('value');
	if(list == "") {
		list = id;
	}
	else {
		list = list + ',' + id;
	}

	jQuery('#jobman-delete-list').attr('value', list);
	
	jQuery(div).parent().parent().remove();
}

function jobman_field_new() {
	jobman_new_count++;

	var htmlDOM = jQuery(jobman_field_template);
	//jQuery('input', htmlDOM).each(jobman_nameFilter);

	jQuery('#jobman-fieldnew').before(jQuery(jobman_field_template));
}

jobman_nameFilter = function () {
	var name = jQuery(this).attr('name');
	if(name == 'jobman-categories') {
		jQuery(this).attr('name', name + '[new][' + jobman_new_count + '][]');
	}
}