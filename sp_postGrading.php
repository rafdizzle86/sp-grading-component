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

            //Set the default grading options when creating a new component
            $default_grading_options = sp_catComponent::getOptionsFromID( $catCompID );
            $this->options = $default_grading_options;

            $this->initComponent( $compInfo );

            // Get the grading options again, but this time for an existing component..
            $default_grading_options = sp_catComponent::getOptionsFromID( $this->catCompID );
            $this->options = $default_grading_options;
        }

        /**
         * We are overriding the render() function from the parent for security reasons.
         * @param bool $force_edit_mode - Forces the component to be rendered in "Edit Mode"
         * @return string - XHTML of the component
         */
        function render( $force_edit_mode = false ){

            // Establish whether we are in edit mode or not
            $edit_mode = $force_edit_mode ? true : (bool) $_GET['edit_mode'];

            require_once(ABSPATH . 'wp-admin/includes/post.php');
            $is_locked = (bool) wp_check_post_lock( $this->postID );

            $html = '';

            // Return preview mode if we're listing posts
            if( !is_single() ){
                if( !$this->isEmpty() ){
                    $html = $this->renderPreview() . ' ';
                }
                return $html;
            }

            // Return edit mode component if we're an admin or an owner
            if( current_user_can('edit_post', $this->postID) && $edit_mode && !$is_locked ){
                $html = '<div id="comp-' . $this->ID . '" data-compid="' . $this->ID . '" data-required="' . $this->isRequired() . '" data-catcompid="' . $this->catCompID . '" data-typeid="' . $this->typeID . '" class="sp-component-edit-mode' . ( ($this->isRequired() && $this->lastOne() && $this->isEmpty() ) ?  ' requiredComponent' : '') . '">';
                if( current_user_can( 'manage_options' ) ) {
                    $html .= $this->render_comp_title( true );
                    $html .= '<span id="del" data-compid="' . $this->ID . '" class="sp_delete sp_xButton" title="Delete Component"></span>';
                    $html .= '<div class="componentHandle tooltip" title="Drag up or down"><div class="theHandle"></div></div>';
                }else{
                    $html .= $this->render_comp_title( false );
                }

                $html .= $this->renderEditMode();
                $html .= '<div class="clear"></div>';
                $html .= '</div><!-- end #comp-' . $this->ID .' -->';

            }else{ // Otherwise return "view mode"
                if( !$this->isEmpty() && current_user_can( 'edit_post', $this->postID ) ){
                    $html = '<div id="comp-' .  $this->ID . '" class="sp_component">';
                    $html .= $this->render_comp_title();
                    $html .= '<div class="clear"></div>';
                    $html .= $this->renderViewMode();
                    $html .= '<div class="clear"></div>';
                    $html .= '</div><!-- end #comp-' . $this->ID .' -->';
                }
            }
            return $html;
        }

        /**
         * @see parent::renderEditMode()
         * @param string $value
         * @return string
         */
        function renderEditMode($value = ""){

            $settings = $this->value;

            // Setup the grading description
            if( $settings->dirty_desc ){
                $component_desc = $settings->grading_desc;
            }else{
                $component_desc = $this->options->comp_desc;
            }

            if( current_user_can( 'manage_options' ) ) {
                $html = sp_core::sp_editor(
                    $component_desc,
                    $this->ID,
                    false,
                    'Add a description here ...',
                    array('data-action' => 'sp_save_grading_desc_via_post', 'data-compid' => $this->ID, 'data-postid' => $this->postID)
                );
            }else{
                $html = $component_desc;
            }

            // Setup the grading fields
            ob_start();
            $this->render_grading_fields();
            $html .= ob_get_clean();

            // Setup the comment section - @uses $settings->grading_comment
            if( current_user_can( 'manage_options' ) ) {
                $html .= sp_core::sp_editor(
                    $settings->grading_comment,
                    $this->ID . '-comment',
                    false,
                    'Add a comment here ...',
                    array('data-action' => 'sp_save_grading_comment', 'data-compid' => $this->ID, 'data-postid' => $this->postID)
                );
            }else{
                $html .= $settings->grading_comment;
            }

            return $html;
        }

        /**
         * @see parent::renderViewMode()
         */
        function renderViewMode(){
            $html = '<div id="sp_grading" style="margin: 20px">';

            $component_desc = !empty( $this->options->comp_desc ) ? $this->options->comp_desc : '';
            $html .= do_shortcode( $component_desc );

            // Add the grading table
            ob_start();
            $this->render_grading_fields();
            $html .= ob_get_clean();

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
                    <th scope="col" class="col-field-grade">Grade</th>
                </tr>
                <?php
                if( is_array( $options->fields ) ){
                    foreach( $options->fields as $field_key => $field ){
                        self::render_field( $field, $field_key, current_user_can( 'manage_options' ) );
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
            $grade = isset( $this->value->grading_fields[$field_key]->grade ) ? $this->value->grading_fields[$field_key]->grade : '';
            ?>
            <tr id="sp-field-row-<?php echo $this->ID?>-<?php echo $field_key ?>">
                <td>
                    <?php if( $editable ): ?>
                    <span id="grading-field-<?php echo $field_key ?>" class="grading-field-editable" data-fieldkey="<?php echo $field_key ?>" data-compid="<?php echo $this->ID ?>">
                        <?php echo stripslashes( $field_obj->field_name ) ?>
                    </span>
                    <?php else: ?>
                        <?php echo stripslashes( $field_obj->field_name ) ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if( $editable ): ?>
                    <span class="grading-field-grade-editable" id="grading-field-grade-<?php echo $this->ID?>-<?php echo $field_key ?>" data-compid="<?php echo $this->ID ?>" data-fieldkey="<?php echo $field_key ?>">
                        <?php echo $grade ?>
                    <?php else: ?>
                        <?php echo $grade ?>
                    <?php endif;?>
                    </span>
                </td>
            </tr>
            <?php
        }

        /**
         * @see parent::render()
         * @return string
         */
        function renderPreview(){
            return '';
        }

        /**
         * Initializes the class with AJAX functions, JS and CSS.
         * @return mixed|void
         */
        static function init(){
            require_once( dirname( __FILE__ ) . '/ajax/sp_postGradingAJAX.php');
            sp_postGradingAJAX::init();
            self::enqueueJS();
            self::enqueueCSS();
        }

        /**
         * Enqueues the JS to the page.
         */
        static function enqueueJS(){
            $suffix = SCRIPT_DEBUG ? '' : '.min';
            wp_register_script( 'sp_postGradingJS', plugins_url('/js/sp_postGrading' . $suffix . '.js', __FILE__), array( 'jquery', 'sp_globals', 'sp_postComponentJS', 'sp_postJS' ) );
            wp_enqueue_script( 'sp_postGradingJS' );
        }

        static function enqueueCSS(){
            $suffix = SCRIPT_DEBUG ? '' : '.min';
            wp_register_style( 'sp_postGradingCSS', plugins_url('/css/sp_postGrading' . $suffix . '.css', __FILE__)  );
            wp_enqueue_style( 'sp_postGradingCSS' );
        }

        /**
         * Returns the grading fields for this component
         */
        function get_grading_fields(){
           return $this->options->fields;
        }

        /**
         * @see parent::update();
         * @param null $comp_instance_settings // Save the settings fo this instance
         * @return bool|int
         */
        function update($comp_instance_settings = null){
            $this->value = $comp_instance_settings;
            return sp_core::updateVar('sp_postComponents', $this->ID, 'value', maybe_serialize( $this->value ), '%s');
        }

        /**
         * @see parent::isEmpty();
         */
        function isEmpty(){
            return false; // Not sure what "empty" means for this component, so return false always
        }
    }
}
