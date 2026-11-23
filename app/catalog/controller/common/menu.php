<?php
class ControllerCommonMenu extends Controller {
	public function index() {
		$this->load->language('common/menu');

		if ($this->config->get('config_welcome_text')) {
			$data['text_welcome'] = $this->config->get('config_welcome_text');
		}

		$data['home'] = $this->url->link('common/home');
		$data['logo'] = is_file(DIR_IMAGE . $this->config->get('config_logo')) ? $this->config->get('config_url') . 'image/' . $this->config->get('config_logo') : '';

		$data['nav_links'] = $this->config->get('config_nav_links');
		if (!is_array($data['nav_links'])) {
			$data['nav_links'] = array();
		}

		$data['drawer_menu'] = $this->config->get('config_drawer_menu');
		if (!is_array($data['drawer_menu'])) {
			$data['drawer_menu'] = array();
		}

		return $this->load->view('common/menu', $data);
	}
}
