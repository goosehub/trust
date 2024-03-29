<div id="room_parent">

    <div id="room_toolbar">

        <strong class="room_name room_title">
            Trust
        </strong>
        <strong id="room_passcode" class="monospace"></strong>

        <div id="room_exit" class="btn btn-sm btn-danger pull-right">
            <i class="fa fa-times-circle" aria-hidden="true"></i>
        </div>

        <?php if ($user) { ?>
        <div id="favorite_room_button" class="btn btn-sm btn-default pull-right" style="display: none;">
            <i class="fa fa-star" aria-hidden="true"></i>
        </div>
        <?php } ?>

        <div id="toggle_theme" class="btn btn-sm btn-info pull-right active">
            <i id="toggle_icon" class="fa fa-toggle-off" aria-hidden="true"></i>
        </div>

        <div id="zoom_in_button" class="btn btn-sm btn-warning pull-right" style="display: none;">
            <i class="fa fa-search-plus" aria-hidden="true"></i>
        </div>

        <div id="zoom_out_button" class="btn btn-sm btn-action pull-right" style="display: none;">
            <i class="fa fa-search-minus" aria-hidden="true"></i>
        </div>

        <a id="toggle_theme" class="btn btn-sm btn-primary pull-right active" target="_blank" href="https://imgur.com/upload">
            <i id="toggle_icon" class="fa fa-image" aria-hidden="true"></i>
        </a>

    </div>

    <div id="passcode_enter_parent" class="container-fluid" style="display: none;">
        <div class="row">
            <div class="col-md-6 col-md-push-3">
                <br>
                <br>
                <br>
                Join 
                <strong class="room_name">
                    Trust
                </strong>
                <div class="form-group">
                    <label for="input_room_passcode_join">
                        <i class="fa fa-comments" aria-hidden="true"></i>
                        Enter Passcode
                    </label>
                    <input type="text" class="form-control" id="input_room_passcode_join" name="room_passcode" placeholder="">
                </div>
                <button id="join_crew_room" type="submit" class="btn btn-action form-control">
                    Join
                    <i class="fa fa-arrow-right" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </div>

    <div id="message_outer_parent">
        <div id="message_content_parent">
            <div id="empty_room_message" class="text-center">
                <p>Info on what to do</p>
            </div>
        </div>
        <div id="message_input_parent">
            <form id="new_message" onsubmit="return submit_new_message()">
                <input type="hidden" id="input_room_id" name="room_id" value=""/>
                <input type="text" name="message_input" class="form-control" id="message_input" autocomplete="off" value="" placeholder="" style="display: none;"/>
                <!-- submit button positioned off screen -->
                <input name="submit_message" type="submit" id="submit_message" value="true" style="position: absolute; left: -9999px">
            </form>
        </div>
    </div>

</div>