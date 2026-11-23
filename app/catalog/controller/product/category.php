<?php
class ControllerProductCategory extends Controller {
	public function index() {
		$this->load->language('product/category');

		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		if (isset($this->request->get['filter'])) {
			$filter = $this->request->get['filter'];
		} else {
			$filter = '';
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
			$limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_category_all'),
			'href' => $this->url->link('product/category_list')
		);

		if (isset($this->request->get['path'])) {
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

			$path = '';

			$parts = explode('_', (string)$this->request->get['path']);

			$category_id = (int)array_pop($parts);

			foreach ($parts as $path_id) {
				if (!$path) {
					$path = (int)$path_id;
				} else {
					$path .= '_' . (int)$path_id;
				}

				$category_info = $this->model_catalog_category->getCategory($path_id);

				if ($category_info) {
					$data['breadcrumbs'][] = array(
						'text' => $category_info['name'],
						'href' => $this->url->link('product/category', 'path=' . $path . $url)
					);
				}
			}
		} else {
			$category_id = 0;
		}

		$category_info = $this->model_catalog_category->getCategory($category_id);

		if ($category_info) {
			$site_name = $this->config->get('config_name');
			$category_name = html_entity_decode($category_info['name'], ENT_QUOTES, 'UTF-8');

			$seo_replace = array(
				'name'      => $category_name,
				'site_name' => $site_name
			);

			// 标题：后台未填写或仅重复分类名时，使用后台配置的标题模板
			$meta_title = trim(html_entity_decode($category_info['meta_title'], ENT_QUOTES, 'UTF-8'));

			if ($meta_title === '' || $meta_title === $category_name) {
				$meta_title = seo_fill(seo_config('config_seo_category_title'), $seo_replace);

				if ($meta_title === '') {
					$meta_title = $category_name . '｜' . $site_name;
				}
			} elseif (mb_strpos($meta_title, $site_name) === false) {
				$meta_title .= '｜' . $site_name;
			}

			// 描述：后台未填写时，从分类说明自动摘要 + 后台配置的补充文案
			$meta_description = trim(html_entity_decode($category_info['meta_description'], ENT_QUOTES, 'UTF-8'));

			if ($meta_description === '') {
				$plain_description = trim(preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8'))));

				if ($plain_description !== '') {
					$meta_description = rtrim(utf8_substr($plain_description, 0, 110), '。.!！') . '。';
				} else {
					$meta_description = $category_name . 'をはじめ、収納・キッチン・インテリアなどの日用品を豊富にラインナップ。';
				}

				$meta_description .= seo_fill(seo_config('config_seo_category_description'), $seo_replace);
			}

			// 关键词：后台未填写时，由分类名与后台配置的关键词组合
			$meta_keywords = trim(html_entity_decode($category_info['meta_keyword'], ENT_QUOTES, 'UTF-8'));

			if ($meta_keywords === '') {
				$keyword_terms = array(
					$category_name,
					seo_fill(seo_config('config_seo_category_keywords'), $seo_replace),
					seo_fill(seo_config('config_seo_common_keywords'), $seo_replace)
				);

				$meta_keywords = implode(',', array_unique(array_filter(array_map('trim', explode(',', implode(',', $keyword_terms))))));
			}

			$this->document->setTitle($meta_title);
			$this->document->setDescription($meta_description);
			$this->document->setKeywords($meta_keywords);
			$this->document->setImage($category_info['image']);

			if($category_info['image']){
				$share_image = $this->model_tool_image->resize($category_info['image'], 600, 315);
			} else {
				$share_image = $this->model_tool_image->resize($this->config->get('config_image'), 600, 315);
			}
			$this->document->setImage($share_image);

			$data['heading_title'] = $category_info['name'];

			$data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));

			// Set the last category breadcrumb
			$data['breadcrumbs'][] = array(
				'text' => $category_info['name'],
				'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'])
			);

			$data['thumb'] = $this->model_tool_image->resize($category_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height'), false);

			$data['description'] = html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8');
			$data['compare'] = $this->url->link('product/compare');

			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['categories'] = array();

			$results = $this->model_catalog_category->getCategories($category_id);

			foreach ($results as $result) {
				$filter_data = array(
					'filter_category_id'  => $result['category_id'],
					'filter_sub_category' => true
				);

				$data['categories'][] = array(
					'name' => $result['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
					'thumb' => $this->model_tool_image->resize($result['image']),
					'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '_' . $result['category_id'] . $url)
				);
			}

			$data['products'] = array();

			$filter_data = array(
				'filter_category_id'  => $category_id,
				'filter_sub_category' => $this->config->get('config_product_category') ? true : false,
				'filter_filter'       => $filter,
				'sort'                => $sort,
				'order'               => $order,
				'start'               => ($page - 1) * $limit,
				'limit'               => $limit
			);

			$product_total = $this->model_catalog_product->getTotalProducts($filter_data);

			$results = $this->model_catalog_product->getProducts($filter_data);

			foreach ($results as $result) {
				$href = $this->url->link('product/product', 'path=' . $this->request->get['path'] . '&product_id=' . $result['product_id'] . $url);
				$data['products'][] = $this->model_catalog_product->handleSingleProduct($result, $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'), $href);
			}

			$category_url = $this->url->link('product/category', 'path=' . $this->request->get['path'] . ($page > 1 ? '&page=' . $page : ''));

			// 结构化数据：CollectionPage
			$this->document->addStructuredData(array(
				'@context'    => 'https://schema.org',
				'@type'       => 'CollectionPage',
				'name'        => $meta_title,
				'description' => $meta_description,
				'url'         => html_entity_decode($category_url, ENT_QUOTES, 'UTF-8'),
				'inLanguage'  => 'ja-JP',
				'isPartOf'    => array(
					'@type' => 'WebSite',
					'name'  => $site_name,
					'url'   => html_entity_decode($this->config->get('config_url'), ENT_QUOTES, 'UTF-8')
				)
			));

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

			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['sorts'] = array();

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_default'),
				'value' => 'p.sort_order-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.sort_order&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_asc'),
				'value' => 'pd.name-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=pd.name&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_desc'),
				'value' => 'pd.name-DESC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=pd.name&order=DESC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_price_asc'),
				'value' => 'p.price-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.price&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_price_desc'),
				'value' => 'p.price-DESC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.price&order=DESC' . $url)
			);

			if ($this->config->get('config_review_status')) {
				$data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_desc'),
					'value' => 'rating-DESC',
					'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=rating&order=DESC' . $url)
				);

				$data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_asc'),
					'value' => 'rating-ASC',
					'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=rating&order=ASC' . $url)
				);
			}

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_model_asc'),
				'value' => 'p.model-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.model&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_model_desc'),
				'value' => 'p.model-DESC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.model&order=DESC' . $url)
			);

			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

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
					'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url . '&limit=' . $value)
				);
			}

			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

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
			$pagination->url = $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url . '&page={page}');

			$data['pagination'] = $pagination->render();

			$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

			// http://googlewebmastercentral.blogspot.com/2011/09/pagination-with-relnext-and-relprev.html
			if ($page == 1) {
			    $this->document->addLink($this->url->link('product/category', 'path=' . $this->request->get['path']), 'canonical');
			} else {
				$this->document->addLink($this->url->link('product/category', 'path=' . $this->request->get['path'] . '&page='. $page), 'canonical');
			}

			if ($page > 1) {
			    $this->document->addLink($this->url->link('product/category', 'path=' . $this->request->get['path'] . (($page - 2) ? '&page='. ($page - 1) : '')), 'prev');
			}

			if ($limit && ceil($product_total / $limit) > $page) {
			    $this->document->addLink($this->url->link('product/category', 'path=' . $this->request->get['path'] . '&page='. ($page + 1)), 'next');
			}

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

			$data['style_grid'] = $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url . '&style=grid');
			$data['style_list'] = $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url . '&style=list');

			$data['continue'] = $this->url->link('common/home');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('product/category', $data));
		} else {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
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
				'href' => $this->url->link('product/category', $url)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
}
