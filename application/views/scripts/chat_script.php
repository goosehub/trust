<script>
var last_message_id = 0;
var chat_at_bottom = true;
var load_messages = true;
var window_active = true;
var page_title = '';
var missed_messages = 0;
var users_array = new Array();
var room_name = '<?php echo site_name(); ?>';
var load_interval = <?php echo CHAT_LOAD_POLLING_SECONDS; ?>;
var system_user_id = <?php echo SYSTEM_USER_ID; ?>;
var global_room_id = null;

$(document).on('click', '.message_pin', function(event) {
    pin_action(event);
});

// On tab press in message input
document.querySelector('#message_input').addEventListener('keydown', function (e) {
    if (e.which == 9) {
        autocomplete_username();
        e.preventDefault();
    }
});

$('#toggle_theme').click(function(event) {
    toggle_theme($('#toggle_theme').hasClass('active'));
});

// Keep dropdown open on altering color
$('#input_user_color').click(function(event){
    event.stopPropagation();
});

// Update color on change of color
$('#input_user_color').change(function(event){
    data = {};
    data.color = $(this).val();
    ajax_post('user/update_color', data, function(response){
        $('#input_user_color')[0].jscolor.hide();
    });
});

// Detect if user is at bottom
$('#message_content_parent').scroll(function() {
    chat_at_bottom = false;
    if ($('#message_content_parent').prop('scrollHeight') - $('#message_content_parent').scrollTop() <= Math.ceil($('#message_content_parent').height())) {
        chat_at_bottom = true;
    }
});

// Favorite room
$('#favorite_room_button').click(function(){
    room_id = current_marker.room_id
    favorite_room(room_id);
});

// Join crew room on button click
$('#join_crew_room').off().click(function(){
    join_crew_room();
});

$(document).ready(function(){

    // If client has theme stored and value is light, toggle to light
    if (localStorage.getItem('theme') === 'light') {
        toggle_theme(true);
    }
    else if (localStorage.getItem('theme') === 'dark') {
        toggle_theme(false);
    }
    else  {
        toggle_theme(false);
    }

    // Detect if window is open
    $(window).blur(function() {
        window_active = false;
    });
    $(window).focus(function() {
        missed_messages = 0;
        $('title').html(room_name);
        window_active = true;
    });

    // If hash exists, it is a room id, load that room
    if (window.location.hash) {
        // Remove hash to get room id and load room
        var room_id = window.location.hash.replace('#', '');
        load_room(room_id);
    }
});

// Load favorite room
$('#favorites_dropdown').on('click', '.favorite_room_link', function(){
    room_id = $(this).attr('room_id') ? $(this).attr('room_id') : $(this).parent().attr('room_id');
    load_room(room_id);
});

function join_crew_room() {
    data = {};
    data.room_id = room_id;
    data.room_passcode = $('#input_room_passcode_join').val();
    ajax_post('room/join_crew_room', data, function(room){
        load_room(room_id);
    });
}

function load_pm(receiving_user_key, receiving_username, sending_username) {
    data = {};
    data.receiving_user_key = receiving_user_key;
    data.sending_username = sending_username;
    data.receiving_username = receiving_username;
    ajax_post('room/create_pm_room', data, function(room){
        setTimeout(function(){
            load_room(room.id);
        }, 1000);
    });
};

function toggle_room_join() {
    $('#join_room, #leave_room').hide();
    if (parseInt(user.room_key) === parseInt(global_room_id)) {
        $('#leave_room').show();
    }
    else {
        $('#join_room').show();
    }
}

function load_room(room_id) {
    global_room_id = room_id;
    toggle_room_join();
    // Get room
    ajax_get('room/get_room/' + room_id, function(room){
        // Ensure chat window is set up
        $('#message_input').show();
        $('#message_input').focus();
        $('#room_parent').fadeIn();

        if (!room.id) {
            return false;
        }

        // Set up room
        window.location.hash = room_id;
        let room_name = parse_room_name(room.name);
        let passcode_string = room.room_passcode ? 'PC: ' + room.room_passcode : '';
        $('.room_name').html(room_name);
        $('#room_passcode').html(passcode_string);
        $('#zoom_out_button').hide();
        $('#zoom_in_button').show();

        // Switch marker icons
        if (current_marker) {
            current_marker.setIcon(find_icon_to_use(current_marker.room_id, current_marker.is_base));
        }
        // if (current_marker && favorite_room_keys.includes(parseInt(current_marker.room_id))) {
        //     current_marker.setIcon(favorite_marker_img);
        // }
        // else if (current_marker) {
        //     current_marker.setIcon(default_marker_img);
        // }
        if (markers[room_id]) {
            markers[room_id].setIcon(current_marker_img);
            current_marker = markers[room_id];
        }

        $('#message_outer_parent').show();
        $('#passcode_enter_parent').hide();
        if (parseInt(room.is_base) && !room.is_member) {
            $('#passcode_enter_parent').show();
            $('#message_outer_parent').hide();
        }

        // Favorite button
        $('#favorite_room_button').removeClass('btn-success').removeClass('btn-default').show();
        if (room.is_favorite) {
            $('#favorite_room_button').addClass('btn-success');
        }
        else {
            $('#favorite_room_button').addClass('btn-default');
        }

        // Load Messages
        clearInterval(messages_load_interval_id);
        messages_load(room_id, true);
        messages_load_interval_id = setInterval(function() {
            messages_load(room_id, false);
        }, load_interval * 1000);

        player_list_load(room_id);
    });
}

function update_room_name() {

}

function favorite_room(room_id) {
    // Send request
    data = {};
    data.room_id = room_id;
    ajax_post('room/favorite', data, function(response){
        // Activate favorite button
        if ($('#favorite_room_button').hasClass('btn-success')) {
            $('#favorite_room_button').removeClass('btn-success').addClass('btn-default');
        }
        else {
            $('#favorite_room_button').removeClass('btn-default').addClass('btn-success');
        }
        load_user();
    });
}

function parse_room_name(room_name) {
    if (!room_name.includes('|')) {
        return room_name
    }
    let names = room_name.split('|');
    let pm_from = names[0] === user.username ? names[1] : names[0];
    return pm_from;
}

// Message Load
function messages_load(room_key, chat_inital_load) {
    if (!load_messages) {
        return false;
    }
    if (chat_inital_load) {
        $('#input_room_id').val(room_key);
        $("#message_content_parent").html('');
        last_message_id = 0;
    }
    else {
        var room_key = $('#input_room_id').val();
    }
    $.ajax({
        url: "<?=base_url()?>chat/load",
        type: "POST",
        data: {
            user_key: user.id,
            room_key: room_key,
            chat_inital_load: chat_inital_load,
            last_message_id: last_message_id
        },
        cache: false,
        success: function(response) {
            var html = '';
            // Emergency force reload
            if (response === 'reload') {
                window.location.reload(true);
            }
            if (chat_inital_load) {
                $("#message_content_parent").html('');
            }
            if (!response) {
                return;
            }
            // Parse messages and loop through them
            messages = JSON.parse(response);
            if (!messages) {
                return false;
            }
            // Handle errors
            if (messages.error && load_messages && window_active) {
                // Prevent stacking errors
                load_messages = false;
                // Alert user
                alert(messages.error + '. You\'ll be redirected so you can rejoin the room.');
                // Redirect to try to rejoin user
                window.location = '<?=base_url()?>?room=' + room_id;
                // Prevent more execution
                return false;
            }
            if (!messages.messages) {
                last_message_id = 0;
                return true;
            }

            // Unread pm rooms
            unread_pm_rooms_html = '';
            $.each(messages.unread_pm_rooms, function(i, pm_room) {
                // Don't include current room
                if (pm_room.id === room_id) {
                    return;
                }
                let pm_from = parse_room_name(pm_room.name);
                unread_pm_rooms_html += '<li class="unread_pm_room_listing">';
                unread_pm_rooms_html += '<a class="unread_pm_room_link text-center" href="#' + pm_room.id + '" onclick="load_room(' + pm_room.id + ')">';
                unread_pm_rooms_html += pm_from;
                unread_pm_rooms_html += '</a></li>';
            });
            if (unread_pm_rooms_html) {
                $('.unread_pm_rooms_menu_parent').show();
                $('#unread_pm_rooms').html(unread_pm_rooms_html);
            }
            else {
                $('.unread_pm_rooms_menu_parent').hide();
            }

            $.each(messages.messages, function(i, message) {
                // Skip if we already have this message, although we really shouldn't
                if (parseInt(message.id) <= parseInt(last_message_id)) {
                    return true;
                }
                // Update latest message id
                last_message_id = message.id;
                // If window is not active, give feedback in tab title
                if (!window_active && !chat_inital_load && message.user_key != system_user_id) {
                    missed_messages++;
                    $('title').html('(' + missed_messages + ') ' + room_name);
                }
                if (message.username === 'system_date') {
                    message.message = systemDateFormat(message.message);
                }
                // System Messages
                if (parseInt(message.user_key) === system_user_id) {
                    html += '<div class="system_message ' + message.username + '">' + message.message + '</div>';
                    return true;
                }
                // Process message
                var message_message = embedica(message.message);
                // Wrap @username with span
                message_message = convert_at_username(message_message);
                // Detect if youtube
                // build message html
                html += '<div class="message_parent">';
                html += '<span class="message_face glyphicon glyphicon-user" title="' + message.timestamp + ' ET" style="color: ' + message.color + ';"></span>';
                <?php if ($user) { ?>
                html += '<a href="#" class="pm_link" style="color: ' + message.color + ';" onclick="load_pm(' + message.user_key + ', \'' + message.username + '\', \'' + user.username + '\')" title="Private Message"><small class="glyphicon glyphicon-envelope"></small></a> ';
                <?php } ?>
                if (use_pin(message_message)) {
                    html += '<span class="message_pin glyphicon glyphicon-pushpin" style="color: ' + message.color + ';"></span>';
                }
                html += '<span class="message_username" style="color: ' + message.color + ';">' + message.username + '</span>';
                <?php if (ENABLE_CHAT_REPORTING) { ?>
                html += '<a href="<?=base_url()?>chat/report/' + message.id + '" target="_blank" class="report_link" title="Report this post"><small class="glyphicon glyphicon-flag"></small></a> ';
                <?php } ?>
                html += '<span class="message_message">' + message_message + '</span>';
                html += '</div>';
            });
            // Append to div
            $("#message_content_parent").append(html);
            // Stay at bottom if at bottom
            if (chat_at_bottom || chat_inital_load) {
                chat_scroll_to_bottom();
            }
        }
    });
}

// New Message
function submit_new_message(event) {
    // Message input
    var message_input = $("#message_input").val();
    var room_key = $('#input_room_id').val();
    // Empty chat input
    $('#message_input').val('');
    $.ajax({
        url: "<?=base_url()?>chat/new_message",
        type: "POST",
        data: {
            message_input: message_input,
            room_key: room_key,
        },
        cache: false,
        success: function(response) {
            // All responses are error messsages
            if (response) {
                alert(response);
                return false;
            }
            // Load log so user can instantly see his message
            messages_load(room_key, false);
            // Focus back on input
            $('#message_input').focus();
            // Scroll to bottom
            chat_scroll_to_bottom();
        }
    });
    return false;
}

function autocomplete_username() {
    if ($('#message_input').val().startsWith('@')) {
        var parsed_text_input = $('#message_input').val().replace('@','').toLowerCase();
        for (var i = 0; i < users_array.length; i++) {
            if (users_array[i].username.toLowerCase().startsWith(parsed_text_input)) {
                $('#message_input').val('@' + users_array[i].username);
            }
        }
    }
}

function pin_action(event) {
    if (!$(event.target).hasClass('active_pin')) {
        $('.active_pin').removeClass('active_pin');
        $('.pinned').removeClass('pinned')
        $(event.target).addClass('active_pin');
        $(event.target).parent().addClass('pinned')
    } else {
        $(event.target).removeClass('active_pin');
        $(event.target).parent().removeClass('pinned')
    }
}

function convert_at_username(input) {
    var pattern = /^\@\w+/g;
    if (pattern.test(input)) {
        var at_username = input.split(' ')[0];
        if (!at_username) {
            return input;
        }
        var replacement = '<span class="at_username">' + at_username + '</span>';
        var input = input.replace(pattern, replacement);
    }
    return input;
}

function use_pin(message) {
    if (
        string_contains(message, 'embedica_youtube') ||
        string_contains(message, 'embedica_vimeo') ||
        string_contains(message, 'embedica_twitch') ||
        string_contains(message, 'embedica_soundcloud') ||
        string_contains(message, 'embedica_vocaroo') ||
        string_contains(message, 'embedica_video') ||
        string_contains(message, 'embedica_image')
    ) {
        return true;
    }
    return false;
}

function chat_scroll_to_bottom() {
    $("#message_content_parent").scrollTop($("#message_content_parent")[0].scrollHeight);
}

function toggle_theme(light_theme) {
    if (light_theme) {
        $('#toggle_theme').removeClass('active');
        $('#toggle_icon').removeClass('fa-toggle-off').addClass('fa-toggle-on');
        $('#room_parent').addClass('light');
        $('#message_content_parent').addClass('light');
        localStorage.setItem('theme', 'light');
    } else {
        $('#toggle_theme').addClass('active');
        $('#toggle_icon').removeClass('fa-toggle-on').addClass('fa-toggle-off');
        $('#room_parent').removeClass('light');
        $('#message_content_parent').removeClass('light');
        localStorage.setItem('theme', 'dark');
    }
}

// https://stackoverflow.com/a/6078873/3774582
function systemDateFormat(unix_timestamp){
    var a = new Date(unix_timestamp * 1000);
    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    var year = a.getFullYear();
    var month = months[a.getMonth()];
    var date = a.getDate();
    var hour = a.getHours();
    // var sec = a.getSeconds();
    // var offset = a.getTimezoneOffset();

    // https://stackoverflow.com/a/8888498/3774582
    var am_pm = hour >= 12 ? 'PM' : 'AM';
    hour = hour % 12;
    hour = hour ? hour : 12; // the hour '0' should be '12'
    var minutes = a.getMinutes();
    minutes = minutes < 10 ? '0'+minutes : minutes;

    // https://stackoverflow.com/a/34405528/3774582
    var timezone_abbr = new Date().toLocaleTimeString('en-us',{timeZoneName:'short'}).split(' ')[2];
    var time = hour + ':' + minutes + ' ' + am_pm + ' ' + timezone_abbr + ' ' + date + ' ' + month + ' ' + year;
    return time;
}

</script>