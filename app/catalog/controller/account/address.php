<?php
class ControllerAccountAddress extends Controller {
    private $error = array();

    public function index() {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/address');

            $this->response->redirect($this->url->link('account/login'));
        }

        $this->load->language('account/address');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('account/address');

        $this->getList();
    }

    public function add() {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/address');

            $this->response->redirect($this->url->link('account/login'));
        }

        $this->load->language('account/address');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
        $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
        $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
        $this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

        $this->load->model('account/address');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_account_address->addAddress($this->customer->getId(), $this->request->post);

            $this->session->data['success'] = $this->language->get('text_add');

            $this->response->redirect($this->url->link('account/address'));
        }

        $this->getForm();
    }

    public function edit() {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/address');

            $this->response->redirect($this->url->link('account/login'));
        }

        $this->load->language('account/address');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
        $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
        $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
        $this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

        $this->load->model('account/address');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_account_address->editAddress($this->request->get['address_id'], $this->request->post);

            // Default Shipping Address
            if (isset($this->session->data['shipping_address']['address_id']) && ($this->request->get['address_id'] == $this->session->data['shipping_address']['address_id'])) {
                $this->session->data['shipping_address'] = $this->model_account_address->getAddress($this->request->get['address_id']);

                unset($this->session->data['shipping_method']);
                unset($this->session->data['shipping_methods']);
            }

            $this->session->data['success'] = $this->language->get('text_edit');

            $this->response->redirect($this->url->link('account/address'));
        }

        $this->getForm();
    }

    public function delete() {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/address');

            $this->response->redirect($this->url->link('account/login'));
        }

        $this->load->language('account/address');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('account/address');

        if (isset($this->request->get['address_id']) && $this->validateDelete()) {
            $this->model_account_address->deleteAddress($this->request->get['address_id']);

            // Default Shipping Address
            if (isset($this->session->data['shipping_address']['address_id']) && ($this->request->get['address_id'] == $this->session->data['shipping_address']['address_id'])) {
                unset($this->session->data['shipping_address']);
                unset($this->session->data['shipping_method']);
                unset($this->session->data['shipping_methods']);
            }

            $this->session->data['success'] = $this->language->get('text_delete');

            $this->response->redirect($this->url->link('account/address'));
        }

        $this->getList();
    }

    protected function getList() {
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
            'href' => $this->url->link('account/address')
        );

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $data['addresses'] = array();

        $results = $this->model_account_address->getAddresses();

        foreach ($results as $result) {
            $data['addresses'][] = array(
                'address_id'     => $result['address_id'],
                'lastname'       => $result['lastname'],
                'firstname'      => $result['firstname'],
                'lastname_kana'  => $result['lastname_kana'],
                'firstname_kana' => $result['firstname_kana'],
                'telephone'      => get_telephone($result['telephone_1'], $result['telephone_2'], $result['telephone_3']),
                'postcode'       => format_postcode($result['postcode']),
                'address'        => address_format_address($result),
                'update'         => $this->url->link('account/address/edit', 'address_id=' . $result['address_id']),
                'delete'         => $this->url->link('account/address/delete', 'address_id=' . $result['address_id'])
            );
        }

        $data['add'] = $this->url->link('account/address/add');
        $data['back'] = $this->url->link('account/account');

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('account/address_list', $data));
    }

    protected function getForm() {
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
            'href' => $this->url->link('account/address')
        );

        if (!isset($this->request->get['address_id'])) {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_address_add'),
                'href' => $this->url->link('account/address/add')
            );
        } else {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_address_edit'),
                'href' => $this->url->link('account/address/edit', 'address_id=' . $this->request->get['address_id'])
            );
        }

        $data['text_address'] = !isset($this->request->get['address_id']) ? $this->language->get('text_address_add') : $this->language->get('text_address_edit');

        if (isset($this->error['firstname'])) {
            $data['error_firstname'] = $this->error['firstname'];
        } else {
            $data['error_firstname'] = '';
        }

        if (isset($this->error['lastname'])) {
            $data['error_lastname'] = $this->error['lastname'];
        } else {
            $data['error_lastname'] = '';
        }

        if (isset($this->error['firstname_kana'])) {
            $data['error_firstname_kana'] = $this->error['firstname_kana'];
        } else {
            $data['error_firstname_kana'] = '';
        }

        if (isset($this->error['lastname_kana'])) {
            $data['error_lastname_kana'] = $this->error['lastname_kana'];
        } else {
            $data['error_lastname_kana'] = '';
        }

        if (isset($this->error['telephone'])) {
            $data['error_telephone'] = $this->error['telephone'];
        } else {
            $data['error_telephone'] = '';
        }

        if (isset($this->error['address_1'])) {
            $data['error_address_1'] = $this->error['address_1'];
        } else {
            $data['error_address_1'] = '';
        }

        if (isset($this->error['city'])) {
            $data['error_city'] = $this->error['city'];
        } else {
            $data['error_city'] = '';
        }

        if (isset($this->error['county_id'])) {
            $data['error_county_id'] = $this->error['county_id'];
        } else {
            $data['error_county_id'] = '';
        }

        if (isset($this->error['postcode'])) {
            $data['error_postcode'] = $this->error['postcode'];
        } else {
            $data['error_postcode'] = '';
        }

        if (isset($this->error['country'])) {
            $data['error_country'] = $this->error['country'];
        } else {
            $data['error_country'] = '';
        }

        if (isset($this->error['zone'])) {
            $data['error_zone'] = $this->error['zone'];
        } else {
            $data['error_zone'] = '';
        }

        if (isset($this->error['custom_field'])) {
            $data['error_custom_field'] = $this->error['custom_field'];
        } else {
            $data['error_custom_field'] = array();
        }

        if (!isset($this->request->get['address_id'])) {
            $data['action'] = $this->url->link('account/address/add');
        } else {
            $data['action'] = $this->url->link('account/address/edit', 'address_id=' . $this->request->get['address_id']);
        }

        if (isset($this->request->get['address_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $address_info = $this->model_account_address->getAddress($this->request->get['address_id']);
        }

        if (isset($this->request->post['firstname'])) {
            $data['firstname'] = $this->request->post['firstname'];
        } elseif (!empty($address_info)) {
            $data['firstname'] = $address_info['firstname'];
        } else {
            $data['firstname'] = '';
        }

        if (isset($this->request->post['lastname'])) {
            $data['lastname'] = $this->request->post['lastname'];
        } elseif (!empty($address_info)) {
            $data['lastname'] = $address_info['lastname'];
        } else {
            $data['lastname'] = '';
        }

        if (isset($this->request->post['firstname_kana'])) {
            $data['firstname_kana'] = $this->request->post['firstname_kana'];
        } elseif (!empty($address_info)) {
            $data['firstname_kana'] = $address_info['firstname_kana'];
        } else {
            $data['firstname_kana'] = '';
        }

        if (isset($this->request->post['lastname_kana'])) {
            $data['lastname_kana'] = $this->request->post['lastname_kana'];
        } elseif (!empty($address_info)) {
            $data['lastname_kana'] = $address_info['lastname_kana'];
        } else {
            $data['lastname_kana'] = '';
        }

        if (isset($this->request->post['telephone_1'])) {
            $data['telephone_1'] = $this->request->post['telephone_1'];
        } elseif (!empty($address_info)) {
            $data['telephone_1'] = $address_info['telephone_1'];
        } else {
            $data['telephone_1'] = '';
        }

        if (isset($this->request->post['telephone_2'])) {
            $data['telephone_2'] = $this->request->post['telephone_2'];
        } elseif (!empty($address_info)) {
            $data['telephone_2'] = $address_info['telephone_2'];
        } else {
            $data['telephone_2'] = '';
        }

        if (isset($this->request->post['telephone_3'])) {
            $data['telephone_3'] = $this->request->post['telephone_3'];
        } elseif (!empty($address_info)) {
            $data['telephone_3'] = $address_info['telephone_3'];
        } else {
            $data['telephone_3'] = '';
        }


        if (isset($this->request->post['address_1'])) {
            $data['address_1'] = $this->request->post['address_1'];
        } elseif (!empty($address_info)) {
            $data['address_1'] = $address_info['address_1'];
        } else {
            $data['address_1'] = '';
        }

        if (isset($this->request->post['address_2'])) {
            $data['address_2'] = $this->request->post['address_2'];
        } elseif (!empty($address_info)) {
            $data['address_2'] = $address_info['address_2'];
        } else {
            $data['address_2'] = '';
        }

        if (isset($this->request->post['postcode'])) {
            $data['postcode'] = $this->request->post['postcode'];
        } elseif (!empty($address_info)) {
            $data['postcode'] = $address_info['postcode'];
        } else {
            $data['postcode'] = '';
        }

        // 邮编两段输入：拆分给前端 postcode_1 / postcode_2
        $postcode = preg_replace('/[^0-9]/', '', (string)$data['postcode']);
        $data['postcode_1'] = substr($postcode, 0, 3);
        $data['postcode_2'] = substr($postcode, 3, 4);

        $this->load->model('localisation/city');

        if (isset($this->request->post['city_id'])) {
            $data['city_id'] = $this->request->post['city_id'];
        } elseif (!empty($address_info)) {
            $data['city_id'] = $address_info['city_id'];
        } else {
            $data['city_id'] = '';
        }

        if (isset($this->request->post['county_id'])) {
            $data['county_id'] = $this->request->post['county_id'];
        } elseif (!empty($address_info)) {
            $data['county_id'] =  $address_info['county_id'];
        } else {
            $data['county_id'] = '';
        }

        if (isset($this->request->post['city'])) {
            $data['city'] = $this->request->post['city'];
        } elseif (!empty($address_info)) {
            $data['city'] = $address_info['city'];
        } else {
            $data['city'] = '';
        }

        if (isset($this->request->post['country_id'])) {
            $data['country_id'] = (int)$this->request->post['country_id'];
        }  elseif (!empty($address_info)) {
            $data['country_id'] = $address_info['country_id'];
        } else {
            $data['country_id'] = $this->config->get('config_country_id');
        }

        if (isset($this->request->post['zone_id'])) {
            $data['zone_id'] = (int)$this->request->post['zone_id'];
        }  elseif (!empty($address_info)) {
            $data['zone_id'] = $address_info['zone_id'];
        } else {
            $data['zone_id'] = '';
        }

        $this->load->model('localisation/country');

        $data['countries'] = $this->model_localisation_country->getCountries();

        // Custom fields
        $data['custom_fields'] = array();

        $this->load->model('account/custom_field');

        $custom_fields = $this->model_account_custom_field->getCustomFields($this->config->get('config_customer_group_id'));

        foreach ($custom_fields as $custom_field) {
            if ($custom_field['location'] == 'address') {
                $data['custom_fields'][] = $custom_field;
            }
        }

        if (isset($this->request->post['custom_field']['address'])) {
            $data['address_custom_field'] = $this->request->post['custom_field']['address'];
        } elseif (isset($address_info)) {
            $data['address_custom_field'] = $address_info['custom_field'];
        } else {
            $data['address_custom_field'] = array();
        }

        if (isset($this->request->post['default'])) {
            $data['default'] = $this->request->post['default'];
        } elseif (isset($this->request->get['address_id'])) {
            $data['default'] = $this->customer->getAddressId() == $this->request->get['address_id'];
        } else {
            $data['default'] = false;
        }

        $data['back'] = $this->url->link('account/address');
        $data['config_select_country'] = config('config_select_country');

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('account/address_form', $data));
    }

    protected function validateForm() {
        $firstname = trim((string)$this->request->post['firstname']);
        $name_invalid = (utf8_strlen($firstname) < 1) || (utf8_strlen($firstname) > 32);

        if (is_ft()) {
            $lastname = trim((string)$this->request->post['lastname']);
            $name_invalid = $name_invalid || (utf8_strlen($lastname) < 1) || (utf8_strlen($lastname) > 32);
        }

        if ($name_invalid) {
            $this->error['firstname'] = $this->language->get('error_name');
        }

        $firstname_kana = is_katakana(array_get($this->request->post, 'firstname_kana', ''));
        $lastname_kana = is_katakana(array_get($this->request->post, 'lastname_kana', ''));
        if ($firstname_kana === false || $lastname_kana === false) {
            $this->error['firstname_kana'] = $this->language->get('error_kana');
        } else {
            $this->request->post['firstname_kana'] = $firstname_kana;
            $this->request->post['lastname_kana'] = $lastname_kana;
        }

        $telephone_1 = is_valid_phone_segment(array_get($this->request->post, 'telephone_1', ''), 2, 3);
        $telephone_2 = is_valid_phone_segment(array_get($this->request->post, 'telephone_2', ''), 4, 4);
        $telephone_3 = is_valid_phone_segment(array_get($this->request->post, 'telephone_3', ''), 4, 4);
        if ($telephone_1 === false || $telephone_2 === false || $telephone_3 === false) {
            $this->error['telephone'] = $this->language->get('error_telephone');
        } else {
            $this->request->post['telephone_1'] = $telephone_1;
            $this->request->post['telephone_2'] = $telephone_2;
            $this->request->post['telephone_3'] = $telephone_3;
        }

        if ((utf8_strlen(trim($this->request->post['address_1'])) < 3) || (utf8_strlen(trim($this->request->post['address_1'])) > 128)) {
            $this->error['address_1'] = $this->language->get('error_address_1');
        }

        $this->load->model('localisation/country');

        $country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);

        if ($country_info && $country_info['postcode_required'] && !preg_match('/^\d{7}$/', trim($this->request->post['postcode']))) {
            $this->error['postcode'] = $this->language->get('error_postcode');
        }

        if ($this->request->post['country_id'] == '' || !is_numeric($this->request->post['country_id'])) {
            $this->error['country'] = $this->language->get('error_country');
        }

        $zone_id = (int)array_get($this->request->post, 'zone_id');
        if (!$zone_id) {
            $this->error['zone'] = $this->language->get('error_zone');
        }

        $city_id = (int)array_get($this->request->post, 'city_id');
        if ($zone_id > 0) {
            $this->load->model('localisation/city');
            $cities = $this->model_localisation_city->getCitiesByZoneId($zone_id, true);
            // 当前 zone 下有 city, 但 city 没有选择
            if (!empty($cities)) {
                if ($city_id < 1) {
                    $this->error['city'] =  t('error_city_id');
                } else {
                    $this->request->post['city'] = '';
                }
            } else { // 当前 zone 下没有 city
                $city_id = 0;
                $this->request->post['city_id'] = 0;
                $this->request->post['county_id'] = 0;
            }
        }

        $county_id = (int)array_get($this->request->post, 'county_id');
        if ($city_id > 0) {
            $this->load->model('localisation/city');
            $counties = $this->model_localisation_city->getCitiesByZoneId($city_id, false);
            // 当前 city 下有 county, 但 county 没有选择
            if (!empty($counties)) {
                if ($county_id < 1) {
                    $this->error['county'] =  t('error_county_id');
                }
            }
        }

        // 邮编拆分
        $postcode = preg_replace('/[^0-9]/', '', (string)array_get($this->request->post, 'postcode', ''));
        $this->request->post['post1'] = substr($postcode, 0, 3);
        $this->request->post['post2'] = substr($postcode, 3, 4);

        // 名称冗余
        $this->request->post['country'] = $country_info ? $country_info['name'] : '';

        $this->load->model('localisation/zone');
        $zone_info = $this->model_localisation_zone->getZone($zone_id);
        $this->request->post['zone'] = $zone_info ? $zone_info['name'] : '';

        $this->load->model('localisation/city');
        $city_info = $this->model_localisation_city->getCity($city_id);
        $this->request->post['city'] = $city_info ? $city_info['name'] : '';

        $county_info = $this->model_localisation_city->getCounty($county_id);
        $this->request->post['county'] = $county_info ? $county_info['name'] : '';


        // Custom field validation
        $this->load->model('account/custom_field');

        $custom_fields = $this->model_account_custom_field->getCustomFields($this->config->get('config_customer_group_id'));

        foreach ($custom_fields as $custom_field) {
            if ($custom_field['location'] == 'address') {
                if ($custom_field['required'] && empty($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']])) {
                    $this->error['custom_field'][$custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
                } elseif (($custom_field['type'] == 'text') && !empty($custom_field['validation']) && !filter_var($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']], FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => $custom_field['validation'])))) {
                    $this->error['custom_field'][$custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
                }
            }
        }

        return !$this->error;
    }

    protected function validateDelete() {
        if ($this->model_account_address->getTotalAddresses() == 1) {
            $this->error['warning'] = $this->language->get('error_delete');
        }

        if ($this->customer->getAddressId() == $this->request->get['address_id']) {
            $this->error['warning'] = $this->language->get('error_default');
        }

        return !$this->error;
    }
}
