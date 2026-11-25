<?php
class ControllerExtensionModulePopularSearch extends Controller {
	public function index($setting) {
		if (empty($setting['status'])) {
			return '';
		}

		$this->load->language('extension/module/popular_search');

		$data['heading'] = $this->language->get('text_heading');
		$data['keywords'] = array();

		if (!empty($setting['keywords']) && is_array($setting['keywords'])) {
			foreach ($setting['keywords'] as $item) {
				$title = isset($item['title']) ? trim($item['title']) : '';
				$keyword = isset($item['keyword']) ? trim($item['keyword']) : '';

				if ($title === '' || $keyword === '') {
					continue;
				}

				$data['keywords'][] = array(
					'title' => $title,
					'href'  => $this->url->link('product/search', 'search=' . rawurlencode($keyword))
				);
			}
		}

		if (!$data['keywords']) {
			return '';
		}

		return $this->load->view('extension/module/popular_search', $data);
	}
}
