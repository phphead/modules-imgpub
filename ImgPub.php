<?php namespace Modules;

use Finfo,
	Modules\ImgPub_Exception as Exception;

class ImgPub {

	protected $_config;

	public function __construct(array $config) {
		$this->_config = $config;
	}

	/** @return ImgPub_MetaData */
	public function set($id, array $options) {
		$imgpub    = $this->_config;
		$timestamp = time();

		$options['id'] = $id;
		$content = http_build_query($options);

		$context = stream_context_create([
			'http' => [
				'header'  => implode("\r\n", [
					"Content-Type: application/x-www-form-urlencoded",
					"Content-Length: " . strlen($content),
					"Api-Username: "   . $imgpub['username'],
					"Api-BucketName: " . $imgpub['bucket'],
					"Api-Timestamp: "  . $timestamp,
					"Api-Key: "        . hash('sha256', $imgpub['username'] . $imgpub['bucket'] . $imgpub['key'] . $timestamp),
					"Default-Port: yes",
					"Host: imgpub.ru",
					"Connection: close",
					""
				]),
				'method'  => 'POST',
				'content' => $content,
				'timeout' => 2,
			],
		]);

		$json = @ json_decode($content = file_get_contents($imgpub['url'] . 'info', null, $context), true);
		if ( ! empty($json['code']) && 1 === $json['code']) {
			return new ImgPub_MetaData($json['data']);

		} else if ( ! empty($this->_config['debug'])) {
			throw new Exception("Bad response from imgpub: #" . $json['code'] . " " . $json['message'] . ' ' . $content);

		} else {
			//Logger::messages()->error("Bad response from imgpub: #" . $json['code'] . " " . $json['message'] . ' ' . $content);
			throw new Exception("Bad response from imgpub");
		}
	}

	/** @return ImgPub_MetaData */
	public function get($id) {
		return $this->set($id, []);
	}

	/** @return ImgPub_MetaData */
	public function put($fileName) {
		$imgpub    = $this->_config;
		$timestamp = time();

		$context = stream_context_create([
			'http' => [
				'header'  => implode("\r\n", [
					"Content-Type: application/x-www-form-urlencoded",
					"Content-Length: " . filesize($fileName),
					"Api-Username: "   . $imgpub['username'],
					"Api-BucketName: " . $imgpub['bucket'],
					"Api-Timestamp: "  . $timestamp,
					"Api-Key: "        . hash('sha256', $imgpub['username'] . $imgpub['bucket'] . $imgpub['key'] . $timestamp),
					"Host: imgpub.ru",
					"Connection: close",
					""
				]),
				'method'  => 'POST',
				'content' => file_get_contents($fileName),
				'timeout' => 300,
			],
		]);

		$json = @ json_decode($content = file_get_contents($imgpub['url'] . 'put', null, $context), true);
		if ( ! empty($json['code']) && 1 === $json['code']) {
			return new ImgPub_MetaData($json['data']);

		} else if ( ! empty($this->_config['debug'])) {
			throw new Exception("Bad response from imgpub (test): #" . $json['code'] . " " . $json['message'] . ' ' . $content);

		} else {
			//Logger::messages()->error("Bad response from imgpub: #" . $json['code'] . " " . $json['message'] . ' ' . $content);
			throw new Exception("Bad response from imgpub");
		}
	}

	/** @return ImgPub_MetaData */
	public function upload($fileList) {
		if ( ! is_array($fileList))
			$fileList = [ $fileList ];

		$imgpub    = $this->_config;
		$timestamp = time();
		$boundary  = '--------------------------' . microtime(true);
		$content   = '';

		foreach ($fileList as $key => $value) {
			if (is_int($key)) {
				$fileName = $value;
				$baseName = basename($fileName);

			} else {
				$fileName = $key;
				$baseName = $value;
			}

			$mime = (new Finfo())->file($fileName, FILEINFO_MIME_TYPE);

			$content .= implode("\n", [
				"--$boundary",
				"Content-Disposition: form-data; name=\"file[]\"; filename=\"$baseName\"",
				"Content-Type: " . $mime . "\n",
				file_get_contents($fileName),
				"--$boundary",
			]);
		}

		$context = stream_context_create([
			'http' => [
				'header'  => implode("\r\n", [
					"Content-Type: multipart/form-data; boundary=" . $boundary,
					"Content-Length: " . strlen($content),
					"Api-Username: "   . $imgpub['username'],
					"Api-BucketName: " . $imgpub['bucket'],
					"Api-Timestamp: "  . $timestamp,
					"Api-Key: "        . hash('sha256', $imgpub['username'] . $imgpub['bucket'] . $imgpub['key'] . $timestamp),
					"Host: imgpub.ru",
					"Connection: close",
					""
				]),
				'method'  => 'POST',
				'content' => $content,
			],
		]);

		$json = @ json_decode($content = file_get_contents($imgpub['url_upload'], null, $context), true);
		if ( ! empty($json['code']) && 1 === $json['code']) {
			return new ImgPub_MetaData($json['data']);

		} else if ( ! empty($this->_config['debug'])) {
			throw new Exception("Bad response from imgpub (test): #" . $json['code'] . " " . $json['message'] . ' ' . $content);

		} else {
			//Rj\Logger::messages()->error("Bad response from imgpub: #" . $json['code'] . " " . $json['message'] . ' ' . $content);
			throw new Exception("Bad response from imgpub");
		}
	}

}
