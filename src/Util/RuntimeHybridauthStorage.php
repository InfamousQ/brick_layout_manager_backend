<?php

namespace InfamousQ\LManager\Util;


use Hybridauth\Storage\StorageInterface;

class RuntimeHybridauthStorage implements StorageInterface {

	protected $storage = array();

	public function get($key) {
		return array_key_exists($key, $this->storage) ? $this->storage[$key] : null;
	}

	public function set($key, $value) {
		return $this->storage[$key] = $value;
	}

	public function delete($key) {
		if (array_key_exists($key, $this->storage)) {
			unset($this->storage[$key]);
		}
	}

	public function deleteMatch($key) {
		$stored_keys = array_keys($this->storage);
		foreach ($stored_keys as $stored_key) {
			if (strstr($stored_key, $key)) {
				$this->delete($stored_key);
			}
		}
	}

	public function clear() {
		$this->storage = array();
	}
}