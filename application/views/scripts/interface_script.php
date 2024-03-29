<input id="share_url_input" type="text" class="input" style="display: none;"/>

<!-- Interface Script -->
<script>

$('.report_bugs_button').click(function(){
    $('#report_bugs_block').show();
});

$('#join_room').click(function(){
    join_room();
});
$('#leave_room').click(function(){
    leave_room();
});

var user;
<?php if ($user) { ?>
user = <?php echo json_encode($user); ?>;
var user_load_polling_seconds = <?php echo USER_LOAD_POLLING_SECONDS; ?>;
<?php } ?>

<?php if (!$landing && $user) { ?>
var favorite_room_keys = new Array();
var favorite_rooms = <?php echo json_encode($user['favorite_rooms']); ?>;
favorite_rooms.forEach(function (favorite_room) {
    favorite_room_keys.push(parseInt(favorite_room.room_key));
});

var joined_room_keys = new Array();
var joined_rooms = <?php echo json_encode($user['joined_rooms']); ?>;
joined_rooms.forEach(function (joined_room) {
    joined_room_keys.push(parseInt(joined_room.room_key));
});
<?php } ?>

// Error reporting
<?php if ($failed_form === 'register') { ?>
    $('#register_block').show();
<?php } ?>
<?php if ($failed_form === 'login') { ?>
    // Show login form if not logged in and not failed to log in
    if (!user) {
        $('#login_block').show();
    }
<?php } else if (isset($_GET['login'])) { ?>
    // Show login form if URL calls for it
    $('#login_block').show();
<?php } ?>

// Validation errors shown on page load if exist
<?php if ($failed_form === 'login') { ?>
$('#login_block').show();
<?php } else if ($failed_form === 'register') { ?> 
$('#register_block').show();
<?php } ?>

$('#input_user_location').click(function(event){
    event.stopPropagation();
});

<?php if (!$landing) { ?>
function join_room() {
    if (!global_room_id) {
        return false;
    }
    data = {};
    data.room_key = global_room_id;
    ajax_post('user/join_room', data, function(response){
        console.log('marco');
        $('#join_room').hide();
        $('#leave_room').show();
        player_list_load(global_room_id, false);
    });
}
function leave_room() {
    if (!global_room_id) {
        return false;
    }
    data = {};
    ajax_post('user/leave_room', data, function(response){
        $('#join_room').show();
        $('#leave_room').hide();
        player_list_load(global_room_id, false);
    });
}

function update_location() {
    user_location = $('#input_user_location').val();
    if (!user_location) {
        return false;
    }
    data = {};
    data.location = user_location;
    ajax_post('user/update_location', data, function(response){
        $('.user_menu_parent').dropdown('toggle');
    });
}

function getShareUrl(filter) {
    let current_lat = map.getCenter().lat().toFixed(4);
    let current_lng = map.getCenter().lng().toFixed(4);
    let current_zoom = map.getZoom();
    let current_map_type = map.getMapTypeId();
    let current_filter = filter ? filter : current_last_activity_slug;
    return '<?=base_url()?>world'
    + '?lat=' + current_lat 
    + '&lng=' + current_lng 
    + '&zoom=' + current_zoom 
    + '&map_type=' + current_map_type
    + '&last_activity=' + current_filter
    + window.location.hash;
}

// Room close
$('#room_exit').click(function(){
    $('#room_parent').fadeOut();
});

// Copy Share URL
let share_message_seconds = 3;
$('#share_button').click(function(){
    let url = getShareUrl();
    setClipboard(url);
    setTimeout(function(){
        $('.share_menu_parent').removeClass('open');
    }, share_message_seconds * 1000);
});

$('.filter_link').click(function(){
    let filter = $(this).attr('filter');
    let url = getShareUrl(filter);
    window.location.href = url;
});
<?php } ?>

<?php if (!$landing && $user) { ?>
// Load favorite rooms on interval
load_user();
setInterval(function(){
    load_user();
}, user_load_polling_seconds * 1000);

function load_user() {
    var url = 'main/load_user/';
    ajax_get(url, function(result){
        favorite_rooms = result.favorite_rooms;
        update_favorite_rooms();
    });
}

function update_favorite_rooms() {
    if (!favorite_room_keys) {
        return;
    }
    favorite_room_keys = new Array();
    favorite_rooms.forEach(function (favorite_room) {
        favorite_room_keys.push(parseInt(favorite_room.room_key));
    });
    $('.favorite_room_listing').remove();
    let loop_length = favorite_rooms.length;
    for (var i = 0; i < loop_length; i++) {
        let favorite_room = favorite_rooms[i];
        $('#favorites_dropdown').append(
            '<li class="favorite_room_listing">' + 
                '<a class="favorite_room_link text-center" room_id="' + favorite_room.room_key + '">' + 
                    favorite_room.name + 
                '</a>' + 
            '</li>'
        );
    }
}
<?php } ?>

// 
// Center block hide and show logic
// 

$('.exit_center_block').click(function(){
    $('.center_block').hide();
    $('.landing_center_block').hide();
});
$('.login_button').click(function(){
        $('.center_block').hide();
        $('#login_block').show();
        $('#login_input_username').focus();
        $('.current_url').val(window.location.href);
});
$('.register_button').click(function(){
        $('.center_block').hide();
        $('#register_block').show();
        $('#register_input_username').focus();
        $('.current_url').val(window.location.href);
});
$('.landing_login_button').click(function(){
        if ($(this).hasClass('btn-primary')) {
            $('.landing_center_block').hide();
            $('.landing_login_button').removeClass('btn-primary').addClass('btn-default')
            return;
        }
        $('.landing_login_button').addClass('btn-primary').removeClass('btn-default');
        $('.landing_register_button').removeClass('btn-primary').addClass('btn-default');
        $('.landing_center_block').hide();
        $('#login_block').show();
});
$('.landing_register_button').click(function(){
        if ($(this).hasClass('btn-primary')) {
            $('.landing_center_block').hide();
            $('.landing_register_button').removeClass('btn-primary').addClass('btn-default')
            return;
        }
        $('.landing_register_button').addClass('btn-primary').removeClass('btn-default');
        $('.landing_login_button').removeClass('btn-primary').addClass('btn-default');
        $('.landing_center_block').hide();
        $('#register_block').show();
});
$('.room_type_select_public').click(function(){
    $('.room_type_select_crew').removeClass('btn-action').addClass('btn-default');
    $('.room_type_select_public').removeClass('btn-default').addClass('btn-action');
    $('#input_is_base').val(0);
    $('.room_type_public').show();
    $('.room_typt_crew').hide();
});
$('.room_type_select_crew').click(function(){
    $('.room_type_select_public').removeClass('btn-action').addClass('btn-default');
    $('.room_type_select_crew').removeClass('btn-default').addClass('btn-action');
    $('#input_is_base').val(1);
    $('.room_typt_crew').show();
    $('.room_type_public').hide();
});

</script>