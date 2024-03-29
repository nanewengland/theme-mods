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
    $formats = json_decode(file_get_contents("https://www.nerna.org/main_server/client_interface/json/?switcher=GetFormats&lang_enum=en"),true);
    $service_bodies =  json_decode(file_get_contents("https://www.nerna.org/main_server/client_interface/json/?switcher=GetServiceBodies&services=1&recursive=1"),true);
    $meetings = $meetings_data['meetings'];
    asort($formats);
    foreach($formats as $subKey => $subArray){
        if($subArray['key_string'] == 'HY' ){
            unset($formats[$subKey]);
        }
        if($subArray['key_string'] == 'TC' ){
            unset($formats[$subKey]);
        }
        if($subArray['key_string'] == 'VM' ){
            unset($formats[$subKey]);
        }
    }
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
            foreach($service_bodies as $subKey => $subArray){
                if($subArray['id'] == '1' ){
                    unset($service_bodies[$subKey]);
                }
            }
            $serviceBodies_choices = array();

            foreach($service_bodies as $servicebody) {
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
                foreach($service_bodies as $serviceBody){
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

// Start of mesmerize specific add new/existing functions file below
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 */
