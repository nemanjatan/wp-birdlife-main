// This script needs to go to `Header` part of Beaver Builder

jQuery(document).ready(function ($) {
    // Events
    $.ajax({
        method: "POST",
        url: ajaxurl,
        data: {'action': 'get_last_sync'}
    })
        .done(function (data) {
            var json = JSON.parse(data);
            var currentTimestampInSeconds = Math.floor(Date.now() / 1000);
            var lastSync = json['last_sync'];

            if (json['type'] === 'Daily') {
                if ((currentTimestampInSeconds - lastSync) > 86400) {
                    // one day passed, run the script
                    console.log("Starting hard refresh after one day");

                    $.ajax({
                        method: "POST",
                        url: ajaxurl,
                        data: {'action': 'hard_refresh_action'}
                    }).done(function () {
                        console.log('Automatic hard refresh completed!');
                    }).fail(function () {
                        console.log('Automatic hard refresh failed!');
                    })
                } else {
                    console.log("Hard refresh was done less than one day ago.");
                }
            } else if (json['type'] === 'Hourly') {
                if ((currentTimestampInSeconds - lastSync) > 3600) {
                    // one hour passed, run the script
                    console.log("Starting hard refresh after one hour");
                    $.ajax({
                        method: "POST",
                        url: ajaxurl,
                        data: {'action': 'hard_refresh_action'}
                    }).done(function () {
                        console.log('Automatic hard refresh completed!');
                    }).fail(function () {
                        console.log('Automatic hard refresh failed!');
                    })
                } else {
                    console.log("Hard refresh for events was done less than one hour ago.");
                }
            }
        })
        .fail(function (data) {
            console.log('Failed AJAX Call for hard_refresh_action :( /// Return Data: ' + data);
        });

    // Projects
    $.ajax({
        method: "POST",
        url: ajaxurl,
        data: {'action': 'get_last_sync_for_projects'}
    })
        .done(function (data) {
            var json = JSON.parse(data);
            var currentTimestampInSeconds = Math.floor(Date.now() / 1000);
            var lastSync = json['last_sync'];

            if (json['type'] === 'Weekly') {
                if ((currentTimestampInSeconds - lastSync) > 604800) {
                    // one week passed, run the script
                    console.log("Starting hard refresh for projexts after one week");

                    $.ajax({
                        method: "POST",
                        url: ajaxurl,
                        data: {'action': 'projects_hard_refresh_action'}
                    }).done(function () {
                        console.log('Automatic hard refresh for projects completed!');
                    }).fail(function () {
                        console.log('Automatic hard refresh for projects failed!');
                    })
                } else {
                    console.log("Hard refresh was done less than one week ago.");
                }
            } else if (json['type'] === 'Monthly') {
                if ((currentTimestampInSeconds - lastSync) > 2628000) {
                    // one month passed, run the script
                    console.log("Starting hard refresh for projects after one month");
                    $.ajax({
                        method: "POST",
                        url: ajaxurl,
                        data: {'action': 'projects_hard_refresh_action'}
                    }).done(function () {
                        console.log('Automatic hard refresh for projects completed!');
                    }).fail(function () {
                        console.log('Automatic hard refresh for projects failed!');
                    })
                } else {
                    console.log("Hard refresh for projects was done less than one month ago.");
                }
            }
        })
        .fail(function (data) {
            console.log('Failed AJAX Call for get_last_sync_for_projects :( /// Return Data: ' + data);
        });
});