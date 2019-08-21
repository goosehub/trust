<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class World extends CI_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model('main_model', '', TRUE);
        $this->load->model('user_model', '', TRUE);
        $this->load->model('world_model', '', TRUE);

        $this->main_model->record_request();
    }
}