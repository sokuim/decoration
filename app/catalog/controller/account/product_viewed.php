<?php

class ControllerAccountProductViewed extends Controller
{
    public function index()
    {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/product_viewed');

            $this->response->redirect($this->url->link('account/login'));
        }

        $this->load->language('account/product_viewed');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_account'),
            'href' => $this->url->link('account/account')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('account/product_viewed')
        );

        $this->load->model('account/product_viewed');
        $this->load->model('catalog/product');

        $ids = $this->model_account_product_viewed->getViewedIds($this->customer->getId(), 30);
        $results = $this->model_account_product_viewed->getProductsByIds($ids);

        $data['products'] = array();

        foreach ($results as $result) {
            $data['products'][] = $this->model_catalog_product->handleSingleProduct($result, 300, 300);
        }

        $data['continue'] = $this->url->link('account/account');

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('account/product_viewed', $data));
    }
}
