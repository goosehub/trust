<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class user_model extends CI_Model
{
    public $user_select_list = '
    id, 
    username, 
    color, 
    location,
    room_key,
    last_load,
    cash,
    cash_received,
    time_served_minutes,
    good_reputation,
    bad_reputation,
    (good_reputation - bad_reputation) as net_reputation,
    (good_reputation + bad_reputation) as sum_reputation,
    jobs_led,
    jobs_success,
    jobs_failed,
    (jobs_success + jobs_failed) as sum_jobs,
    bails_paid_count,
    bails_paid_amount,
    arrested,
    in_jail_time_minutes,
    fines_paid,
    payouts_stolen,
    crews_joined,
    kills,
    skill_thief,
    skill_muscle,
    skill_driver,
    skill_conman,
    skill_cracker,
    skill_hacker,
    skill_fixer,
    is_dead,
    is_in_jail,
    jail_sentence_end_timestamp,
    created,
    ';

    // Get all users
    function get_all_users()
    {
        $this->db->select($this->user_select_list);
        $this->db->from('user');
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }
    function get_all_users_by_room_key($room_key)
    {
        $this->db->select($this->user_select_list);
        $this->db->from('user');
        $this->db->where('room_key', $room_key);
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }
    function get_this_user()
    {
        // Default to user as false
        $user = false;

        // Get user by session
        if ($this->session->userdata('user_session')) {
            $session_data = $this->session->userdata('user_session');
            $user = $this->user_model->get_user_by_id($session_data['id']);
            if (!isset($user['username'])) {
                redirect('user/logout', 'refresh');
                exit();
                return false;
            }
            // Disabled for performance concerns
            // $this->user_loaded($user['id']);
        }

        // Get user by api key
        else if ($this->input->get('api')) {
            $input = get_json_post(false);
            if (isset($input->user_id) && isset($input->api_key)) {
                $user_auth = $this->user_model->get_user_auth_by_id($input->user_id);
                if (!isset($user_auth['api_key']) || !hash_equals($user_auth['api_key'], $input->api_key)) {
                    $this->output->set_status_header(401);
                    echo api_error_response('bad_auth', 'Your user_id, api_key combination was incorrect');
                    exit();
                }
                $user = $this->get_user_by_id($user_auth['id']);
                // Disabled for performance concerns
                // $this->user_loaded($user['id']);
            }
        }

        // Return user
        return $user;
    }
    function get_user_by_id($user_id)
    {
        $this->db->select($this->user_select_list);
        $this->db->from('user');
        $this->db->where('id', $user_id);
        $this->db->limit(1);
        $query = $this->db->get();
        $result = $query->result_array();
        return isset($result[0]) ? $result[0] : false;
    }
    function get_user_auth_by_id($user_id)
    {
        $this->db->select('id, username, password, api_key');
        $this->db->from('user');
        $this->db->where('id', $user_id);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            $result = $query->result_array();
            return isset($result[0]) ? $result[0] : false;
        }
        else {
            return false;
        }
    }
    function get_user_auth_by_username($username)
    {
        $this->db->select('id, username, password, api_key');
        $this->db->from('user');
        $this->db->where('username', $username);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            $result = $query->result_array();
            return isset($result[0]) ? $result[0] : false;
        }
        else {
            return false;
        }
    }
    function register($username, $password, $api_key, $email, $ip, $register_ip_frequency_limit_minutes, $ab_test, $color, $location)
    {
        // Check for excessive IPs registers
        $this->db->select('id');
        $this->db->from('user');
        $this->db->where('ip', $ip);
        $this->db->where('created > NOW() - INTERVAL ' . $register_ip_frequency_limit_minutes . ' MINUTE');
        $this->db->limit(1);
        $query = $this->db->get();

        // Failed register ip frequency limit
        if (!is_dev() && $query->num_rows() > 0) {
            return 'ip_fail';
        }

        // Check for existing username
        $this->db->select('username');
        $this->db->from('user');
        $this->db->where('username', $username);
        $this->db->limit(1);
        $query = $this->db->get();

        // Username already exists
        if ($query->num_rows() > 0) {
            return false;
        }
        // Register
        else {
            // Insert user into user
            $data = array(
            'username' => $username,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'room_key' => null,
            'api_key' => $api_key,
            'email' => $email,
            'ip' => $ip,
            'ab_test' => $ab_test,
            'color' => $color,
            'location' => $location,
            );
            $this->db->insert('user', $data);

            // Return user id
            $this->db->select_max('id');
            $this->db->from('user');
            $this->db->limit(1);
            $query = $this->db->get()->row();
            $user_id = $query->id;
            return $user_id;
        }
    }
    function user_loaded($user_id)
    {
        $data = array(
            'last_load' => date('Y-m-d H:i:s')
        );
        $this->db->where('id', $user_id);
        $this->db->update('user', $data);
        return true;
    }
    function update_color($user_id, $color)
    {
        $data = array(
            'color' => $color
        );
        $this->db->where('id', $user_id);
        $this->db->update('user', $data);
        return true;
    }
    function update_location($user_id, $location)
    {
        $data = array(
            'location' => $location
        );
        $this->db->where('id', $user_id);
        $this->db->update('user', $data);
        return true;
    }
    function update_room($user_id, $room_key)
    {
        $data = array(
            'room_key' => $room_key
        );
        $this->db->where('id', $user_id);
        $this->db->update('user', $data);
        return true;
    }

}
?>