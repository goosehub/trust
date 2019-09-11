<div id="player_list_container">
    
    <div id="join_room" class="btn btn-action form-control" style="display: none;">
        Join room
    </div>
    
    <div id="leave_room" class="btn btn-danger form-control" style="display: none;">
        Leave room
    </div>

    <div id="player_list_parent">

        <?php
        $info = array(
            'Name' => 'goose',
            'Cash' => '$1,000,000',
            'Good Karma' => 26,
            'Bad Karma' => 0,
            'Net Karma' => null,
            'Jobs Done' => 57,
            'Time Served' => '30 minutes',
            'Thief' => 'Pro',
            'Conman' => 'Ace',
            'Message' => true ? '<i class="fa fa-envelope" aria-hidden="true"></i>' : null,
        )
        ?>

        <?php for ($i = 0; $i < 12; $i++) { ?>

        <div class="player_card row">
            <?php foreach ($info as $key => $value) { ?>
            <?php if (is_null($value)) { continue; } ?>
            <div class="player_info_card col-md-4 col-sm-3 col-xs-4">
                <label class="<?php echo $key; ?>_label">
                    <?php echo $key; ?>
                </label>
                <span class="<?php echo $key; ?>_value">
                    <?php echo $value; ?>
                </span>
            </div>
            <?php } ?>
        </div>

        <?php } ?>

    </div>

</div>