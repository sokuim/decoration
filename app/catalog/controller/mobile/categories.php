<?php
class ControllerMobileCategories extends Controller {
	public function index() {
		$this->load->language('mobile/categories');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/category');
		$this->load->model('tool/image');

		$data['categories'] = array();

		$categories = $this->model_catalog_category->getCategories(0);

		foreach ($categories as $category) {
			$data['categories'][] = array(
				'name'  => $category['name'],
				'thumb' => $this->model_tool_image->resize($category['image'], 300, 300),
				'href'  => $this->url->link('product/category', 'path=' . $category['category_id'])
			);
		}

		$data['continue'] = $this->url->link('common/home');

		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('mobile/categories', $data));
	}
}
