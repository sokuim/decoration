<?php
class ControllerProductCategoryList extends Controller {
	public function index() {
		$this->load->language('product/category_list');

		$this->load->model('catalog/category');

		$this->load->model('tool/image');

		$site_name = $this->config->get('config_name');
		$seo_replace = array('site_name' => $site_name);

		$heading = seo_fill($this->config->get('config_seo_category_list_heading'), $seo_replace);
		if ($heading === '') {
			$heading = $this->language->get('heading_title');
		}

		$meta_title = seo_fill($this->config->get('config_seo_category_list_title'), $seo_replace);
		if ($meta_title === '') {
			$meta_title = $heading . '｜' . $site_name;
		}

		$meta_description = seo_fill($this->config->get('config_seo_category_list_description'), $seo_replace);
		if ($meta_description === '') {
			$meta_description = $heading . '。' . $site_name . '（くらしマート）公式オンラインショップ。';
		}

		$meta_keywords = seo_fill($this->config->get('config_seo_category_list_keywords'), $seo_replace);
		if ($meta_keywords === '') {
			$meta_keywords = seo_fill(seo_config('config_seo_common_keywords'), $seo_replace);
		}

		$this->document->setTitle($meta_title);
		$this->document->setDescription($meta_description);
		$this->document->setKeywords($meta_keywords);

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $heading,
			'href' => $this->url->link('product/category_list')
		);

		$data['heading_title'] = $heading;

		$data['categories'] = array();

		$categories = $this->model_catalog_category->getCategories(0);

		foreach ($categories as $category) {
			$children_data = array();

			$children = $this->model_catalog_category->getCategories($category['category_id']);

			foreach ($children as $child) {
				$child_description = $this->getPlainDescription($child['description']);

				$children_data[] = array(
					'name'        => $child['name'],
					'description' => $child_description,
					'thumb'       => $this->model_tool_image->resize($child['image'], 120, 120, false),
					'href'        => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'])
				);
			}

			$data['categories'][] = array(
				'category_id' => $category['category_id'],
				'name'        => $category['name'],
				'description' => $this->getPlainDescription($category['description']),
				'thumb'       => $this->model_tool_image->resize($category['image'], 320, 240, false),
				'thumb_square' => $this->model_tool_image->resize($category['image'], 200, 200, false),
				'href'        => $this->url->link('product/category', 'path=' . $category['category_id']),
				'children'    => $children_data
			);
		}

		// 结构化数据：BreadcrumbList
		$breadcrumb_items = array();
		$position = 1;

		foreach ($data['breadcrumbs'] as $breadcrumb) {
			$breadcrumb_items[] = array(
				'@type'    => 'ListItem',
				'position' => $position++,
				'name'     => html_entity_decode($breadcrumb['text'], ENT_QUOTES, 'UTF-8'),
				'item'     => html_entity_decode($breadcrumb['href'], ENT_QUOTES, 'UTF-8')
			);
		}

		$this->document->addStructuredData(array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $breadcrumb_items
		));

		$this->document->addLink($this->url->link('product/category_list'), 'canonical');

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('product/category_list', $data));
	}

	// 取分类描述的纯文本（完整输出，不截断）
	private function getPlainDescription($description) {
		return trim(preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode((string)$description, ENT_QUOTES, 'UTF-8'))));
	}
}
