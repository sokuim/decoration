<?php
class ControllerExtensionModuleCollect extends Controller {
	public function index($setting) {
		if (empty($setting['status'])) {
			return '';
		}

		$this->load->language('extension/module/collect');
		$this->load->model('tool/image');

		$module_id = isset($setting['module_id']) ? (int)$setting['module_id'] : 0;

		$data['heading'] = !empty($setting['display_name']) ? (isset($setting['name']) ? $setting['name'] : '') : '';
		$data['list_url'] = $this->url->link('collect/index', 'module_id=' . $module_id);
		$data['topics'] = array();

		$topics = (isset($setting['topics']) && is_array($setting['topics'])) ? array_values($setting['topics']) : array();
		$categories = (isset($setting['categories']) && is_array($setting['categories'])) ? array_values($setting['categories']) : array();

		$grouped = array();

		foreach ($topics as $index => $topic) {
			// 仅显示设置为「在模块中显示」的主题
			if (isset($topic['status']) && !$topic['status']) {
				continue;
			}

			$grouped[$index] = array(
				'title'      => isset($topic['title']) ? $topic['title'] : '',
				'sort_order' => isset($topic['sort_order']) ? (int)$topic['sort_order'] : 0,
				'categories' => array()
			);
		}

		foreach ($categories as $category) {
			// 仅显示设置为「在模块中显示」的类别
			if (empty($category['status'])) {
				continue;
			}

			$topic_index = isset($category['topic_index']) ? (int)$category['topic_index'] : 0;

			if (!isset($grouped[$topic_index])) {
				continue;
			}

			$image = '';

			if (!empty($category['image_list'])) {
				$image = $this->model_tool_image->resize($category['image_list'], 480, 480);
			} else {
				$image = $this->model_tool_image->resize('placeholder.png', 480, 480);
			}

			$grouped[$topic_index]['categories'][] = array(
				'category_id' => isset($category['category_id']) ? (int)$category['category_id'] : 0,
				'name'        => isset($category['name']) ? $category['name'] : '',
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

			// 模块中每个主题最多显示 4 张类别图片
			$grouped[$index]['categories'] = array_slice($categories_sorted, 0, 4);
			$data['topics'][] = $grouped[$index];
		}

		if (!$data['topics']) {
			return '';
		}

		usort($data['topics'], function($a, $b) {
			return $a['sort_order'] - $b['sort_order'];
		});

		return $this->load->view('extension/module/collect', $data);
	}
}
