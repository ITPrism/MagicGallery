var MagicgalleryHelper = {
    loadStyleFile: function(file, myCodeMirror) {

        jQuery.ajax({
            url: 'index.php?option=com_magicgallery&task=csseditor.getFile&format=raw&style_file='+file,
            method: "get",
            cache: false,
            dataType: "text",
            beforeSend: function() {
                jQuery("#ajax_loader").show();
            },
            success: function( responseText ) {

                // Hide loading animation
                jQuery("#ajax_loader").hide();

                // Check for error
                try {
                    var response = jQuery.parseJSON(responseText);

                    if(response && !response.success) {
                        MagicgalleryHelper.displayMessageFailure(response.title, response.text);
                        return;
                    }

                } catch (e){

                }

                // Set the code to the textarea
                myCodeMirror.setValue(responseText);
            }

        });
    }
};