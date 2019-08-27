<!-- create_room_block Block -->
<div id="create_room_block" class="center_block">
    <strong>
        <i class="fa fa-map-marker" aria-hidden="true"></i>
        Create A Room Here
    </strong>

    <button type="button" class="exit_center_block btn btn-default btn-sm">
        <span class="glyphicon glyphicon-remove-sign" aria-hidden="true"></span>
    </button>

    <div class="clearfix"></div>
    <br>

    <div class="text-center">
        <div class="row">
            <div class="room_type_select_parent col-sm-6">
                <button class="room_type_select_public room_type_action_button form-control menu_element btn btn-action">
                    <i class="fa fa-user" aria-hidden="true"></i>
                    <i class="fa fa-user" aria-hidden="true"></i>
                    <i class="fa fa-user" aria-hidden="true"></i>
                    Public
                </button>
            </div>
            <div class="landing_register_button_parent col-sm-6">
                <button class="room_type_select_crew room_type_action_button form-control menu_element btn btn-default">
                    <i class="fa fa-users" aria-hidden="true"></i>
                    Crew
                </button>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="input_room_name">
            <i class="fa fa-comments" aria-hidden="true"></i>
            Name
        </label>
        <input type="text" class="form-control" id="input_room_name" name="room_name" placeholder="">
        <input type="hidden" id="input_is_base" name="is_base" value="">
    </div>
    <div class="form-group room_typt_crew" style="display: none;">
        <label for="input_room_passcode">
            <i class="fa fa-comments" aria-hidden="true"></i>
            Crew Passcode
        </label>
        <input type="text" class="form-control" id="input_room_passcode" name="room_passcode" placeholder="">
    </div>
    <button id="create_room_submit" type="submit" class="btn btn-action form-control">
        Create
        <i class="fa fa-arrow-right" aria-hidden="true"></i>
    </button>
</div>