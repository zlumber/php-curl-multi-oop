<?php

/**
 * Simulates google chrome browser for requests
 * @author   Martins Balodis <martins256@gmail.com>
 * @category Curl_Browser_Class
 * @package  Curl
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     https://github.com/martinsbalodis/php-curl-multi-oop
 */
class Curl_Browser_GoogleChrome extends Curl {

	public function __construct($use_cookie = false) {

		// initialize curl
		parent::__construct($use_cookie);

		// Sent headers will be saved
		$this->setopt(CURLINFO_HEADER_OUT, true);

		// Chrome user agent
		$this->setopt(CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_4) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.15 Safari/537.1");

		// Follow redirects. This is limited to 4 redirects
		$this->setopt(CURLOPT_FOLLOWLOCATION, true);
		$this->setopt(CURLOPT_MAXREDIRS, 4);

		// The contents of the "Accept-Encoding: " header. This enables decoding of the response. Supported encodings are "identity", "deflate", and "gzip". If an empty string, "", is set, a header containing all supported encoding types is sent.
		$this->setopt(CURLOPT_ENCODING, '');

        $this->set_fetch_headers();
    }
}
