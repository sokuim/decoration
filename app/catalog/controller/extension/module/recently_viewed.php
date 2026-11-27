<?php

class ControllerExtensionModuleRecentlyViewed extends Controller
{
    public function index($setting)
    {
        static $module = 0;

        $this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');
        $this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.min.js');

        $this->load->model('account/product_viewed');

        if ($this->customer->isLogged()) {
            $ids = $this->model_account_product_viewed->getViewedIds($this->customer->getId(), 30);
        } else {
            $ids = recently_viewed_get_ids();
        }

        if (!$ids) {
            return '';
        }

        // 商品详情页不显示当前正在浏览的商品
        if (isset($this->request->get['route']) && $this->request->get['route'] == 'product/product' && isset($this->request->get['product_id'])) {
            $ids = array_values(array_diff($ids, array((int)$this->request->get['product_id'])));

            if (!$ids) {
                return '';
            }
        }

        $this->load->language('extension/module/recently_viewed');
        $this->load->model('catalog/product');

        // 最多取 30 条，前台一次显示 5 个，其余通过左右箭头滚动
        $results = $this->model_account_product_viewed->getProductsByIds($ids);

        if (!$results) {
            return '';
        }

        $heading = isset($setting['heading']) ? html_entity_decode((string)$setting['heading'], ENT_QUOTES, 'UTF-8') : '';

        if (trim($heading) === '') {
            $heading = $this->language->get('heading_title');
        }

        // 缩略图尺寸固定，实际布局由 CSS 5 列网格决定
        $width = 300;
        $height = 300;

        // 已登录读取收藏商品；游客读取会话中的收藏，用于区分实心/空心
        $wishlist_ids = array();

        if ($this->customer->isLogged()) {
            $this->load->model('account/wishlist');

            foreach ($this->model_account_wishlist->getWishlist() as $wishlist) {
                $wishlist_ids[] = (int)$wishlist['product_id'];
            }
        } elseif (isset($this->session->data['wishlist']) && is_array($this->session->data['wishlist'])) {
            $wishlist_ids = array_map('intval', $this->session->data['wishlist']);
        }

        $data['heading_title'] = $heading;
        $data['module'] = $module++;
        $data['products'] = array();

        foreach ($results as $result) {
            $product = $this->model_catalog_product->handleSingleProduct($result, $width, $height);
            $product['in_wishlist'] = in_array((int)$result['product_id'], $wishlist_ids, true);

            $data['products'][] = $product;
        }

        return $this->load->view('extension/module/recently_viewed', $data);
    }
}
