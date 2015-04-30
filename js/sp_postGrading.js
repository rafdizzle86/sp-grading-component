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
        init: function(){

        }
    };

    $(document).ready(function(){
        smartpost.sp_postGrading.init();
    });

})(jQuery);