jQuery(document).ready(function() {
	
	// Validation script
    Joomla.submitbutton = function(task){
        if (task == 'resource.cancel' || document.formvalidator.isValid(document.id('resource-form'))) {
            Joomla.submitform(task, document.getElementById('resource-form'));
        }
    };

	// Style file input
	jQuery(".fileupload").fileinput();

});

