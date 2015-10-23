jQuery(document).ready(function() {
	
	// Validation script
    Joomla.submitbutton = function(task){
        if (task == 'gallery.cancel' || document.formvalidator.isValid(document.id('gallery-form'))) {
            Joomla.submitform(task, document.getElementById('gallery-form'));
        }
    };
    
});

