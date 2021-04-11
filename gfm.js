var baseURL;

baseURL = "https://www.nerna.org/main_server/client_interface/jsonp/?switcher=GetSearchResults";

var getDayOfWeek = function(dayint) {
    return ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"][dayint];
};

var militaryToStandard = function(value) {
    if (value !== null && value !== undefined){ //If value is passed in
        if(value.indexOf('AM') > -1 || value.indexOf('PM') > -1){ //If time is already in standard time then don't format.
            return value;
        }
        else {
            if (value.length == 8) { //If value is the expected length for military time then process to standard time.
                valueconv = value.split(':'); // convert to array
                // fetch
                var hours = Number(valueconv[0]);

                // calculate
                var timeValue;
                if (hours > 0 && hours <= 12) { // If hour is less than or equal to 12 then convert to standard 12 hour format
                    timeValue= "" + hours;
                } else if (hours > 12) { //If hour is greater than 12 then convert to standard 12 hour format
                    timeValue= "" + (hours - 12);
                } else if (hours == 0) { //If hour is 0 then set to 12 for standard time 12 AM
                    timeValue= "12";
                }

                timeValue += ":" + valueconv[1];  // get minutes
                timeValue += (hours >= 12) ? ":pm" : ":am";  // get AM/PM
                // show
                return timeValue;
            }
            else { //If value is not the expected length than just return the value as is
                return valueconv;
            }
        }
    }
};


var getMeetingsByMeetingId = function(meetingId, callback) {
    getJSON(baseURL + "&meeting_ids[]=" + meetingId + "&callback=?", callback);
};

var getServiceBodies = function(callback) {
    getJSON(baseURL + "GetServiceBodies" + "&callback=?", callback);

};



var getJSON = function(url, callback) {
    var random = Math.floor(Math.random() * 999999);
    var callbackFunctionName = "cb_" + random
    url = url.replace("callback=?", "callback=" + callbackFunctionName);

    window[callbackFunctionName] = function(data) {
        callback(data);
    };

    var scriptItem = document.createElement('script');
    scriptItem.setAttribute('src', url);
    document.body.appendChild(scriptItem);
}


function addTimes (startTime, endTime) {
    var times = [ 0, 0, 0 ]
    var max = times.length

    var a = (startTime || '').split(':')
    var b = (endTime || '').split(':')

    // normalize time values
    for (var i = 0; i < max; i++) {
        a[i] = isNaN(parseInt(a[i])) ? 0 : parseInt(a[i])
        b[i] = isNaN(parseInt(b[i])) ? 0 : parseInt(b[i])
    }

    // store time values
    for (var i = 0; i < max; i++) {
        times[i] = a[i] + b[i]
    }

    var minutes = times[1];
    var hours = times[0];



    if (minutes >= 60) {
        res = (minutes / 60) | 0;
        hours += res;
        minutes = minutes - (60 * res);
        minutes = minutes < 10 ? "0" + minutes : minutes;

    }

    value = hours + ':' + minutes + ':00';
    valueconv = value.split(':'); // convert to array
    // fetch
    var hours = Number(valueconv[0]);

    // calculate
    var timeValue;
    if (hours > 0 && hours <= 12) { // If hour is less than or equal to 12 then convert to standard 12 hour format
        timeValue= "" + hours;
    } else if (hours > 12) { //If hour is greater than 12 then convert to standard 12 hour format
        timeValue= "" + (hours - 12);
    } else if (hours == 0) { //If hour is 0 then set to 12 for standard time 12 AM
        timeValue= "12";
    }
    if (valueconv[1].length < 2) {
        valueconv[1] = valueconv[1] + "0";
    }
    //valueconv[1].toFixed(2);
    timeValue += ":" + valueconv[1];  // get minutes
    timeValue += (hours >= 12) ? ":pm" : ":am";  // get AM/PM
    // show
    return timeValue;

}


var getServiceBodies = function(callback) {
    getJSON("https://www.nerna.org/main_server/client_interface/jsonp/?switcher=GetServiceBodies&callback=?", callback);
};

var getServiceBodyById = function(id) {
    for (item of serviceBodies) {
        if (item.id == id) {
            return item;
        }
    }
}

var serviceBodies = [];


getServiceBodies(function(data) {
    serviceBodies = data;
    //selectDay(getTodayDayOfWeek());
})


jQuery(document).ready(function(){

    jQuery('#input_10_31').bind('change', function()
    {
        //get selected value from drop down;
        var selectedValue = jQuery("#input_10_31").val();
        getMeetingsByMeetingId(selectedValue, function(data){

            startTime = militaryToStandard(data[0].start_time);
            startTimeArray = startTime.split(':');
            startTimeMeridian = startTimeArray[2];
            startTimeHour = startTimeArray[0];
            startTimeMinute = startTimeArray[1];

            endTime = addTimes(data[0].start_time, data[0].duration_time);
            endTimeArray = endTime.split(':');
            endTimeMeridian = endTimeArray[2];
            endTimeHour = endTimeArray[0];
            endTimeMinute = endTimeArray[1];

            dayOfWeek = getDayOfWeek(data[0].weekday_tinyint - 1);

            if ( dayOfWeek == 'Sunday' ) {
                jQuery('#choice_10_4_1').attr('checked', true);
                jQuery('#choice_10_4_2').attr('checked', false);
                jQuery('#choice_10_4_3').attr('checked', false);
                jQuery('#choice_10_4_4').attr('checked', false);
                jQuery('#choice_10_4_5').attr('checked', false);
                jQuery('#choice_10_4_6').attr('checked', false);
                jQuery('#choice_10_4_7').attr('checked', false);
            }
            else if ( dayOfWeek == 'Monday' ) {
                jQuery('#choice_10_4_2').attr('checked', true);
                jQuery('#choice_10_4_1').attr('checked', false);
                jQuery('#choice_10_4_3').attr('checked', false);
                jQuery('#choice_10_4_4').attr('checked', false);
                jQuery('#choice_10_4_5').attr('checked', false);
                jQuery('#choice_10_4_6').attr('checked', false);
                jQuery('#choice_10_4_7').attr('checked', false);
            }
            else if ( dayOfWeek == 'Tuesday' ) {
                jQuery('#choice_10_4_3').attr('checked', true);
                jQuery('#choice_10_4_1').attr('checked', false);
                jQuery('#choice_10_4_2').attr('checked', false);
                jQuery('#choice_10_4_4').attr('checked', false);
                jQuery('#choice_10_4_5').attr('checked', false);
                jQuery('#choice_10_4_6').attr('checked', false);
                jQuery('#choice_10_4_7').attr('checked', false);
            }
            else if ( dayOfWeek == 'Wednesday' ) {
                jQuery('#choice_10_4_4').attr('checked', true);
                jQuery('#choice_10_4_1').attr('checked', false);
                jQuery('#choice_10_4_2').attr('checked', false);
                jQuery('#choice_10_4_3').attr('checked', false);
                jQuery('#choice_10_4_5').attr('checked', false);
                jQuery('#choice_10_4_6').attr('checked', false);
                jQuery('#choice_10_4_7').attr('checked', false);
            }
            else if ( dayOfWeek == 'Thursday' ) {
                jQuery('#choice_10_4_5').attr('checked', true);
                jQuery('#choice_10_4_1').attr('checked', false);
                jQuery('#choice_10_4_2').attr('checked', false);
                jQuery('#choice_10_4_3').attr('checked', false);
                jQuery('#choice_10_4_4').attr('checked', false);
                jQuery('#choice_10_4_6').attr('checked', false);
                jQuery('#choice_10_4_7').attr('checked', false);
            }
            else if ( dayOfWeek == 'Friday' ) {
                jQuery('#choice_10_4_6').attr('checked', true);
                jQuery('#choice_10_4_1').attr('checked', false);
                jQuery('#choice_10_4_2').attr('checked', false);
                jQuery('#choice_10_4_3').attr('checked', false);
                jQuery('#choice_10_4_4').attr('checked', false);
                jQuery('#choice_10_4_5').attr('checked', false);
                jQuery('#choice_10_4_7').attr('checked', false);
            }
            else if ( dayOfWeek == 'Saturday' ) {
                jQuery('#choice_10_4_7').attr('checked', true);
                jQuery('#choice_10_4_1').attr('checked', false);
                jQuery('#choice_10_4_2').attr('checked', false);
                jQuery('#choice_10_4_3').attr('checked', false);
                jQuery('#choice_10_4_4').attr('checked', false);
                jQuery('#choice_10_4_5').attr('checked', false);
                jQuery('#choice_10_4_6').attr('checked', false);
            }

            meetingState = data[0].location_province;
            if ( meetingState == 'MA') {
                meetingState = 'Massachusetts';
                jQuery("#input_10_10_4").val(meetingState);
            }
            else if ( meetingState == 'RI') {
                meetingState = 'Rhode Island';
                jQuery("#input_10_10_4").val(meetingState);
            }

            formatsArray = data[0].format_shared_id_list.split(',');
            formatsArrayName = data[0].formats.split(',');
            formatsArrayFilterClosed = formatsArrayName.filter(e => e !== 'C');
            formatsArrayFilterOpen = formatsArrayFilterClosed.filter(d => d !== 'O');
            formatsArrayFilterClosed = formatsArrayFilterOpen.filter(e => e !== 'C');
            formatsArrayFilterOpen = formatsArrayFilterClosed.filter(d => d !== 'O');
            formatsArrayFilterTC = formatsArrayFilterOpen.filter(d => d !== 'TC');
            formatsArrayFilterHY = formatsArrayFilterTC.filter(d => d !== 'HY');
            formatsArrayFilter = formatsArrayFilterHY.filter(d => d !== 'VM');

            // Venue Type    VM is 50 | TC is 54 | HY is 55
            if ( !jQuery.inArray('50', formatsArray) > -1 && !jQuery.inArray('54', formatsArray)> -1  && jQuery.inArray('55', formatsArray) > -1 ) {
                jQuery('#choice_10_37_3').prop("checked", true);  // Hybrid (both in-person and virtual)
            }
            else if ( jQuery.inArray('50', formatsArray) > -1 && jQuery.inArray('54', formatsArray) > -1 && !jQuery.inArray('55', formatsArray) > -1 ) {
                jQuery('#choice_10_37_2').prop("checked", true);  // Virtual (temporarily replacing an in-person)
            }
            else if ( jQuery.inArray('50', formatsArray) > -1 && !jQuery.inArray('54', formatsArray) > -1 && !jQuery.inArray('55', formatsArray) > -1 ) {
                jQuery('#choice_10_37_1').prop("checked", true);  // Virtual
            }
            else if ( !jQuery.inArray('50', formatsArray) > -1 && !jQuery.inArray('54', formatsArray) > -1 && !jQuery.inArray('55', formatsArray) > -1 ) {
                jQuery('#choice_10_37_0').prop('checked', true);  // In-Person
            }

            // Published / Unpublished
            if  (data[0].published === "1") {
                jQuery('#choice_10_38_1').prop('checked', true);
            } else {
                jQuery('#choice_10_38_1').prop('checked', false);
            }


            // Closed
            if ( jQuery.inArray('4', formatsArray) > -1 ) {
                jQuery('#choice_10_13_1').prop('checked', true);
            }
            // Open
            else if ( jQuery.inArray('17', formatsArray) > -1 ) {
                jQuery('#choice_10_13_0').prop('checked', true);
            }

            if (formatsArrayFilter[0] !== null) {
                jQuery("#input_10_14").val(formatsArrayFilter[0]);
            }
            if (formatsArrayFilter[1] !== null) {
                jQuery("#input_10_18").val(formatsArrayFilter[1]);
            }
            if (formatsArrayFilter[2] !== null) {
                jQuery("#input_10_17").val(formatsArrayFilter[2]);
            }
            if (formatsArrayFilter[3] !== null) {
                jQuery("#input_10_16").val(formatsArrayFilter[3]);
            }
            if (formatsArrayFilter[4] !== null) {
                jQuery("#input_10_15").val(formatsArrayFilter[4]);
            }

            jQuery("#input_10_5_1").val(startTimeHour);
            jQuery("#input_10_5_2").val(startTimeMinute);
            jQuery("#input_10_5_3").val(startTimeMeridian);
            jQuery("#input_10_6_1").val(endTimeHour);
            jQuery("#input_10_6_2").val(endTimeMinute);
            jQuery("#input_10_6_3").val(endTimeMeridian);

            jQuery("#input_10_3").val(data[0].meeting_name);
            jQuery("#input_10_8").val(data[0].location_text);
            jQuery("#input_10_9").val(data[0].location_info);
            jQuery("#input_10_10_1").val(data[0].location_street);
            jQuery("#input_10_10_3").val(data[0].location_municipality);
            jQuery("#input_10_10_5").val(data[0].location_postal_code_1);

            serviceBodyName = getServiceBodyById(data[0].service_body_bigint)['name'];
            //serviceBodyName = serviceBodyName.replace(" Area", "");
            //serviceBodyName = serviceBodyName.trim();
            jQuery("#input_10_7").val(serviceBodyName);
            meetingCounty = data[0].location_sub_province;
            meetingCounty = meetingCounty.replace(" County", "");
            jQuery("#input_10_11").val(meetingCounty);

            var trainLine = data[0].train_lines.split ( '#@-@#' );
            var busLine = data[0].bus_lines.split ( '#@-@#' );

            jQuery("#input_10_33").val(data[0].virtual_meeting_link);
            jQuery("#input_10_34").val(data[0].phone_meeting_number);
            jQuery("#input_10_35").val(data[0].virtual_meeting_additional_info);
            jQuery("#input_10_20").val(trainLine[1]);
            jQuery("#input_10_19").val(busLine[1]);
            jQuery("#input_10_21").val(data[0].contact_name_1);
            jQuery("#input_10_23").val(data[0].contact_email_1);
            jQuery("#input_10_22").val(data[0].contact_phone_1);
            jQuery("#input_10_32").val(data[0].id_bigint);

        });

    });
});
