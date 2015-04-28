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
                var fieldNameElem = $( '#' + self.fieldInputPrefix + compid );
                var fieldTypeElem = $( '#' + self.gradingTypePrefix + compid );
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
                    $( '#' + self.gradingFieldContainer + compID ).append( response );
                },
                error    : function(jqXHR, statusText, errorThrown){
                    if(smartpost.sp_postComponent)
                        smartpost.sp_postComponent.showError('Status: ' + statusText + ', Error Thrown:' + errorThrown);
                }
            });
        },

        /**
         * Dynamically initializes field
         */
        initFieldHandler: function(){

        },

        /**
         * Initialize JS for the grading cmoponent
         */
        init: function() {
            var self = this;

            // initializes vars
            self.fieldInputPrefix  = 'sp-new-grading-field-';
            self.gradingTypePrefix = 'grading-type-';
            self.gradingFieldContainer = 'sp-grading-field-container-';

            self.addNewFieldButton = $( '.submit-new-grading-field' );

            // initialize methods
            self.addNewFieldHandler( self.addNewFieldButton );
        }
    };


    $(document).ready(function(){
        smartpost.sp_postGrading.init();
    });

})(jQuery);