/**
 * Handles admin-side JS for the SmartPost grading component
 */
(function($) {
    sp_admin.sp_catGrading = {

        /**
         * Required for all post component JS objects.
         * Used in sp_globals.SP_TYPES to determine which
         * methods to call for different post component types
         */
        setTypeID: function(){
            if(sp_admin){
                var types = sp_admin.SP_TYPES;

                //!Important - the raw name of the type
                if(types['Grading']){
                    this.typeID = types['Grading']; // Get the type ID of our object
                    sp_admin.SP_TYPES['Grading'] = this; // Overwrite it with this object
                }
            }else{
                return 0;
            }
        },

        /**
         * Handler for adding a new grading field
         * @param buttonElem
         */
        addNewFieldHandler: function( buttonElem ){
            var self = this;
            buttonElem.click( function(){
                var compid = $(this).data( 'compid' );
                var fieldNameElem = $( '#' + self.FIELD_INPUT_PREFIX + compid );
                var fieldTypeElem = $( '#' + self.GRADING_TYPE_PREFIX + compid );

                // Show loader
                $('#' + self.SUBMIT_LOADER_GIF_PREFIX_ID + compid).show();

                // Add the new field
                self.saveNewField( fieldNameElem, fieldTypeElem, compid );
            });
        },

        /**
         * Creates the field and saves it
         * @param fieldNameElem
         * @param fieldTypeElem
         * @param compID
         */
        saveNewField: function( fieldNameElem, fieldTypeElem, compID ){
            var self = this;
            $.ajax({
                url  : SP_AJAX_URL,
                type : 'POST',
                data : {
                    action: 'sp_grading_save_field',
                    nonce: SP_NONCE,
                    compid: compID,
                    fieldname: fieldNameElem.val(),
                    fieldtype: fieldTypeElem.val()
                },
                dataType : 'html',
                success: function( response ){
                    $( '#' + self.GRADING_FIELD_CONTAINER + compID ).append( response );

                    // Find the elems that we need to initialize
                    var editableFieldElemID = $(response).find('.grading-field-editable:first').attr('id');
                    var deleteElemID = $(response).find('.' + self.DELETE_FIELD_CLASS).attr('id');

                    // Bind handlers to elems
                    self.initEditableFieldName( $('#' + editableFieldElemID) ); // initialize editable field name
                    self.initDeleteHandler( $('#'+ deleteElemID) ); // initialize deleting fields

                    $('#' + self.SUBMIT_LOADER_GIF_PREFIX_ID + compID).hide(); // hide loader
                },
                error    : function(jqXHR, statusText, errorThrown){
                    if(smartpost.sp_postComponent)
                        smartpost.sp_postComponent.showError('Status: ' + statusText + ', Error Thrown:' + errorThrown);
                }
            });
        },
        /**
         * Class an AJAX function to save an existing field's name
         * @param newName
         * @param fieldKey
         * @param compID
         */
        saveFieldName: function( newName, fieldKey, compID ){
            $.ajax({
                url		 : SP_AJAX_URL,
                type     : 'POST',
                data	 : {
                    nonce  : SP_NONCE,
                    action : 'sp_grading_set_field_name',
                    fieldName : newName,
                    fieldKey  : fieldKey,
                    compid    : compID
                },
                dataType : 'json',
                success  : function(response, statusText, jqXHR){
                    console.log( response );
                },
                error    : function(jqXHR, statusText, errorThrown){
                    sp_admin.adminpage.showError(errorThrown, null);
                }
            });
        },
        /**
         * Makes all the name fields editable using jQuery editable
         * @param fieldElems
         */
        initEditableFieldName: function( fieldElems ){
            var self = this;
            fieldElems.editable(function(value, settings){
                    var fieldKey = $(this).data('fieldkey');
                    var compID   = $(this).data('compid');

                    self.saveFieldName( value, fieldKey, compID );
                    return value;
                },
                {
                    placeholder: 'Click to add a grading field name',
                    onblur     : 'submit',
                    cssclass   : 'editableCatCompTitle',
                    maxlength  : 35
                }
            )
        },

        /**
         * Delete a field key
         * @param fieldKey
         * @param compID
         */
        deleteField: function( fieldKey, compID ){
            var self = this;
            $.ajax({
                url		 : SP_AJAX_URL,
                type     : 'POST',
                data	 : {
                    nonce  : SP_NONCE,
                    action : 'sp_grading_delete_field',
                    fieldKey  : fieldKey,
                    compid    : compID
                },
                dataType : 'json',
                success  : function(response, statusText, jqXHR){
                    $('#' + self.GRADING_FIELD_ROW_PREFIX_ID + compID + '-' + fieldKey).remove();
                },
                error    : function(jqXHR, statusText, errorThrown){
                    sp_admin.adminpage.showError(errorThrown, null);
                }
            });
        },

        /**
         * Handles deleting grading fields
         * @param deleteElem
         */
        initDeleteHandler: function( deleteElem ){
            var self = this;
            deleteElem.click(function(){
                var fieldKey = $(this).data('fieldkey');
                var compid   = $(this).data('compid');
                $('#' + self.GRADING_FIELD_ROW_PREFIX_ID + compid + '-' + fieldKey).html( '<td><img src="' + SP_IMAGE_PATH + '/loading.gif"> Removing field...</td>');
                self.deleteField( fieldKey, compid );
            });
        },
        /**
         * Initialize component if it was created dynamically
         */
        initComponent: function(componentElem){
            var self = this;

            // Initialize new field button
            var addNewFieldButton = componentElem.find( '.' + self.ADD_NEW_FIELD_BUTTON_CLASS );
            self.addNewFieldHandler( addNewFieldButton );

            // Initalize the CK Editor
            if( smartpost.sp_post ){
                var spEditor = componentElem.find( '.sp-editor-content' );
                smartpost.sp_post.initCkEditors( spEditor );
            }

        },
        /**
         * Initialize JS for the grading cmoponent
         */
        init: function(){
            var self = this;

            // initialize element ids
            self.DELETE_FIELD_CLASS      = 'sp-grading-delete';
            self.FIELD_INPUT_PREFIX      = 'sp-new-grading-field-';
            self.GRADING_TYPE_PREFIX     = 'grading-type-';
            self.GRADING_FIELD_CONTAINER = 'sp-grading-field-container-';
            self.ADD_NEW_FIELD_BUTTON_CLASS   = 'submit-new-grading-field';
            self.GRADING_FIELD_EDITABLE_CLASS = 'grading-field-editable';
            self.GRADING_FIELD_ROW_PREFIX_ID = 'sp-field-row-'; //sp-field-row-<COMP_ID>-<FIELD_KEY>, @see self.deleteField
            self.SUBMIT_LOADER_GIF_PREFIX_ID = 'sp-grading-submit-loader-';

            // initialize methods
            self.addNewFieldHandler( $('.' + self.ADD_NEW_FIELD_BUTTON_CLASS ) );
            self.initEditableFieldName( $('.' + self.GRADING_FIELD_EDITABLE_CLASS ) );
            self.initDeleteHandler( $('.' + self.DELETE_FIELD_CLASS ) );

            self.setTypeID();
        }
    };

    $(document).ready(function(){
        sp_admin.sp_catGrading.init();
    });

})(jQuery);