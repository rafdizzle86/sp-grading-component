/**
 * Handles admin-side JS for the SmartPost grading component
 */
(function($) {
    smartpost.sp_postGrading = {

        /**
         * Required for all post component JS objects.
         * Used in sp_globals.SP_TYPES to determine which
         * methods to call for different post component types
         */
        setTypeID: function(){
            if(sp_globals){
                var types = sp_globals.SP_TYPES;

                //!Important - the raw name of the type
                if(types['Content']){
                    this.typeID = types['Content'];
                    sp_globals.SP_TYPES[this.typeID] = this;
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
                compid = $(this).data( 'compid' );
                var fieldNameElem = $( '#' + self.FIELD_INPUT_PREFIX + compid );
                var fieldTypeElem = $( '#' + self.GRADING_TYPE_PREFIX + compid );
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
                    compid: compid,
                    fieldname: fieldNameElem.val(),
                    fieldtype: fieldTypeElem.val()
                },
                dataType : 'html',
                success: function( response ){
                    $( '#' + self.GRADING_FIELD_CONTAINER + compID ).append( response );
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
            })
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
         * Initialize JS for the grading cmoponent
         */
        init: function() {
            var self = this;

            // initialize element ids
            self.FIELD_INPUT_PREFIX      = 'sp-new-grading-field-';
            self.GRADING_TYPE_PREFIX     = 'grading-type-';
            self.GRADING_FIELD_CONTAINER = 'sp-grading-field-container-';
            self.ADD_NEW_FIELD_BUTTON    = $( '.submit-new-grading-field' );
            self.GRADING_FIELD_EDITABLE_CLASS  = 'grading-field-editable';

            // initialize methods
            self.addNewFieldHandler( self.ADD_NEW_FIELD_BUTTON );
            self.initEditableFieldName( $('.' + self.GRADING_FIELD_EDITABLE_CLASS ) );
        }
    };


    $(document).ready(function(){
        smartpost.sp_postGrading.init();
    });

})(jQuery);