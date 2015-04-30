<?php
if (!class_exists("sp_postGrading")) {
    /**
     * Extends sp_postComponent
     *
     * @see sp_postComponent
     */
    class sp_postGrading extends sp_postComponent{

        function __construct($compID = 0, $catCompID = 0, $compOrder = 0,
                             $name = '', $value = '', $postID = 0){
            $compInfo = compact("compID", "catCompID", "compOrder", "name", "value", "postID");

            //Set the default grading options
            $gradingOptions = sp_catComponent::getOptionsFromID($catCompID);
            $this->options = $gradingOptions;

            $this->initComponent($compInfo);
        }

        /**
         * @see parent::renderEditMode()
         */
        function renderEditMode($value = ""){

            $component_desc = !empty( $this->options->comp_desc ) ? $this->options->comp_desc : '';
            if( current_user_can( 'edit_dashboard' ) ) {

                // Create an editor area for a video description
                $html = sp_core::sp_editor(
                    $component_desc,
                    $this->ID,
                    false,
                    'Add a description here ...',
                    array('data-action' => 'save_grading_desc_via_post', 'data-compid' => $this->ID, 'data-postid' => $this->postID)
                );

                ob_start();
                $this->render_grading_fields();
                $html .= ob_get_clean();

            }else{
                return $component_desc;
            }

            return $html;
        }

        /**
         * @see parent::renderViewMode()
         */
        function renderViewMode(){
            $html = '<div id="sp_grading" style="margin: 20px">';
            $html .= do_shortcode( $this->value );

            $html .= '</div>';
            return $html;
        }

        /**
         * Renders the grading fields setup in the smartpost dashboard
         */
        function render_grading_fields(){
            $options = $this->options;
            ?>
            <!-- contains the different fields -->
            <table id="sp-grading-field-container-<?php echo $this->ID ?>" class="sp-grading-field-container">
                <tr>
                    <th scope="col" class="col-field-name">Breakdown</th>
                    <th scope="col" class="col-field-type">Grade</th>
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
         * Renders a grading field
         * @param $field_obj
         * @param $field_key
         * @param bool $editable
         */
        function render_field( $field_obj, $field_key, $editable = false ){
            ?>
            <tr id="sp-field-row-<?php echo $this->ID?>-<?php echo $field_key ?>">
                <td>
                    <span id="grading-field-<?php echo $field_key ?>" class="grading-field-editable" data-fieldkey="<?php echo $field_key ?>" data-compid="<?php echo $this->ID ?>"><?php echo stripslashes( $field_obj->field_name ) ?></span>
                </td>
                <td>
                    <?php if( $editable ): ?>
                    <span class="grading-field-grade-editable" id="grading-field-grade-<?php echo $this->ID?>-<?php echo $field_key ?>">
                        <?php echo $field_obj->grade ?>
                    </span>
                    <?php else: ?>
                        <?php echo $field_obj->grade ?>
                    <?php endif;?>
                </td>
            </tr>
            <?php
        }

        /**
         * @see parent::render()
         * @return string
         */
        function renderPreview(){
            return self::renderViewMode();
        }

        /**
         * Initializes the class with AJAX functions, JS and CSS.
         * @return mixed|void
         */
        static function init(){
            require_once( dirname( __FILE__ ) . '/ajax/sp_postGradingAJAX.php');
            sp_postGradingAJAX::init();
            self::enqueueJS();
        }

        /**
         * Enqueues the JS to the page.
         */
        static function enqueueJS(){
            wp_register_script( 'sp_postGrading', plugins_url('/js/sp_postGrading.js', __FILE__));
            wp_enqueue_script( 'sp_postGrading', null, array( 'jquery', 'sp_globals', 'sp_postComponentJS', 'sp_postJS' ) );
        }

        /**
         * @see parent::update();
         */
        function update($grading = ''){
            $this->value = (string) stripslashes_deep( $grading );
            return sp_core::updateVar('sp_postComponents', $this->ID, 'value', $this->value, '%s');
        }

        /**
         * @see parent::isEmpty();
         */
        function isEmpty(){
            $strippedContent = trim(strip_tags($this->value));
            return empty($strippedContent);
        }
    }
}
