jQuery(document).ready(function() {
	
	// Validation script
    Joomla.submitbutton = function(task){
        if (task == 'gallery.cancel' || document.formvalidator.isValid(document.id('project-form'))) {
            Joomla.submitform(task, document.getElementById('project-form'));
        }
    };
    
});

