jQuery(document).ready(function() {
	
	// Validation script
    Joomla.submitbutton = function(task){
        if (task == 'entity.cancel' || document.formvalidator.isValid(document.id('entity-form'))) {
            Joomla.submitform(task, document.getElementById('entity-form'));
        }
    };

	// Style file input
	jQuery(".fileupload").fileinput();

});

