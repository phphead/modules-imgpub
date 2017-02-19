<?php namespace Modules;

/**
 * Class ImgPub_MetaData
 * @package Modules
 *
 * @property mixed $id
 * @property mixed $length
 * @property mixed $center_x
 * @property mixed $center_y
 * @property mixed $rotate
 * @property mixed $width
 * @property mixed $height
 */
class ImgPub_MetaData {

	protected $_data = [];

	public function __construct(array $data) {
		$this->id    = $data['id'];
		$this->_data = $data;
	}

	public function __get($key) {
		return isset($this->_data[$key]) ? $this->_data[$key] : null;
	}

	public function getURL($key) {
		return isset($this->_data[$key]['src']) ? $this->_data[$key]['src'] : false;
	}

	public function getSize($key) {
		return isset($this->_data[$key]['width']) ? [ $this->_data[$key]['width'], $this->_data[$key]['height'] ] : false;
	}

	public function getLength($key) {
		return isset($this->_data[$key]['length']) ? $this->_data[$key]['length'] : false;
	}

	public function toArray() {
		return $this->_data;
	}

}
