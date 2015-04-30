<?php
if (!class_exists("sp_catGrading")) {

    /**
     * Extends sp_catComponent
     * Content category component. Defines administrative features
     * for the content component. Also used alongside sp_postComponent
     * for front-end handling.
     *
     * @see sp_catComponent
     */
    class sp_catGrading extends sp_catComponent{

        public $grade_types = array( 'alpha' => 'Alpha', 'num' => 'Numeric' );

        function __construct($compID = 0, $catID = 0, $name = '',
                             $description = '', $typeID = 0, $order = 0,
                             $options = null, $default = false, $required = false){

            $compInfo = compact("compID", "catID", "name", "description", "typeID",
                "options",	"order", "default", "required");

            $this->initComponent($compInfo);
        }

        /**
         * @see parent::installComponent()
         */
        function install(){
            self::installComponent("Grading", "Classroom style grading of SmartPost Posts.", __FILE__);
        }

        /**
         * @see parent::uninstall()
         */
        function uninstall(){}

        /**
         * Adds CSS / JS to stylize and handle any UI actions
         */
        static function init(){
            // AJAX class
            require_once( dirname( __FILE__ ) . '/ajax/sp_catGradingAJAX.php');
            sp_catGradingAJAX::init();

            self::enqueueCSS();
            self::enqueueJS();
        }

        /**
         * Add content component JS
         * @important wp_register_script() should contain a dependency for sp_catComponentJS otherwise it will break the front-end
         */
        static function enqueueJS(){
            wp_register_script( 'sp_catGradingJS', plugins_url('/js/sp_catGrading.js', __FILE__), array( 'jquery', 'sp_globals', 'sp_catComponentJS' ));
            wp_enqueue_script( 'sp_catGradingJS' );
        }

        /**
         * Add content component CSS
         */
        static function enqueueCSS(){
            wp_register_style( 'sp_catGradingCSS', plugins_url('/css/sp_catGrading.css', __FILE__), array( 'sp_admin_css' ) );
            wp_enqueue_style( 'sp_catGradingCSS' );
        }

        /**
         * @see parent::componentOptions()
         */
        function componentOptions(){
            $options = $this->options;
            ?>
            <p><?php echo __( 'Component Description:', 'wpspl' ) ?></p>
            <p>
            <?php
                $component_desc = !empty( $options->comp_desc ) ? $options->comp_desc : '';
                echo sp_core::sp_editor(
                    $component_desc,
                    'sp-grading-desc-' . $this->ID,
                    false,
                    'Click to add a description',
                    array(
                        'data-action' => 'sp_grading_save_desc',
                        'data-compid' => $this->ID
                    )
                );
            ?>
            </p>
            <p>Add a new grading field:
                <input type="text" class="sp-new-grading-field" id="sp-new-grading-field-<?php echo $this->ID ?>" /> | Grade type:
                <?php self::render_grade_types_dropdown() ?>
                <button type="button" id="submit-new-grading-field-<?php echo $this->ID ?>" data-compid="<?php echo $this->ID ?>" class="submit-new-grading-field button button-secondary">Submit</button>
                <span class="sp-grading-submit-loader" id="sp-grading-submit-loader-<?php echo $this->ID ?>"><img src="<?php echo SP_IMAGE_PATH . '/loading.gif' ?>" /> Adding new field...</span>
            </p>
            <!-- contains the different fields -->
            <?php if( !empty( $options->fields) ){ echo '<p>Existing fields: </p>'; } ?>
            <table id="sp-grading-field-container-<?php echo $this->ID ?>" class="sp-grading-field-container">
                <tr>
                    <th scope="col" class="col-field-name">Field Name</th>
                    <th scope="col" class="col-field-type">Field Type</th>
                    <th scope="col" class="col-field-del">Delete</th>
                </tr>
                <?php
                if( is_array( $options->fields ) ){
                    foreach( $options->fields as $field_key => $field ){
                        self::render_field( $field, $field_key );
                    }
                }
                ?>
            </table>
        <?php
        }

        /**
         * Renders grade type dropdowns
         * @param $selected
         */
        function render_grade_types_dropdown( $selected = '' ){
            ?>
            <select class="grading-type" id="grading-type-<?php echo $this->ID ?>">
            <?php
            if( is_array( $this->grade_types ) ){
                foreach( $this->grade_types as $grade_key => $grade_label ){
                    echo '<option id="' . $grade_key . '">' . $grade_label . '</option>';
                }
            }
            ?>
            </select>
            <?php
        }

        /**
         * Given a field_obj variable, renders a single field row
         * @param $field_obj
         * @param $field_key
         */
        function render_field( $field_obj, $field_key ){
            ?>
            <tr id="sp-field-row-<?php echo $this->ID?>-<?php echo $field_key ?>">
                <td>
                    <span id="grading-field-<?php echo $field_key ?>" class="grading-field-editable" data-fieldkey="<?php echo $field_key ?>" data-compid="<?php echo $this->ID ?>"><?php echo stripslashes( $field_obj->field_name ) ?></span>
                </td>
                <td>
                    <?php self::render_grade_types_dropdown( $field_obj->field_type ) ?>
                </td>
                <td>
                    <span class="sp-grading-delete" id="sp-grading-delete-<?php echo $this->ID ?>-<?php echo $field_key ?>" data-compid="<?php echo $this->ID ?>" data-fieldkey="<?php echo $field_key ?>">Delete</span>
                </td>
            </tr>
            <?php
        }

        /**
         * @see parent::getOptions()
         */
        function getOptions(){
            return $this->options;
        }

        /**
         * @see parent::setOptions()
         */
        function setOptions($data = null){
            $options = maybe_serialize( $data );
            return sp_core::updateVar('sp_catComponents', $this->ID, 'options', $options, '%s');
        }

        /**
         * Renders the global options for this component, otherwise returns false.
         * @return bool|string
         */
        public static function globalOptions(){
            return false;
        }
    }
}
?>