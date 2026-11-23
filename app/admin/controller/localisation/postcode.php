<?php
class ControllerLocalisationPostcode extends Controller {
	public function index() {
		$json = array();

		if (isset($this->request->get['postcode'])) {
			$this->load->model('localisation/postcode');

			$json = $this->model_localisation_postcode->getAddressByPostcode($this->request->get['postcode']);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
