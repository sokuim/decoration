<?php
namespace Cart;
class Customer {
	private $customer_id;
	private $firstname;
    private $lastname;
    private $firstname_kana;
    private $lastname_kana;
	private $customer_group_id;
	private $email;
	private $telephone_1;
	private $telephone_2;
	private $telephone_3;
	private $newsletter;
	private $address_id;

	public function __construct($registry) {
		$this->config = $registry->get('config');
		$this->db = $registry->get('db');
		$this->request = $registry->get('request');
		$this->session = $registry->get('session');

		if (isset($this->session->data['customer_id'])) {
			$customer_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$this->session->data['customer_id'] . "' AND status = '1'");

			if ($customer_query->num_rows) {
				$this->customer_id = $customer_query->row['customer_id'];
				$this->firstname = $customer_query->row['firstname'];
                $this->lastname = $customer_query->row['lastname'];
                $this->firstname_kana = $customer_query->row['firstname_kana'];
                $this->lastname_kana = $customer_query->row['lastname_kana'];
				$this->customer_group_id = $customer_query->row['customer_group_id'];
				$this->email = $customer_query->row['email'];
				$this->telephone_1 = $customer_query->row['telephone_1'];
				$this->telephone_2 = $customer_query->row['telephone_2'];
				$this->telephone_3 = $customer_query->row['telephone_3'];
				$this->newsletter = $customer_query->row['newsletter'];
				$this->address_id = $customer_query->row['address_id'];

				$this->db->query("UPDATE " . DB_PREFIX . "customer SET language_id = '" . (int)$this->config->get('config_language_id') . "', ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "' WHERE customer_id = '" . (int)$this->customer_id . "'");
			} else {
				$this->logout();
			}
		}
	}

  public function login($customer, $password, $override = false) {
	    if (filter_var($customer, FILTER_VALIDATE_EMAIL)) {
            $customer_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($customer)) . "' AND status = '1'");
        } else {
		    $customer_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$customer . "' AND status = '1'");
        }

		if ($customer_query->num_rows) {
			if (!$override) {
				if (password_verify($password, $customer_query->row['password'])) {
					if (password_needs_rehash($customer_query->row['password'], PASSWORD_DEFAULT)) {
						$new_password_hashed = password_hash($password, PASSWORD_DEFAULT);
					}
				} elseif ($customer_query->row['password'] == sha1($customer_query->row['salt'] . sha1($customer_query->row['salt'] . sha1($password))) || $customer_query->row['password'] == md5($password)) {
					$new_password_hashed = password_hash($password, PASSWORD_DEFAULT);
				} else {
					return false;
				}
			}
			
			$this->session->data['customer_id'] = $customer_query->row['customer_id'];

			$this->customer_id = $customer_query->row['customer_id'];
			$this->firstname = $customer_query->row['firstname'];
            $this->lastname = $customer_query->row['lastname'];
            $this->firstname_kana = $customer_query->row['firstname_kana'];
            $this->lastname_kana = $customer_query->row['lastname_kana'];
			$this->customer_group_id = $customer_query->row['customer_group_id'];
			$this->email = $customer_query->row['email'];
			$this->telephone_1 = $customer_query->row['telephone_1'];
			$this->telephone_2 = $customer_query->row['telephone_2'];
			$this->telephone_3 = $customer_query->row['telephone_3'];
			$this->newsletter = $customer_query->row['newsletter'];
			$this->address_id = $customer_query->row['address_id'];

			$this->db->query("UPDATE " . DB_PREFIX . "customer SET " . ((isset($new_password_hashed)) ? "salt = '', password = '" . $this->db->escape($new_password_hashed) . "', " : "") . "language_id = '" . (int)$this->config->get('config_language_id') . "', ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "' WHERE customer_id = '" . (int)$this->customer_id . "'");

			return true;
		} else {
			return false;
		}
	}

	public function logout() {
		unset($this->session->data['customer_id']);

		$this->customer_id = '';
		$this->firstname = '';
        $this->lastname = '';
        $this->firstname_kana = '';
        $this->lastname_kana = '';
		$this->customer_group_id = '';
		$this->email = '';
		$this->telephone_1 = '';
		$this->telephone_2 = '';
		$this->telephone_3 = '';
		$this->newsletter = '';
		$this->address_id = '';
	}

	public function isLogged() {
		return $this->customer_id;
	}

	public function getId() {
		return $this->customer_id;
	}

	public function getFirstName() {
		return $this->firstname;
	}

    public function getLastName() {
        return $this->lastname;
    }
    public function getFirstNameKana() {
        return $this->firstname_kana;
    }

    public function getLastNameKana() {
        return $this->lastname_kana;
    }

	public function getGroupId() {
		return $this->customer_group_id;
	}

	public function getEmail() {
		return $this->email;
	}

    public function getTelephone1() {
        return $this->telephone_1;
    }

    public function getTelephone2() {
        return $this->telephone_2;
    }

    public function getTelephone3() {
        return $this->telephone_3;
    }

    public function getTelephone() {
        return get_telephone($this->telephone_1, $this->telephone_2, $this->telephone_3);
    }

	public function getNewsletter() {
		return $this->newsletter;
	}

	public function getAddressId() {
		return $this->address_id;
	}

	public function getBalance() {
		$query = $this->db->query("SELECT SUM(amount) AS total FROM " . DB_PREFIX . "customer_transaction WHERE customer_id = '" . (int)$this->customer_id . "'");

		return $query->row['total'];
	}

	public function getRewardPoints() {
		$query = $this->db->query("SELECT SUM(points) AS total FROM " . DB_PREFIX . "customer_reward WHERE customer_id = '" . (int)$this->customer_id . "'");

		return $query->row['total'];
	}

    public function associated($provider)
    {
        if (empty($this->customer_id)) {
            return false;
        }

        $customer = \Models\Customer::find($this->customer_id);
        if (empty($customer)) {
            return false;
        }

        return $customer->authentications()->where('provider', $provider)->count();
    }
}
