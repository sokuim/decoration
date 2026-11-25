<?php
class ControllerExtensionModuleCollect extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/collect');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/module');

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$this->request->post = $this->prepareData($this->request->post);
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('collect', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module'));
		}

		foreach (array('warning', 'name', 'topic_title', 'category_name') as $key) {
			$data['error_' . $key] = isset($this->error[$key]) ? $this->error[$key] : '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')
		);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/collect', 'user_token=' . $this->session->data['user_token'])
			);

			$data['action'] = $this->url->link('extension/module/collect', 'user_token=' . $this->session->data['user_token']);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/collect', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'])
			);

			$data['action'] = $this->url->link('extension/module/collect', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id']);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		$fields = array('name' => '', 'display_name' => 0, 'seo_title' => '', 'seo_description' => '', 'seo_keywords' => '', 'topics' => array(), 'categories' => array(), 'status' => 0);

		foreach ($fields as $field => $default) {
			if (isset($this->request->post[$field])) {
				$data[$field] = $this->request->post[$field];
			} elseif (!empty($module_info) && isset($module_info[$field])) {
				$data[$field] = $module_info[$field];
			} else {
				$data[$field] = $default;
			}
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('tool/image');
		$this->load->model('catalog/product');

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		foreach ($data['categories'] as $key => $category) {
			$data['categories'][$key]['thumb_list'] = !empty($category['image_list']) ? $this->model_tool_image->resize($category['image_list'], 100, 100) : $data['placeholder'];
			$data['categories'][$key]['thumb_detail'] = !empty($category['image_detail']) ? $this->model_tool_image->resize($category['image_detail'], 100, 100) : $data['placeholder'];

			$data['categories'][$key]['product_list'] = array();

			if (!empty($category['products']) && is_array($category['products'])) {
				foreach ($category['products'] as $product_id) {
					$product_info = $this->model_catalog_product->getProduct((int)$product_id);

					if ($product_info) {
						$data['categories'][$key]['product_list'][] = array(
							'product_id' => $product_info['product_id'],
							'name'       => $product_info['name']
						);
					}
				}
			}
		}

		// 按 sort_order 展示主题，并把类别的归属位置同步到排序后的位置
		$sorted_topics = array();

		foreach ($data['topics'] as $old_index => $topic) {
			$sorted_topics[] = array(
				'old_index'  => $old_index,
				'sort_order' => isset($topic['sort_order']) ? (int)$topic['sort_order'] : 0,
				'topic'      => $topic
			);
		}

		usort($sorted_topics, function($a, $b) {
			return $a['sort_order'] - $b['sort_order'];
		});

		$topic_position = array();

		$data['topics'] = array();

		foreach ($sorted_topics as $position => $item) {
			$topic_position[$item['old_index']] = $position;
			$data['topics'][] = $item['topic'];
		}

		foreach ($data['categories'] as $key => $category) {
			$old_topic_index = isset($category['topic_index']) ? (int)$category['topic_index'] : 0;

			$data['categories'][$key]['topic_index'] = isset($topic_position[$old_topic_index]) ? $topic_position[$old_topic_index] : 0;
		}

		// 按主题归组类别（主题下包含多个类别）
		$data['topic_categories'] = array();

		foreach ($data['categories'] as $key => $category) {
			$topic_index = isset($category['topic_index']) ? (int)$category['topic_index'] : 0;

			$data['topic_categories'][$topic_index][] = array(
				'index'    => $key,
				'category' => $data['categories'][$key]
			);
		}

		// 新增主题/类别时使用的初始序号
		$data['initial_topic_row'] = count($data['topics']);
		$data['initial_category_row'] = count($data['categories']);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/collect', $data));
	}

	/**
	 * 为提交的数据补全主题/类别 ID，并清理商品数据
	 */
	private function prepareData($post) {
		$post['topics'] = (isset($post['topics']) && is_array($post['topics'])) ? array_values($post['topics']) : array();
		$post['categories'] = (isset($post['categories']) && is_array($post['categories'])) ? array_values($post['categories']) : array();

		$topic_ids = array();

		foreach ($post['topics'] as $topic) {
			if (isset($topic['topic_id']) && $topic['topic_id'] !== '') {
				$topic_ids[] = (int)$topic['topic_id'];
			}
		}

		$next_topic_id = $topic_ids ? max($topic_ids) + 1 : 1;
		$used_topic_ids = array();

		foreach ($post['topics'] as $key => $topic) {
			$topic_id = isset($topic['topic_id']) ? (int)$topic['topic_id'] : 0;

			if (!$topic_id || isset($used_topic_ids[$topic_id])) {
				$topic_id = $next_topic_id++;
			}

			$used_topic_ids[$topic_id] = true;

			$post['topics'][$key]['topic_id'] = $topic_id;
			$post['topics'][$key]['title'] = isset($topic['title']) ? trim($topic['title']) : '';
			$post['topics'][$key]['sort_order'] = isset($topic['sort_order']) ? (int)$topic['sort_order'] : 0;
			$post['topics'][$key]['status'] = (isset($topic['status']) && !$topic['status']) ? 0 : 1;
		}

		$category_ids = array();

		foreach ($post['categories'] as $category) {
			if (isset($category['category_id']) && $category['category_id'] !== '') {
				$category_ids[] = (int)$category['category_id'];
			}
		}

		$next_category_id = $category_ids ? max($category_ids) + 1 : 1;
		$used_category_ids = array();

		foreach ($post['categories'] as $key => $category) {
			$category_id = isset($category['category_id']) ? (int)$category['category_id'] : 0;

			if (!$category_id || isset($used_category_ids[$category_id])) {
				$category_id = $next_category_id++;
			}

			$used_category_ids[$category_id] = true;

			$products = array();

			if (isset($category['products']) && is_array($category['products'])) {
				foreach ($category['products'] as $product_id) {
					$product_id = (int)$product_id;

					if ($product_id && !in_array($product_id, $products)) {
						$products[] = $product_id;
					}
				}
			}

			$topic_index = isset($category['topic_index']) ? (int)$category['topic_index'] : 0;

			$post['categories'][$key] = array(
				'category_id'      => $category_id,
				'topic_index'      => isset($post['topics'][$topic_index]) ? $topic_index : 0,
				'name'             => isset($category['name']) ? trim($category['name']) : '',
				'description'      => isset($category['description']) ? $category['description'] : '',
				'image_list'       => isset($category['image_list']) ? $category['image_list'] : '',
				'image_detail'     => isset($category['image_detail']) ? $category['image_detail'] : '',
				'meta_title'       => isset($category['meta_title']) ? $category['meta_title'] : '',
				'meta_description' => isset($category['meta_description']) ? $category['meta_description'] : '',
				'meta_keywords'    => isset($category['meta_keywords']) ? $category['meta_keywords'] : '',
				'status'           => !empty($category['status']) ? 1 : 0,
				'sort_order'       => isset($category['sort_order']) ? (int)$category['sort_order'] : 0,
				'products'         => $products
			);
		}

		return $post;
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/collect')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 2) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if (!empty($this->request->post['topics']) && is_array($this->request->post['topics'])) {
			foreach ($this->request->post['topics'] as $topic) {
				if (!isset($topic['title']) || trim($topic['title']) === '') {
					$this->error['topic_title'] = $this->language->get('error_topic_title');
					break;
				}
			}
		}

		if (!empty($this->request->post['categories']) && is_array($this->request->post['categories'])) {
			foreach ($this->request->post['categories'] as $category) {
				if (!isset($category['name']) || trim($category['name']) === '') {
					$this->error['category_name'] = $this->language->get('error_category_name');
					break;
				}
			}
		}

		return !$this->error;
	}
}
