<?php
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 */


add_filter("gform_pre_render_10", "monitor_dropdown");
function monitor_dropdown($form){

    wp_register_script('my_amazing_script', get_template_directory_uri() . '/gfm.js', array('jquery'),'1.2', true);
    wp_enqueue_script('my_amazing_script');

    return $form;
}


add_filter( 'gform_pre_render_10', 'populate_posts' );
add_filter( 'gform_pre_validation_10', 'populate_posts' );
add_filter( 'gform_pre_submission_filter_10', 'populate_posts' );
add_filter( 'gform_admin_pre_render_10', 'populate_posts' );

function populate_posts( $form ) {
    $meetings_data =  json_decode(file_get_contents("https://www.nerna.org/main_server/client_interface/json/?switcher=GetSearchResults&services=1&recursive=1&advanced_published=0&sort_keys=meeting_name,service_body_bigint,weekday_tinyint,start_time&get_used_formats"),true);
    $meetings = $meetings_data['meetings'];
    $formats = $meetings_data['formats'];
    asort($formats);

    $counties_array = array();
    foreach($meetings as $county){
        $counties_array[] = $county['location_sub_province'];
    }
    $counties_array = array_unique($counties_array, SORT_REGULAR);
    asort($counties_array);
    $finalCountyArray = array();
    foreach($counties_array as $county){
        $finalCountyArray[] = array( 'text' => $county, 'value' => $county );
    }

    foreach ( $form['fields'] as &$field ) {
        if ( $field->type === 'select' && strpos( $field->cssClass, 'formats_dd' ) === true ) {
            $format_choices = array();
            foreach($formats as $format) {
                $format_choices[] = array( 'text' => ($format['key_string']. " - " .$format['name_string']), 'value' => $format['key_string'] );
            }
            $field->placeholder = 'Select a Format';
            $field->choices = $format_choices;
        }
        if ( $field->type === 'select' && strpos( $field->cssClass, 'servicebody' ) === true ) {
            $serviceBodies_results =  json_decode(file_get_contents("https://www.nerna.org/main_server/client_interface/json/?switcher=GetServiceBodies&services=1&recursive=1"),true);
            foreach($serviceBodies_results as $subKey => $subArray){
                if($subArray['id'] == '1' ){
                    unset($serviceBodies_results[$subKey]);
                }
            }
            $serviceBodies_choices = array();

            foreach($serviceBodies_results as $servicebody) {
                $serviceBodies_choices[] = array( 'text' => $servicebody['name'], 'value' => $servicebody['name'] );
            }
            asort($serviceBodies_choices);
            // update 'Select a Post' to whatever you'd like the instructive option to be
            $field->placeholder = 'Select a Service Body';
            $field->choices = $serviceBodies_choices;
        }
        if ( $field->type === 'select' && strpos( $field->cssClass, 'county_dd' ) === true ) {
            $field->placeholder = 'Select a County';
            $field->choices = $finalCountyArray;
        }
        if ( $field->type === 'select' && strpos( $field->cssClass, 'meeting' ) === true ) {
            $days_of_the_week = [1 => "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
            $choices = array();
            foreach($meetings as $meeting) {
                foreach($serviceBodies_results as $serviceBody){
                    $area_id = $serviceBody['id'];
                    if ( $area_id === $meeting['service_body_bigint'] ) {
                        $area_name = $serviceBody['name'];
                        $area_name = str_replace(' Area', '', $area_name);
                    }
                }
                $meeting['meeting_name'] = str_replace(' Area', '', $meeting['meeting_name']);
                $meeting['start_time'] = date("g:iA",strtotime($meeting['start_time']));
                $extra_meeting_display = substr($meeting['meeting_name'], 0, 30) . " [" .$days_of_the_week[$meeting['weekday_tinyint']]. "] [" .$meeting['start_time']. "] [". $area_name. "]";
                $meetingf = htmlspecialchars($extra_meeting_display);
                $choices[] = array( 'text' => $meetingf, 'value' => $meeting['id_bigint'] );
            }
            $field->placeholder = 'Select a Meeting';
            $field->choices = $choices;
        }
    }
    return $form;
}

// add_action( 'gform_after_submission_10', 'after_submission', 10, 2 );
add_action( 'gform_after_submission_10', 'after_submission', 10, 2 );

function after_submission( $entry, $form ) {
//var_dump($entry); PJ Testing
    foreach ( $form['fields'] as $field ) {
        $inputs = $field->get_entry_inputs();
        if ( is_array( $inputs ) ) {
            foreach ( $inputs as $input ) {
                $value = rgar( $entry, (string) $input['id'] );
                error_log($value);
                // do something with the value
            }
        } else {
            $value = rgar( $entry, (string) $field->id );
            error_log($value);
            // do something with the value
        }
    }
}

// Start of mesmerize specific
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 */

if ( ! defined('MESMERIZE_THEME_REQUIRED_PHP_VERSION')) {
    define('MESMERIZE_THEME_REQUIRED_PHP_VERSION', '5.3.0');
}

add_action('after_switch_theme', 'mesmerize_check_php_version');

function mesmerize_check_php_version()
{
    // Compare versions.
    if (version_compare(phpversion(), MESMERIZE_THEME_REQUIRED_PHP_VERSION, '<')) :
        // Theme not activated info message.
        add_action('admin_notices', 'mesmerize_php_version_notice');


        // Switch back to previous theme.
        switch_theme(get_option('theme_switched'));

        return false;
    endif;
}

function mesmerize_php_version_notice()
{
    ?>
    <div class="notice notice-alt notice-error notice-large">
        <h4><?php _e('Mesmerize theme activation failed!', 'mesmerize'); ?></h4>
        <p>
            <?php _e('You need to update your PHP version to use the <strong>Mesmerize</strong>.', 'mesmerize'); ?> <br/>
            <?php _e('Current php version is:', 'mesmerize') ?> <strong>
                <?php echo phpversion(); ?></strong>, <?php _e('and the minimum required version is ', 'mesmerize') ?>
            <strong><?php echo MESMERIZE_THEME_REQUIRED_PHP_VERSION; ?></strong>
        </p>
    </div>
    <?php
}

if (version_compare(phpversion(), MESMERIZE_THEME_REQUIRED_PHP_VERSION, '>=')) {
    require_once get_template_directory() . "/inc/functions.php";

    //SKIP FREE START

    // look for an embedded child theme
    if(! defined('MESMERIZE_CHILD_DEV') || ! MESMERIZE_CHILD_DEV){
        add_filter('mesmerize_is_child_embedded', '__return_true');
        mesmerize_require("child/functions.php");
    }

    if ( ! defined('MESMERIZE_ONLY_FREE') || ! MESMERIZE_ONLY_FREE) {
        // NEXT FREE VERSION
        require_once get_template_directory() . "/inc/functions-next.php";

        // PRO HERE
        require_once get_template_directory() . "/pro/functions.php";
    }

    //SKIP FREE END

    if ( ! mesmerize_can_show_cached_value("mesmerize_cached_kirki_style_mesmerize")) {

        if ( ! mesmerize_skip_customize_register()) {
            do_action("mesmerize_customize_register_options");
        }
    }

} else {
    add_action('admin_notices', 'mesmerize_php_version_notice');
}
