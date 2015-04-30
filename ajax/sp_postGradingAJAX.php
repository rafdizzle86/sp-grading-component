<?php
/**
 * Created by PhpStorm.
 * User: ryagudin
 * Date: 4/30/15
 * Time: 10:19 AM
 */


if (!class_exists("sp_postGradingAJAX")) {
    class sp_postGradingAJAX
    {
        static function init()
        {
            //add_action('wp_ajax_sp_grading_save_desc', array('sp_postGradingAJAX', 'sp_grading_save_desc'));
        }
    }
}