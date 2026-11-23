<?php
class ControllerExtensionModuleTopLinks extends Controller {
	public function index($setting) {
		if (empty($setting['status'])) {
			return '';
		}

		$data['links'] = array();

		if (!empty($setting['links']) && is_array($setting['links'])) {
			foreach ($setting['links'] as $link) {
				$text = isset($link['text']) ? trim($link['text']) : '';

				if ($text === '') {
					continue;
				}

				$data['links'][] = array(
					'text' => $text,
					'href' => isset($link['href']) ? $link['href'] : ''
				);
			}
		}

		if (!$data['links']) {
			return '';
		}

		return $this->load->view('extension/module/top_links', $data);
	}
}
