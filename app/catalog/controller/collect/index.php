<?php
class ControllerCollectIndex extends Controller {
	public function index() {
		$this->load->language('collect/collect');
		$this->load->model('setting/module');
		$this->load->model('tool/image');

		$module_id = isset($this->request->get['module_id']) ? (int)$this->request->get['module_id'] : 0;

		$setting = $this->getCollectSetting($module_id);

		if (!$setting) {
			$this->response->redirect($this->url->link('common/home'));

			return;
		}

		$module_id = isset($setting['module_id']) ? (int)$setting['module_id'] : $module_id;

		// SEO
		$page_title = $this->language->get('heading_title');
		$title = !empty($setting['seo_title']) ? $setting['seo_title'] : $page_title;

		$this->document->setTitle($title);
		$this->document->setDescription(!empty($setting['seo_description']) ? $setting['seo_description'] : '');
		$this->document->setKeywords(!empty($setting['seo_keywords']) ? $setting['seo_keywords'] : '');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $page_title,
			'href' => $this->url->link('collect/index', 'module_id=' . $module_id)
		);

		$data['heading_title'] = $page_title;
		$data['topics'] = array();

		$topics = (isset($setting['topics']) && is_array($setting['topics'])) ? array_values($setting['topics']) : array();
		$categories = (isset($setting['categories']) && is_array($setting['categories'])) ? array_values($setting['categories']) : array();

		$grouped = array();

		foreach ($topics as $index => $topic) {
			$grouped[$index] = array(
				'title'      => isset($topic['title']) ? $topic['title'] : '',
				'sort_order' => isset($topic['sort_order']) ? (int)$topic['sort_order'] : 0,
				'categories' => array()
			);
		}

		foreach ($categories as $category) {
			$topic_index = isset($category['topic_index']) ? (int)$category['topic_index'] : 0;

			if (!isset($grouped[$topic_index])) {
				continue;
			}

			$image = !empty($category['image_list']) ? $this->model_tool_image->resize($category['image_list'], 480, 480) : $this->model_tool_image->resize('placeholder.png', 480, 480);

			$grouped[$topic_index]['categories'][] = array(
				'category_id' => isset($category['category_id']) ? (int)$category['category_id'] : 0,
				'name'        => isset($category['name']) ? $category['name'] : '',
				'description' => isset($category['description']) ? $category['description'] : '',
				'image'       => $image,
				'sort_order'  => isset($category['sort_order']) ? (int)$category['sort_order'] : 0,
				'href'        => $this->url->link('collect/category', 'module_id=' . $module_id . '&category_id=' . (isset($category['category_id']) ? (int)$category['category_id'] : 0))
			);
		}

		foreach ($grouped as $index => $topic) {
			if (!$topic['categories']) {
				continue;
			}

			$categories_sorted = $topic['categories'];

			usort($categories_sorted, function($a, $b) {
				return $a['sort_order'] - $b['sort_order'];
			});

			$grouped[$index]['categories'] = $categories_sorted;
			$data['topics'][] = $grouped[$index];
		}

		usort($data['topics'], function($a, $b) {
			return $a['sort_order'] - $b['sort_order'];
		});

		$data['header'] = $this->load->controller('common/header');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('collect/index', $data));
	}

	private function getCollectSetting($module_id) {
		if ($module_id) {
			$setting = $this->model_setting_module->getModule($module_id);

			if ($setting && !empty($setting['status'])) {
				$setting['module_id'] = $module_id;

				return $setting;
			}
		}

		foreach ($this->model_setting_module->getModulesByCode('collect') as $module) {
			$setting = $module['setting'] ? json_decode($module['setting'], true) : array();

			if (!empty($setting['status'])) {
				$setting['module_id'] = (int)$module['module_id'];

				return $setting;
			}
		}

		return array();
	}
}
