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
         */
        static function enqueueJS(){
            wp_register_script( 'sp_catGradingJS', plugins_url('/js/sp_catGrading.js', __FILE__));
            wp_enqueue_script( 'sp_catGradingJS', array('jquery', 'sp_admin_globals', 'sp_admin_js', 'sp_catComponentJS') );
        }

        /**
         * Add content component CSS
         */
        static function enqueueCSS(){
            //wp_register_style( 'sp_catGradingCSS', plugins_url('/css/sp_catGrading.css', __FILE__));
            //wp_enqueue_style( 'sp_catGradingCSS' );
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
                <select class="grading-type" id="grading-type-<?php echo $this->ID ?>">
                    <option id="alpha" value="alpha">Alpha</option>
                    <option id="numeric" value="num">Numeric</option>
                </select>
                <button type="button" id="submit-new-grading-field-<?php echo $this->ID ?>" data-compid="<?php echo $this->ID ?>" class="submit-new-grading-field button button-secondary">Submit</button>
            </p>
            <!-- contains the different fields -->
            <div id="sp-grading-field-container-<?php echo $this->ID ?>" class="sp-grading-field-container"></div>
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
            $options = maybe_serialize($data);
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