<?php
class ControllerCollectCategory extends Controller {
	public function index() {
		$this->load->language('collect/collect');
		$this->load->model('setting/module');
		$this->load->model('tool/image');
		$this->load->model('catalog/product');

		$module_id = isset($this->request->get['module_id']) ? (int)$this->request->get['module_id'] : 0;
		$category_id = isset($this->request->get['category_id']) ? (int)$this->request->get['category_id'] : 0;

		$setting = $this->getCollectSetting($module_id);

		if (!$setting) {
			$this->response->redirect($this->url->link('common/home'));

			return;
		}

		$module_id = isset($setting['module_id']) ? (int)$setting['module_id'] : $module_id;

		$category_info = array();
		$categories = (isset($setting['categories']) && is_array($setting['categories'])) ? array_values($setting['categories']) : array();

		foreach ($categories as $category) {
			if (isset($category['category_id']) && (int)$category['category_id'] === $category_id) {
				$category_info = $category;
				break;
			}
		}

		if (!$category_info) {
			$this->response->redirect($this->url->link('collect/index', 'module_id=' . $module_id));

			return;
		}

		$name = isset($category_info['name']) ? $category_info['name'] : '';

		// SEO
		$title = !empty($category_info['meta_title']) ? $category_info['meta_title'] : $name;

		$this->document->setTitle($title);
		$this->document->setDescription(!empty($category_info['meta_description']) ? $category_info['meta_description'] : (isset($category_info['description']) ? strip_tags($category_info['description']) : ''));
		$this->document->setKeywords(!empty($category_info['meta_keywords']) ? $category_info['meta_keywords'] : '');

		$list_title = $this->language->get('heading_title');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $list_title,
			'href' => $this->url->link('collect/index', 'module_id=' . $module_id)
		);

		$data['breadcrumbs'][] = array(
			'text' => $name,
			'href' => $this->url->link('collect/category', 'module_id=' . $module_id . '&category_id=' . $category_id)
		);

		$data['heading_title'] = $name;
		$data['description'] = isset($category_info['description']) ? $category_info['description'] : '';
		$data['image'] = !empty($category_info['image_detail']) ? $this->model_tool_image->resize($category_info['image_detail'], 1200, 600) : '';
		$data['back'] = $this->url->link('collect/index', 'module_id=' . $module_id);

		$image_width = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width');
		$image_height = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height');

		if (!$image_width) {
			$image_width = 300;
		}

		if (!$image_height) {
			$image_height = 300;
		}

		$data['products'] = array();

		if (!empty($category_info['products']) && is_array($category_info['products'])) {
			foreach ($category_info['products'] as $product_id) {
				$product_info = $this->model_catalog_product->getProduct((int)$product_id);

				if (!$product_info) {
					continue;
				}

				$image = $product_info['image'] ? $this->model_tool_image->resize($product_info['image'], $image_width, $image_height) : $this->model_tool_image->resize('placeholder.png', $image_width, $image_height);

				$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);

				$data['products'][] = array(
					'product_id' => $product_info['product_id'],
					'name'       => $product_info['name'],
					'thumb'      => $image,
					'price'      => $price,
					'href'       => $this->url->link('product/product', 'product_id=' . $product_info['product_id'])
				);
			}
		}

		$data['header'] = $this->load->controller('common/header');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('collect/category', $data));
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
