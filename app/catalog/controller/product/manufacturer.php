<?php
class ControllerProductManufacturer extends Controller {
	public function index() {
		$this->load->language('product/manufacturer');

		$this->load->model('catalog/manufacturer');

		$site_name = $this->config->get('config_name');
		$seo_replace = array('site_name' => $site_name);

		$heading = seo_fill($this->config->get('config_seo_manufacturer_list_heading'), $seo_replace);
		if ($heading === '') {
			$heading = $this->language->get('heading_title');
		}

		$meta_title = seo_fill($this->config->get('config_seo_manufacturer_list_title'), $seo_replace);
		if ($meta_title === '') {
			$meta_title = $heading . '｜' . $site_name;
		}

		$meta_description = seo_fill($this->config->get('config_seo_manufacturer_list_description'), $seo_replace);
		if ($meta_description === '') {
			$meta_description = $heading . '。' . $site_name . '（くらしマート）公式オンラインショップ。';
		}

		$meta_keywords = seo_fill($this->config->get('config_seo_manufacturer_list_keywords'), $seo_replace);
		if ($meta_keywords === '') {
			$meta_keywords = seo_fill(seo_config('config_seo_common_keywords'), $seo_replace);
		}

		$this->document->setTitle($meta_title);
		$this->document->setDescription($meta_description);
		$this->document->setKeywords($meta_keywords);

		// canonical
		$this->document->addLink($this->url->link('product/manufacturer'), 'canonical');

		$data['heading_title'] = $heading;

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_brand'),
			'href' => $this->url->link('product/manufacturer')
		);

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

		$this->load->model('tool/image');
		$data['manufacturers'] = array();

		$results = $this->model_catalog_manufacturer->getManufacturers();

		foreach ($results as $result) {
			$description = trim(preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode((string)$result['description'], ENT_QUOTES, 'UTF-8'))));

			$data['manufacturers'][] = array(
				'name'        => $result['name'],
				'description' => $description,
				'thumb'       => $this->model_tool_image->resize($result['image'], 240, 240),
				'href'        => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $result['manufacturer_id'])
			);
		}

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('product/manufacturer_list', $data));
	}

	public function info() {
		$this->load->language('product/manufacturer');

		$this->load->model('catalog/manufacturer');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		if (isset($this->request->get['manufacturer_id'])) {
			$manufacturer_id = (int)$this->request->get['manufacturer_id'];
		} else {
			$manufacturer_id = 0;
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'p.sort_order';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = (int)$this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_brand'),
			'href' => $this->url->link('product/manufacturer')
		);

		$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($manufacturer_id);

		if ($manufacturer_info) {
			$site_name = $this->config->get('config_name');
			$manufacturer_name = html_entity_decode($manufacturer_info['name'], ENT_QUOTES, 'UTF-8');
			$seo_replace = array(
				'name'      => $manufacturer_name,
				'site_name' => $site_name
			);

			if (!empty($manufacturer_info['title'])) {
				$meta_title = html_entity_decode($manufacturer_info['title'], ENT_QUOTES, 'UTF-8') . '｜' . $site_name;
			} else {
				$meta_title = seo_fill(seo_config('config_seo_manufacturer_title'), $seo_replace);
				if ($meta_title === '') {
					$meta_title = $manufacturer_name . '｜' . $site_name;
				}
			}

			$meta_description = seo_fill(seo_config('config_seo_manufacturer_description'), $seo_replace);
			if ($meta_description === '') {
				$meta_description = $manufacturer_name . 'の商品を豊富にラインナップ。';
			}

			$keyword_terms = array(
				$manufacturer_name,
				seo_fill(seo_config('config_seo_manufacturer_keywords'), $seo_replace),
				seo_fill(seo_config('config_seo_common_keywords'), $seo_replace)
			);
			$meta_keywords = implode(',', array_unique(array_filter(array_map('trim', explode(',', implode(',', $keyword_terms))))));

			$this->document->setTitle($meta_title);
			$this->document->setDescription($meta_description);
			$this->document->setKeywords($meta_keywords);
			$this->document->setImage($manufacturer_info['image']);

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $manufacturer_info['name'],
				'href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url)
			);

			$data['heading_title'] = (!empty($manufacturer_info['title'])) ? $manufacturer_info['title'] : $manufacturer_info['name'];

			$data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));

			$data['compare'] = $this->url->link('product/compare');

			$data['products'] = array();

			$filter_data = array(
				'filter_manufacturer_id' => $manufacturer_id,
				'sort'                   => $sort,
				'order'                  => $order,
				'start'                  => ($page - 1) * $limit,
				'limit'                  => $limit
			);

			$product_total = $this->model_catalog_product->getTotalProducts($filter_data);

			$results = $this->model_catalog_product->getProducts($filter_data);

			foreach ($results as $result) {
				$href = $this->url->link('product/product', 'manufacturer_id=' . $manufacturer_id . '&product_id=' . $result['product_id'] . $url);
				$data['products'][] = $this->model_catalog_product->handleSingleProduct($result, $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'), $href);
			}

			$url = '';

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['sorts'] = array();

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_default'),
				'value' => 'p.sort_order-ASC',
				'href'  => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&sort=p.sort_order&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_asc'),
				'value' => 'pd.name-ASC',
				'href'  => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&sort=pd.name&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_desc'),
				'value' => 'pd.name-DESC',
				'href'  => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&sort=pd.name&order=DESC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_price_asc'),
				'value' => 'p.price-ASC',
				'href'  => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&sort=p.price&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_price_desc'),
				'value' => 'p.price-DESC',
				'href'  => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&sort=p.price&order=DESC' . $url)
			);

			if ($this->config->get('config_review_status')) {
				$data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_desc'),
					'value' => 'rating-DESC',
					'href'  => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&sort=rating&order=DESC' . $url)
				);

				$data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_asc'),
					'value' => 'rating-ASC',
					'href'  => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&sort=rating&order=ASC' . $url)
				);
			}

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_model_asc'),
				'value' => 'p.model-ASC',
				'href'  => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&sort=p.model&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_model_desc'),
				'value' => 'p.model-DESC',
				'href'  => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&sort=p.model&order=DESC' . $url)
			);

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			$data['limits'] = array();

			for ($i = 1; $i <= 5; $i++) {
				$value = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit') * $i;
				$data['limits'][] = array(
					'text'  => $value,
					'value' => $value,
					'href'  => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url . '&limit=' . $value)
				);
			}

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$pagination = new Pagination();
			$pagination->total = $product_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->url = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] .  $url . '&page={page}');

			$data['pagination'] = $pagination->render();

			$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

			// http://googlewebmastercentral.blogspot.com/2011/09/pagination-with-relnext-and-relprev.html
			if ($page == 1) {
			    $this->document->addLink($this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id']), 'canonical');
			} else {
				$this->document->addLink($this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url . '&page='. $page), 'canonical');
			}

			if ($page > 1) {
			    $this->document->addLink($this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url . '&page='. (($page - 2) ? '&page='. ($page - 1) : '')), 'prev');
			}

			if ($limit && ceil($product_total / $limit) > $page) {
			    $this->document->addLink($this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url . '&page='. ($page + 1)), 'next');
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

			// 结构化数据：ItemList（当前页商品）
			$list_items = array();
			$position = 1;
			foreach ($data['products'] as $product) {
				$list_items[] = array(
					'@type'    => 'ListItem',
					'position' => $position++,
					'url'      => html_entity_decode($product['href'], ENT_QUOTES, 'UTF-8'),
					'name'     => html_entity_decode($product['name'], ENT_QUOTES, 'UTF-8')
				);
			}
			$this->document->addStructuredData(array(
				'@context'        => 'https://schema.org',
				'@type'           => 'ItemList',
				'itemListElement' => $list_items
			));

			$data['sort'] = $sort;
			$data['order'] = $order;
			$data['limit'] = $limit;

			if (isset($this->request->get['style'])) {
				if ($this->request->get['style'] == 'list') {
					$data['style'] = 'list';
				} else {
					$data['style'] = 'grid';
				}
			} elseif (isset($this->session->data['display_style'])) {
				$data['style'] = $this->session->data['display_style'];
			} else {
				$data['style'] = 'grid';
			}
			$this->session->data['display_style'] = $data['style'];

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$data['style_grid'] = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url . '&style=grid');
			$data['style_list'] = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url . '&style=list');

			$data['continue'] = $this->url->link('common/home');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('product/manufacturer_info', $data));
		} else {
			$url = '';

			if (isset($this->request->get['manufacturer_id'])) {
				$url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('product/manufacturer/info', $url)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['heading_title'] = $this->language->get('text_error');

			$data['text_error'] = $this->language->get('text_error');

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['header'] = $this->load->controller('common/header');
			$data['footer'] = $this->load->controller('common/footer');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
}
