<?php
class ControllerProductLatest extends Controller {
	public function index() {
		$this->load->language('product/latest');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');

		$site_name = $this->config->get('config_name');
		$seo_replace = array('site_name' => $site_name);

		$heading = seo_fill($this->config->get('config_seo_latest_list_heading'), $seo_replace);
		if ($heading === '') {
			$heading = $this->language->get('heading_title');
		}

		$meta_title = seo_fill($this->config->get('config_seo_latest_list_title'), $seo_replace);
		if ($meta_title === '') {
			$meta_title = $heading . '｜' . $site_name;
		}

		$meta_description = seo_fill($this->config->get('config_seo_latest_list_description'), $seo_replace);
		if ($meta_description === '') {
			$meta_description = $heading . '。' . $site_name . '（くらしマート）公式オンラインショップ。';
		}

		$meta_keywords = seo_fill($this->config->get('config_seo_latest_list_keywords'), $seo_replace);
		if ($meta_keywords === '') {
			$meta_keywords = seo_fill($this->config->get('config_seo_common_keywords'), $seo_replace);
		}

		$this->document->setTitle($meta_title);
		$this->document->setDescription($meta_description);
		$this->document->setKeywords($meta_keywords);

		$data['heading_title'] = $heading;

		$this->document->setUrl(($this->request->server['HTTPS'] ? 'https://' : 'http://') . $this->request->server['HTTP_HOST'] . $this->request->server['REQUEST_URI']);
		$this->document->setImage($this->model_tool_image->resize($this->config->get('config_image'), 600, 315));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $heading,
			'href' => $this->url->link('product/latest')
		);

		$data['products'] = array();

		$filter_data = array(
			'sort'  => 'p.date_modified',
			'order' => 'DESC',
			'start' => ($page - 1) * $limit,
			'limit' => $limit
		);

		$product_total = $this->model_catalog_product->getTotalProducts(array());

		$results = $this->model_catalog_product->getProducts($filter_data);

		foreach ($results as $result) {
			$data['products'][] = $this->model_catalog_product->handleSingleProduct($result, $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
		}

		$pagination = new Pagination();
		$pagination->total = $product_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->url->link('product/latest', '&page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

		if ($page == 1) {
		    $this->document->addLink($this->url->link('product/latest'), 'canonical');
		} else {
		    $this->document->addLink($this->url->link('product/latest', 'page='. $page), 'canonical');
		}

		if ($page > 1) {
			$this->document->addLink($this->url->link('product/latest', (($page - 2) ? '&page='. ($page - 1) : '')), 'prev');
		}

		if ($limit && ceil($product_total / $limit) > $page) {
		    $this->document->addLink($this->url->link('product/latest', 'page='. ($page + 1)), 'next');
		}

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('product/latest', $data));
	}
}
