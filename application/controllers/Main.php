<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model('main_model', '', TRUE);
        $this->load->model('user_model', '', TRUE);
        $this->load->model('room_model', '', TRUE);

        // Force ssl
        if (!is_dev()) {
            force_ssl();
        }

        $this->main_model->record_request();
    }

    public function index()
    {
        // Authentication
        $data['user'] = $this->user_model->get_this_user();

        // Decide sort
        $data['sort'] = $this->input->get('sort') ? $this->input->get('sort') : 'activity';

        // Load view
        $data = $this->registration_starting_details($data);
        $data['page_title'] = site_name();
        $data['landing'] = true;
        $this->load->view('templates/header', $data);
        $this->load->view('landing', $data);
        $this->load->view('report_bugs', $data);
        $this->load->view('scripts/interface_script', $data);
        $this->load->view('templates/footer', $data);
    }

    public function world()
    {
        // Authentication
        $data['user'] = $this->user_model->get_this_user();
        if (!$data['user']) {
            redirect(base_url(), 'refresh');
        }

        // Get filters
        $data['filters'] = $this->get_filters();

        if ($data['user']) {            
            // Include favorite_roomd rooms
            $data['user']['favorite_rooms'] = $this->room_model->get_favorite_rooms_by_user_key($data['user']['id']);
            
            // Include favorite_roomd rooms
            $data['user']['joined_rooms'] = $this->room_model->get_joined_rooms_by_user_key($data['user']['id']);
        }

        // Get last activity filter if exists
        if ($this->input->get('last_activity') && isset($data['filters'][$this->input->get('last_activity')])) {
            $data['current_last_activity_filter'] = $data['filters'][$this->input->get('last_activity')];
        }
        // Use last activity default first
        else {
            $data['current_last_activity_filter'] = $this->determine_default_activity();
        }

        // Get rooms by last activity
        $data['rooms'] = $this->room_model->get_all_rooms_by_last_activity($data['current_last_activity_filter']['minutes_ago']);

        // Calculate a center and zoom that makes sense per the pins
        $data = $this->get_smart_center_and_zoom($data);

        // A/B testing
        $ab_array = array('', '');
        $data['ab_test'] = $ab_array[array_rand($ab_array)];

        // Registration starting details
        if (!$data['user']) {
            // Random color
            $data['random_color'] = random_hex_color();

            // Guess location
            $data['location_prepopulate'] = $this->guess_location();
        }

        // Validation errors
        $data['validation_errors'] = $this->session->flashdata('validation_errors');
        $data['failed_form'] = $this->session->flashdata('failed_form');
        $data['just_registered'] = $this->session->flashdata('just_registered');

        // Load view
        $data = $this->registration_starting_details($data);
        $data['page_title'] = site_name();
        $data['landing'] = false;
        $this->load->view('templates/header', $data);
        $this->load->view('menus', $data);
        $this->load->view('search', $data);
        $this->load->view('blocks', $data);
        $this->load->view('room', $data);
        $this->load->view('player', $data);
        $this->load->view('login', $data);
        $this->load->view('report_bugs', $data);
        $this->load->view('scripts/map_script', $data);
        $this->load->view('scripts/chat_script', $data);
        $this->load->view('scripts/player_script', $data);
        $this->load->view('scripts/interface_script', $data);
        $this->load->view('templates/footer', $data);
    }

    public function get_smart_center_and_zoom($data)
    {
        $data['smart_lat'] = $data['smart_lng'] = $data['smart_zoom'] = false;
        // Don't bother if user is defining desired lat and lng
        if ($this->input->get('lat') && $this->input->get('lng')) {
            return $data;
        }
        // Don't do unless there's a minimum amount of rooms
        if (count($data['rooms']) >= MIN_ROOMS_FOR_SMART_ZOOM) {
            // Gather some data we will use
            $sum_lat = $sum_lng = 0;
            $max_lat = $min_lat = $max_lng = $min_lng = false;
            foreach ($data['rooms'] as $room) {
                $room['lat'] = (float)$room['lat'];
                $room['lng'] = (float)$room['lng'];
                $sum_lat += $room['lat'];
                $sum_lng += $room['lng'];
                $max_lat = $room['lat'] > $max_lat || !$max_lat ? $room['lat'] : $max_lat;
                $min_lat = $room['lat'] < $min_lat || !$min_lat ? $room['lat'] : $min_lat;
                $max_lng = $room['lng'] > $max_lng || !$max_lng ? $room['lng'] : $max_lng;
                $min_lng = $room['lng'] < $min_lng || !$min_lng ? $room['lng'] : $min_lng;
            }
            // Guess on the zoom
            $data['smart_zoom'] = $this->get_smart_zoom($data, $max_lat, $min_lat, $max_lng, $min_lng);
            // Get the average center
            if ($data['smart_zoom']) {
                $data['smart_lat'] = $sum_lat / count($data['rooms']);
                $data['smart_lng'] = $sum_lng / count($data['rooms']);
            }
        }
        return $data;
    }

    public function get_smart_zoom($data, $max_lat, $min_lat, $max_lng, $min_lng)
    {
        $lat_diff = $max_lat - $min_lat;
        $lng_diff = $max_lng - $min_lng;
        // Will need this as I get more examples
        if ($this->input->get('debug')) {
            echo $lat_diff;
            echo ' ';
            echo $lng_diff;
        }
        if ($lat_diff < 0.05 && $lng_diff < 0.05) {
            return 13;
        }
        if ($lat_diff < 0.1 && $lng_diff < 0.1) {
            return 12;
        }
        if ($lat_diff < 0.4 && $lng_diff < 0.4) {
            return 11;
        }
        if ($lat_diff < 0.7 && $lng_diff < 0.7) {
            return 10;
        }
        if ($lat_diff < 1 && $lng_diff < 1) {
            return 9;
        }
        if ($lat_diff < 3 && $lng_diff < 3) {
            return 8;
        }
        if ($lat_diff < 5 && $lng_diff < 5) {
            return 7;
        }
        if ($lat_diff < 10 && $lng_diff < 10) {
            return 6;
        }
        if ($lat_diff < 15 && $lng_diff < 15) {
            return 5;
        }
        if ($lat_diff < 20 && $lng_diff < 20) {
            return 4;
        }
        if ($lat_diff < 25 && $lng_diff < 25) {
            return 3;
        }
        else {
            return false;
        }
    }

    public function determine_default_activity()
    {
        // return $data['filters'][LAST_ACTIVITY_DEFAULT];
        $recent_rooms = $this->room_model->get_recent_rooms(DEFAULT_NUMBER_OF_ROOMS);
        if (empty($recent_rooms)) {
            return $this->get_filters()['all'];
        }
        $time_needed_to_include = strtotime($recent_rooms[0]['last_message_time']);
        $minutes_ago = (time() - $time_needed_to_include) / 60;
        foreach (array_reverse($this->get_filters()) as $filter) {
            if ($filter['minutes_ago'] > $minutes_ago) {
                return $filter;
            }
        }
        return $this->filters()['all'];
    }

    public function registration_starting_details($data)
    {
        // Registration starting details
        if (!$data['user']) {
            // Random color
            $data['random_color'] = random_hex_color();

            // Guess location
            $data['location_prepopulate'] = $this->guess_location();
        }

        // Validation errors
        $data['validation_errors'] = $this->session->flashdata('validation_errors');
        $data['failed_form'] = $this->session->flashdata('failed_form');
        $data['just_registered'] = $this->session->flashdata('just_registered');

        return $data;
    }

    public function load_map_rooms()
    {
        // Get filters
        $data['filters'] = $this->get_filters();

        // Use last activity default first
        $data['current_last_activity_filter'] = $data['filters'][LAST_ACTIVITY_DEFAULT];

        // Get last activity filter if exists
        if ($this->input->get('last_activity') && isset($data['filters'][$this->input->get('last_activity')])) {
            $data['current_last_activity_filter'] = $data['filters'][$this->input->get('last_activity')];
        }

        // Get rooms by last activity
        $data['rooms'] = $this->room_model->get_all_rooms_by_last_activity($data['current_last_activity_filter']['minutes_ago']);

        // Return rooms
        echo api_response($data['rooms']);
    }

    public function guess_location()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $api_response = @file_get_contents("http://ipinfo.io/{$ip}");
        if (!$api_response) {
            return '';
        }
        $location_guess = json_decode($api_response);
        $location_prepopulate = '';
        if (isset($location_guess->region) && $location_guess->region && isset($location_guess->country) && $location_guess->country) {
            $location_prepopulate = $location_guess->region . ', ' . $location_guess->country;
        }
        return $location_prepopulate;
    }

    public function load_user()
    {
        // Authentication
        $data = $this->user_model->get_this_user();
        if ($data) {
            $data['favorite_rooms'] = $this->room_model->get_favorite_rooms_by_user_key($data['id']);
        }
        // htmlspecialchars is used inside api_response
        echo api_response($data);
    }

    public function get_filters()
    {
        $filters = array();
        $filters['all'] = array(
            'slug' => 'all',
            'minutes_ago' => 5 * 365 * 24 * 60,
        );
        $filters['this_month'] = array(
            'slug' => 'this_month',
            'minutes_ago' => 4 * 7 * 24 * 60,
        );
        $filters['this_week'] = array(
            'slug' => 'this_week',
            'minutes_ago' => 7 * 24 * 60,
        );
        $filters['today'] = array(
            'slug' => 'today',
            'minutes_ago' => 1 * 24 * 60,
        );
        $filters['this_hour'] = array(
            'slug' => 'this_hour',
            'minutes_ago' => 1 * 60,
        );
        $filters['now'] = array(
            'slug' => 'now',
            'minutes_ago' => 20,
        );
        return $filters;
    }
}
