<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Room extends CI_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model('main_model', '', TRUE);
        $this->load->model('user_model', '', TRUE);
        $this->load->model('room_model', '', TRUE);
        $this->load->model('chat_model', '', TRUE);

        $this->main_model->record_request();
    }

    public function get_room($room_id = false)
    {
        // Validate input
        if (!$room_id) {
            echo api_error_response('room_id_missing', 'Room id is a required parameter and was not provided.');
            return false;
        }

        // Get room
        $room = $this->room_model->get_room_by_id($room_id);

        // Authentication
        $user = $this->user_model->get_this_user();
        $room['is_favorite'] = false;
        if ($user) {
            $room['is_favorite'] = $this->room_model->get_favorite_room($user['id'], $room_id);
        }
        $room['is_member'] = false;
        if ($user) {
            $room['is_member'] = $this->room_model->get_room_memeber($user['id'], $room_id);
        }
        if (!$room['is_member']) {
            $room['room_passcode'] = '';
        }

        // Handle room not found
        if (!$room) {
            echo api_error_response('room_not_found', 'That room was not found.');
            return false;
        }

        // Return room
        echo api_response($room);
    }

    public function join_crew_room()
    {
        // Authentication
        $user = $this->user_model->get_this_user();

        // Validate input
        $input = get_json_post(true);
        if (!$user) {
            echo api_error_response('auth_missing', 'Must be logged in.');
            return false;
        }
        if (!isset($input->room_passcode) || !$input->room_passcode) {
            echo api_error_response('room_passcode_missing', 'Room passcode is a required parameter and was not provided.');
            return false;
        }
        if (!isset($input->room_id) || !$input->room_id) {
            echo api_error_response('room_id_missing', 'Room id is a required parameter and was not provided.');
            return false;
        }
        // Check if already joined
        $is_member = $this->room_model->get_room_memeber($user['id'], $input->room_id);
        if ($is_member) {
            echo api_error_response('already_member', 'You have already joined this crew.');
            return false;
        }
        // Verify passcode, use hash equals to prevent timing attack
        $room = $this->room_model->get_room_by_id($input->room_id);
        if (!hash_equals($room['room_passcode'], $input->room_passcode)) {
            echo api_error_response('incorrect_room_passcode', 'Room passcode incorrect.');
            return false;
        }

        // Create member record
        $data['member_created'] = $this->room_model->create_room_memeber($user['id'], $input->room_id);
        if (!$data['member_created']) {
            echo api_error_response('already_member', 'You have already joined this crew.');
            return false;
        }

        echo api_response($data);
    }

    public function create()
    {
        // Limiting
        $create_room_requests = $this->main_model->count_requests_by_route($_SERVER['REMOTE_ADDR'], 'room/create', date('Y-m-d H:i:s', time() - CREATE_ROOM_SPAM_LIMIT_LENGTH));
        if ($create_room_requests > CREATE_ROOM_SPAM_LIMIT_AMOUNT) {
            echo api_error_response('too_many_rooms', 'Sorry, but too many rooms have been created from this IP. Please take a break and try again later.');
            return false;
        }

        // Authentication
        $user = $this->user_model->get_this_user();

        // Validate input
        $input = get_json_post(true);
        if (!$user) {
            echo api_error_response('auth_missing', 'Must be logged in.');
            return false;
        }
        if (!isset($input->room_name) || !$input->room_name) {
            echo api_error_response('room_name_missing', 'Room name is a required parameter and was not provided.');
            return false;
        }
        if (!isset($input->is_base)) {
            echo api_error_response('is_base_missing', 'Is base is a required parameter and was not provided.');
            return false;
        }
        if (!isset($input->room_passcode) || ($input->is_base && !$input->room_passcode)) {
            echo api_error_response('room_passcode_missing', 'Room passcode is a required parameter and was not provided.');
            return false;
        }
        $max_length_room_passcode = 40;
        if ($input->is_base && strlen($input->room_passcode) > $max_length_room_passcode) {
            echo api_error_response('room_passcode_too_long', 'Room passcode is too long.');
            return false;
        }
        if (!isset($input->lat)) {
            echo api_error_response('lat_missing', 'lat is a required parameter and was not provided.');
            return false;
        }
        if (!isset($input->lng)) {
            echo api_error_response('lng_missing', 'lng is a required parameter and was not provided.');
            return false;
        }
        $max_length_room_name = 40;
        if (strlen($input->room_name) > $max_length_room_name) {
            echo api_error_response('room_name_too_long', 'Room name must be ' . $max_length_room_name . ' characters or shorter.');
            return false;
        }
        // Lat and Lng must be valid geo locations
        if (!is_numeric($input->lat) || !is_numeric($input->lng)) {
            echo api_error_response('invalid_location', 'Location provided was invalid.');
            return false;
        }
        // Check that no room at that location exists
        $input->lat = number_format($input->lat, 4);
        $input->lng = number_format($input->lng, 4);
        $room_exists_at_location = $this->room_model->get_room_by_location($input->lat, $input->lng);
        if ($room_exists_at_location) {
            echo api_error_response('room_exists_at_location', 'Room at that location already exists.');
            return false;
        }

        // Create room
        $room = array();
        $room['name'] = $input->room_name;
        $room['is_base'] = $input->is_base;
        $room['room_passcode'] = $input->room_passcode;
        $room['lat'] = $input->lat;
        $room['lng'] = $input->lng;
        $room['last_message_time'] = date('Y-m-d H:i:s');
        $room['created'] = date('Y-m-d H:i:s');
        $room['user_key'] = isset($user['id']) ? $user['id'] : null;
        // Insert room
        $room['id'] = $this->room_model->insert_room($room);

        // Creator is always a member
        if ($room['is_base']) {
            $this->room_model->create_room_memeber($user['id'], $room['id']);
        }

        // Respond
        echo api_response($room);
    }

    public function create_pm_room()
    {
        // Authentication
        $user = $this->user_model->get_this_user();

        // Validate input
        $input = get_json_post(true);

        if (!isset($input->receiving_user_key)) {
            echo api_error_response('receiving_user_key_missing', 'Receiving User is a required parameter and was not provided.');
            return false;
        }
        if (!isset($input->sending_username)) {
            echo api_error_response('sending_username_missing', 'Receiving User is a required parameter and was not provided.');
            return false;
        }
        if (!isset($input->receiving_username)) {
            echo api_error_response('receiving_username_missing', 'Receiving User is a required parameter and was not provided.');
            return false;
        }

        $room = $this->room_model->get_pm_room_by_user_keys($user['id'], $input->receiving_user_key);
        if ($room) {
            echo api_response($room);
            return;
        }

        // Create room
        $room = array();
        $room['name'] = $input->sending_username . '|' . $input->receiving_username;
        $room['lat'] = number_format(0, 4);
        $room['lng'] = number_format(0, 4);
        $room['last_message_time'] = date('Y-m-d H:i:s');
        $room['created'] = date('Y-m-d H:i:s');
        $room['is_pm'] = 1;
        $room['user_key'] = isset($user['id']) ? $user['id'] : null;
        $room['user_unread'] = 0;
        $room['receiving_user_unread'] = 1;
        $room['receiving_user_key'] = $input->receiving_user_key;
        // Insert room
        $room['id'] = $this->room_model->insert_room($room);

        // Respond
        echo api_response($room);
    }

    public function favorite()
    {
        // Authentication
        $user = $this->user_model->get_this_user();
        if (!$user) {
            echo api_error_response('not_logged_in', 'You must be logged in to favorite a room');
            return false;
        }

        // Validate input
        $input = get_json_post(true);
        if (!isset($input->room_id)) {
            echo api_error_response('room_id_missing', 'Room id is a required parameter and was not provided.');
            return false;
        }

        // Check if room is already a favorite
        $get_favorite = $this->room_model->get_favorite_room($user['id'], $input->room_id);

        // If current favorite, delete favorite
        if ($get_favorite) {
            $this->room_model->delete_favorite_room($user['id'], $input->room_id);
        }
        // If not current favorite, insert favorite
        else {
            $this->room_model->create_favorite_room($user['id'], $input->room_id);
        }

        // Respond
        echo api_response(array());
    }
}