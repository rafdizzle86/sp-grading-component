/*
 * JS sp_posGrading Component class
 * Used alongside sp_postGrading for AJAX calls
 * Used in front-end posts
 *
 * @version 1.0
 * @author Rafi Yagudin <rafi.yagudin@tufts.edu>
 * @project SmartPost 
 */
(function($){
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
                if(types['Grading']){
                    this.typeID = types['Grading'];
                    sp_globals.SP_TYPES[this.typeID] = this;
                }
            }else{
                return 0;
            }
        },
        initEditableGradeFields: function( fieldElems){
            var self = this;
            fieldElems.editable(function(value, settings){
                    var fieldKey = $(this).data('fieldkey');
                    var compID   = $(this).data('compid');
                    self.saveGrade( fieldKey, compID, value );
                    return value;
                },
                {
                    placeholder: 'Click to add a grade',
                    onblur     : 'submit',
                    cssclass   : 'sp_compTitleEditable',
                    maxlength  : 35
                }
            );
        },
        /**
         * Saves a grade for a specific field
         * @param fieldKey
         * @param compID
         * @param grade
         */
        saveGrade: function( fieldKey, compID, grade ){
            $.ajax({
                url		 : SP_AJAX_URL,
                type     : 'POST',
                data	 : {
                    nonce  : SP_NONCE,
                    action : 'sp_save_grade_ajax',
                    grade  : grade,
                    fieldKey  : fieldKey,
                    compid    : compID
                },
                dataType : 'json',
                success  : function(response, statusText, jqXHR){
                    console.log( response );
                },
                error    : function(jqXHR, statusText, errorThrown){
                    smartpost.sp_postComponent.showError(errorThrown, null);
                }
            });
        },
        /**
         * Initializes a newly created grading component
         * @param component
         * @param postID
         * @param autoFocus
         */
        initComponent: function( component, postID, autoFocus){
            var self = this;
            var editableFields = component.find( '.' + self.GRADE_FIELD_EDITABLE_CLASS );
            self.initEditableGradeFields( editableFields );
        },
        init: function(){
            var self = this;

            // Initialize constants
            self.GRADE_FIELD_EDITABLE_CLASS = 'grading-field-grade-editable';
            self.GRADE_FIELD_PREFIX_ID = 'grading-field-grade-'; //grading-field-grade-<COMP_ID>-<FIELD_KEY>

            // Init/Bind handlers
            self.initEditableGradeFields( $('.' + self.GRADE_FIELD_EDITABLE_CLASS ) );

            self.setTypeID();
        }
    };

    $(document).ready(function(){
        smartpost.sp_postGrading.init();
    });

})(jQuery);