<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Player extends CI_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model('main_model', '', TRUE);
        $this->load->model('user_model', '', TRUE);
        $this->load->model('room_model', '', TRUE);

        $this->main_model->record_request();
    }

    public function get_player_list($room_key = false)
    {
        // Validate input
        if (!$room_key) {
            echo api_error_response('room_key_missing', 'Room id is a required parameter and was not provided.');
            return false;
        }

        // Get player list
        $data['player_list'] = $this->user_model->get_all_users_by_room_key($room_key);

        // Handle player_list not found
        if (!$data['player_list']) {
            echo api_error_response('player_list_not_found', 'That player_list was not found.');
            return false;
        }

        // Return room
        echo api_response($data);
    }
}