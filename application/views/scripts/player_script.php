<script>
var player_list_load_interval_id;
var player_list_interval = <?php echo PLAYER_LIST_LOAD_POLLING_SECONDS; ?>;
var player_list_at_bottom = false;
var player_list_initial_load = false;

// Detect if user is at bottom
$('#player_list_parent').scroll(function() {
    player_list_at_bottom = false;
    if ($('#player_list_parent').prop('scrollHeight') - $('#player_list_parent').scrollTop() <= Math.ceil($('#player_list_parent').height())) {
        player_list_at_bottom = true;
    }
});

$(document).ready(function(){
    // Load Messages
    clearInterval(player_list_load_interval_id);
    player_list_load(global_room_id, true);
    player_list_load_interval_id = setInterval(function() {
        player_list_load(global_room_id, false);
    }, player_list_interval * 1000);
});

// Message Load
function player_list_load(room_key, player_list_initial_load) {
    $.ajax({
        url: "<?=base_url()?>player/get_player_list/" + room_key,
        type: "GET",
        cache: false,
        success: function(response) {
            if (player_list_initial_load) {
                $("#player_list_parent").html('');
            }
            if (!response) {
                return;
            }
            // Parse players and loop through them
            data = JSON.parse(response);
            if (!data) {
                return false;
            }
            html = '';
            $.each(data['player_list'], function(i, player) {
                html += '<div class="player_card row">'
                html += player_info_block('Alias', player.username, 'string');
                html += player_info_block('Cash', player.cash, 'money');
                html += player_info_block('Thief', player.skill_thief, 'skill');
                html += player_info_block('Muscle', player.skill_muscle, 'skill');
                html += player_info_block('Driver', player.skill_driver, 'skill');
                html += player_info_block('Conman', player.skill_conman, 'skill');
                html += player_info_block('Cracker', player.skill_cracker, 'skill');
                html += player_info_block('Hacker', player.skill_hacker, 'skill');
                html += player_info_block('Fixer', player.skill_fixer, 'skill');
                html += player_info_block('Net Reputation', player.net_reputation, 'number');
                html += player_info_block('Sum Reputation', player.sum_reputation, 'number');
                html += player_info_block('Jobs Done', player.sum_jobs, 'number');
                html += '</div>'
            });
            // Append to div
            $("#player_list_parent").html(html);
            // Stay at bottom if at bottom
            if (player_list_at_bottom || player_list_initial_load) {
                player_list_scroll_to_bottom();
            }
        }
    });
}

function player_info_block(key, value, type) {
    value = player_stat_format(value, type);
    if (!value) {
        return '';
    }
    return `
    <div class="player_info_card col-md-4 col-sm-3 col-xs-4">
        <label class="${key}_label">
            ${key}
        </label>
        <span class="${key}_value">
            ${value}
        </span>
    </div>
    `;
}

function player_stat_format(value, type) {
    if (type === 'string') {
        return value;
    }
    if (type === 'number') {
        return value.replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }
    if (type === 'money') {
        return '$' + parseFloat(value).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }
    if (type === 'skill') {
        return 'Amateur';
    }
}

function player_list_scroll_to_bottom() {
    $("#player_list_parent").scrollTop($("#player_list_parent")[0].scrollHeight);
}
</script>