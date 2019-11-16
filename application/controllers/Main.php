<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller {
	public $data = []; // set public data

	function __construct() {
		parent::__construct();

		$this->load->model('M_Main');
	}

	public function index() {
		redirect(base_url('main/sign_in'));
	}

	public function sign_in() {
		// set title
		$this->data['title'] = 'Sign In - User Page';

		// load view
		$this->load->view('usr/sign_in', $this->data);

		Accesscontrol_Helper::Visitor_Counter('Sign In');
	}

	public function sign_in_process() {
		$this->load->helper('security'); // load security

		$clean = $this->security->xss_clean($this->input->post());

		$email = $clean['UsrEmail'];
		$pass = $clean['UsrPassword'];

		$get_rslt_usr = $this->M_Main->get_by_email($email);
		if (!empty($get_rslt_usr)) {
			// jika email ditemukan
			$this->load->library('password'); // load pass hash

			if (!$this->password->validate_password($pass, $get_rslt_usr->UsrPassword)) { // password false
				// pass error
				$this->session->set_flashdata('error', 'account error');
				redirect(base_url('main/sign_in'));
			} else {
				// pass true
				foreach ($get_rslt_usr as $key => $val) {
					$this->session->set_userdata($key, $val);
				}
				$this->session->set_userdata('is_logged_in', TRUE);

				$this->session->set_flashdata('success', 'success login');
				redirect(base_url('dashboard/index'));
			}
		} else {
			// jika email tidak ditemukan
		}
	}

	public function sign_up() {
		// set title
		$this->data['title'] = 'Sign Up - User Page';

		// load view
		$this->load->view('usr/sign_up', $this->data);

		Accesscontrol_Helper::Visitor_Counter('Sign Up');
	}

	public function sign_up_process() {
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$this->load->helper('security');

			$clean = $this->security->xss_clean($this->input->post());

			$get_rslt_usr = $this->M_Main->get_by_firstname($clean['UsrFirstName']);

			if (!isset($get_rslt_usr)) {
				// load lib pass
				$this->load->library('password');
				$pass = $this->password->create_hash($clean['UsrPassword']); // make hash pass

				$name = substr(strtolower(str_replace(' ', '', $clean['UsrFirstName'])), 0, 15); // make user

				$data_usr = [
					'UsrUniqeId' => Accesscontrol_Helper::UniqIdReal(),
					'UsrName' => $name,
					'UsrFirstName' => ucwords($clean['UsrFirstName']),
					'UsrEmail' => $clean['UsrEmail'],
					'UsrPassword' => $pass,
					'UsrCreatedId' => 0,
					'UsrRole' => 'User',
					'UsrStatus' => 'Active',
				];

				// create new data
				$get_rslt_usr = $this->M_Main->add($data_usr);

				if ($get_rslt_usr > 0) {
					// data berhasil
					$this->session->set_flashdata('success', 'Insert data success');
					redirect(base_url('main/sign_up'));
				} else {
					// data gagal
					$this->session->set_flashdata('error', 'Insert data failed');
					redirect(base_url('main/sign_up'));
				}
			} else {
				// send notif to sign up view
				$this->session->set_flashdata('error', 'Insert data failed. account found!');
				redirect(base_url('main/sign_up'));
			}
		} else {
			redirect(base_url('main/sign_up'));
		}
	}

	public function sign_out() {
		foreach ($this->session->userdata as $key => $value) {
			$this->session->unset_userdata($key);
		}

		$this->session->set_flashdata('success', 'Success sign out');
		redirect(base_url('main/sign_in'));
	}
}