<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class world_model extends CI_Model
{
    function get_world_by_slug($world_slug)
    {
        return $this->default_world();

        // $this->db->select('*');
        // $this->db->from('world');
        // $this->db->where('slug', $world_slug);
        // $this->db->where('archived', 0);
        // $query = $this->db->get();
        // $result = $query->result_array();

        // if (!isset($result[0])) {
        //     return false;
        // }

        // // Update world last_load
        // $data = array(
        //     'last_load' => date('Y-m-d H:i:s')
        // );
        // $this->db->where('id', $result[0]['id']);
        // $this->db->update('world', $data);

        // // Return world
        // return $result[0];
    }

    function default_world()
    {
        return array(
            'id' => '1',
            'slug' => 'world',
            'user_key' => '1',
            'archived' => '0',
        );
    }

}
?>