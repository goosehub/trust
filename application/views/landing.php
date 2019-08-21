<div class="container-fluid landing-container">
    <div class="row">
        <div class="col-md-6 left-landing blue-background-color">
            <h1 class="text-center landing-site-title black-color">
                <?php echo strtoupper(site_name()); ?>
            </h1>
            <h2 class="text-center" style="font-family: monospace;">
                In Pre-Alpha
            </h2>
            <br class="hidden-xs hidden-sm">
            <div class="row">
                <div class="col-sm-2">
                </div>
                <div class="col-sm-8">
                    <p class="lead black-color landing-lead-text">
                        <i class="fa fa-globe" aria-hidden="true"></i>
                        A Game Of Skill
                    </p>
                    <br class="hidden-xs hidden-sm">
                    <p class="lead black-color landing-lead-text">
                        <i class="fa fa-map" aria-hidden="true"></i>
                        A Game Of Crime
                    </p>
                    <br class="hidden-xs hidden-sm">
                    <p class="lead black-color landing-lead-text">
                        <i class="fa fa-street-view" aria-hidden="true"></i>
                        A Game Of Trust
                    </p>
                </div>
                <div class="col-sm-2">
                </div>
            </div>
            <br class="hidden-xs hidden-sm">

            <!-- Login Block -->
            <div class="row">
                <div class="col-sm-2">
                </div>
                <div class="col-sm-8">

                    <!-- Login and Join -->
                    <?php if (!$user) { ?>
                    <div class="text-center">
                        <div class="row">
                            <div class="landing_login_button_parent col-sm-6">
                                <button class="landing_login_button landing_action_button form-control menu_element btn btn-default">
                                    <i class="fa fa-sign-in" aria-hidden="true"></i>
                                    Login
                                </button>
                            </div>
                            <div class="landing_register_button_parent col-sm-6">
                                <button class="landing_register_button landing_action_button form-control menu_element btn btn-default">
                                    <i class="fa fa-user-plus" aria-hidden="true"></i>
                                    Join
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php } ?>

                    <?php if (!$user) { ?>
                    <div id="login_block" class="landing_center_block well">
                        <strong>
                            <i class="fa fa-sign-in" aria-hidden="true"></i>
                            Login
                        </strong>

                        <!-- Validation Errors -->
                        <span class="text-danger">
                            <?php if ($failed_form === 'login') { echo $validation_errors; } ?>
                        </span>
                        <!-- Form -->
                        <?php echo form_open('user/login'); ?>
                            <div class="form-group">
                                <label for="input_username">
                                    <i class="fa fa-user-circle" aria-hidden="true"></i>
                                    Username
                                </label>
                                <input type="username" class="form-control" id="login_input_username" name="username" placeholder="Username">
                            </div>
                            <div class="form-group">
                                <label for="input_password">
                                    <i class="fa fa-key" aria-hidden="true"></i>
                                    Password
                                </label>
                                <input type="password" class="form-control" id="login_input_password" name="password" placeholder="Password">
                            </div>
                            <button type="submit" class="btn btn-action form-control">
                                Login
                                <i class="fa fa-arrow-right" aria-hidden="true"></i>
                            </button>
                        </form>
                    </div>
                    <?php } ?>

                    <!-- Join Block -->
                    <?php if (!$user) { ?>
                    <div id="register_block" class="landing_center_block well">
                        <strong>
                            <i class="fa fa-user-plus" aria-hidden="true"></i>
                            Join
                        </strong>

                        <!-- Validation Errors -->
                        <span class="text-danger">
                            <?php if ($failed_form === 'register') { echo $validation_errors; } ?>
                        </span>
                        <!-- Form -->
                        <?php echo form_open('user/register'); ?>
                            <div class="form-group">
                                <input type="hidden" name="bee_movie" id="bee_movie" value="">
                                <input type="hidden" name="ab_test" id="ab_test" value="">
                                <input type="hidden" name="register_location" value="">
                                <label for="input_username">
                                    <i class="fa fa-user-circle" aria-hidden="true"></i>
                                    Username
                                </label>
                                <input type="username" class="form-control" id="register_input_username" name="username" placeholder="">
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="input_password">
                                            <i class="fa fa-key" aria-hidden="true"></i>
                                            Password
                                        </label>
                                        <input type="password" class="form-control" id="register_input_password" name="password" placeholder="Password">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="input_confirm">
                                            <i class="fa fa-key" aria-hidden="true"></i>
                                            Confirm
                                        </label>
                                        <input type="password" class="form-control" id="register_input_confirm" name="confirm" placeholder="Confirm">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="register_color">
                                            <i class="fa fa-commenting-o" aria-hidden="true"></i>
                                            Color
                                        </label>
                                        <input type="text" class="jscolor color_input form-control" id="register_color" name="register_color" value="<?php echo $random_color; ?>">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-action form-control">
                                Join
                                <i class="fa fa-arrow-right" aria-hidden="true"></i>
                            </button>
                        </form>
                    </div>
                    <?php } ?>

                    <?php if ($user) { ?>
                    <div class="well">
                        <a href="" class="btn btn-lg btn-action">
                            Go To Game
                        </a>
                        <p class="pull-right">
                            <a class="logout_button" href="<?=base_url()?>user/logout">
                                <small>
                                    <i class="fa fa-sign-out" aria-hidden="true"></i>
                                    Logout
                                </small>
                            </a>
                        </p>
                        <div class="clearfix"></div>
                    </div>
                    <?php } ?>
                    <br class="hidden-xs hidden-sm">
                    <br class="hidden-xs hidden-sm">
                    <div class="landing_links_parent text-center">
                        <span>
                            <a class="btn btn-link landing_link" href="https://github.com/goosehub/trust" target="_blank">
                                <i class="fa fa-github" aria-hidden="true"></i>
                                GitHub
                            </a>
                        </span>
                        <span>
                            <a class="btn btn-link landing_link" href="https://gooseweb.io/" target="_blank">
                                <i class="fa fa-code" aria-hidden="true"></i>
                                GooseWeb
                            </a>
                        </span>
                        <span>
                            <a class="btn btn-link landing_link" href="https://www.reddit.com/r/trustgame/" target="_blank">
                                <i class="fa fa-reddit-alien" aria-hidden="true"></i>
                                /r/trustgame
                            </a>
                        </span>
                        <span>
                            <a class="report_bugs_button btn btn-link landing_link" href="javascript:;">
                                <i class="fa fa-bug" aria-hidden="true"></i>
                                Report Bugs
                            </a>
                        </span>
                    </div>
                </div>
                <div class="col-sm-2">
                </div>
            </div>
        </div>
        <div class="col-md-6 no-float right-landing black-background-color">
            <br class="hidden-xs hidden-sm">
            <div class="well">
                <h2>
                    How To Play:
                </h2>
                <p class="lead">
                    Coming soon
                </p>
            </div>
        </div>
    </div>
</div>