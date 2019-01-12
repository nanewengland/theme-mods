<?php

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 */


add_action( 'wp_enqueue_scripts', 'wpb_adding_scripts', 999 );

add_filter("gform_pre_render_10", "monitor_dropdown");
function monitor_dropdown($form){

    wp_register_script('my_amazing_script', get_template_directory_uri() . '/gfm.js', array('jquery'),'1.1', true);
    wp_enqueue_script('my_amazing_script');

    return $form;
}


add_filter( 'gform_pre_render_10', 'populate_posts' );
add_filter( 'gform_pre_validation_10', 'populate_posts' );
add_filter( 'gform_pre_submission_filter_10', 'populate_posts' );
add_filter( 'gform_admin_pre_render_10', 'populate_posts' );
function populate_posts( $form ) {


    foreach ( $form['fields'] as &$fieldf ) {
        if ( $fieldf->type != 'select' || strpos( $fieldf->cssClass, 'formats_dd' ) === false ) {
            continue;
        }


        $formatsURL =  file_get_contents("https://www.nerna.org/main_server/client_interface/json/?switcher=GetFormats");
        $format_results = json_decode($formatsURL,true);
        asort($format_results);
        $format_choices = array();

        foreach($format_results as $formats) {
            $format_choices[] = array( 'text' => ($formats['key_string']. " - " .$formats['name_string']), 'value' => $formats['key_string'] );
        }

        // update 'Select a Post' to whatever you'd like the instructive option to be
        $fieldf->placeholder = 'Select a Format';
        $fieldf->choices = $format_choices;

    }


    foreach ( $form['fields'] as &$fields ) {
        if ( $fields->type != 'select' || strpos( $fields->cssClass, 'servicebody' ) === false ) {
            continue;
        }

        $serviceBodiesURL =  file_get_contents("https://www.nerna.org/main_server/client_interface/json/?switcher=GetServiceBodies");
        $serviceBodies_results = json_decode($serviceBodiesURL,true);
        foreach($serviceBodies_results as $subKey => $subArray){
            if($subArray['name'] == 'New England Region'){
                unset($serviceBodies_results[$subKey]);
            }
        }
        asort($serviceBodies_results);
        $serviceBodies_choices = array();

        foreach($serviceBodies_results as $servicebody) {
            $serviceBodies_choices[] = array( 'text' => $servicebody['name'], 'value' => $servicebody['name'] );
        }

        // update 'Select a Post' to whatever you'd like the instructive option to be
        $fields->placeholder = 'Select a Service Body';
        $fields->choices = $serviceBodies_choices;

    }

    foreach ( $form['fields'] as &$fields ) {
        if ( $fields->type != 'select' || strpos( $fields->cssClass, 'county_dd' ) === false ) {
            continue;
        }

        $countiesURL =  file_get_contents("https://www.nerna.org/main_server/client_interface/json/?switcher=GetFieldValues&meeting_key=location_sub_province");
        $counties_results = json_decode($countiesURL,true);
        $finalCountyArray = array();
        foreach($counties_results as $county){
            $finalCountyArray[] = array( 'text' => $county['location_sub_province'], 'value' => $county['location_sub_province'] );

        }
        asort($finalCountyArray);

        // update 'Select a Post' to whatever you'd like the instructive option to be
        $fields->placeholder = 'Select a County';
        $fields->choices = $finalCountyArray;

    }

    foreach ( $form['fields'] as &$field ) {
        if ( $field->type != 'select' || strpos( $field->cssClass, 'meeting' ) === false ) {
            continue;
        }


        $meetingsURL =  file_get_contents("https://www.nerna.org/main_server/client_interface/json/?switcher=GetSearchResults&sort_keys=meeting_name,service_body_bigint,weekday_tinyint,start_time");
        $meetings = json_decode($meetingsURL,true);
        $days_of_the_week = [1 => "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
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

    return $form;
}

// add_action( 'gform_after_submission_10', 'after_submission', 10, 2 );
add_action( 'gform_after_submission_10', 'after_submission', 10, 2 );

function after_submission( $entry, $form ) {
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



// end of gravity changes
