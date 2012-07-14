<?php

/**
 * php curl functions wrapped in a class
 * @author   Martins Balodis <martins256@gmail.com>
 * @category Curl_Base_Class
 * @package  Curl
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     https://github.com/martinsbalodis/php-curl-multi-oop
 */
class Curl {

	/**
	 * Curl Handle
	 * @var
	 */
	protected $ch;

	/**
	 * Fetch received headers
	 * @var boolean
	 */
	protected $fetch_headers = false;

	/**
	 * Received headers after execution
	 * @var string
	 */
	protected $headers_received;

    /**
     * An associative array of headers to send along with requests
     *
     * @var array
     **/
    public $headers = array();

    /**
     * Cookie file.
     *
     * @var string
     */
    protected $cookie_file;

	/**
	 * Initializes curl handler
	 */
	public function __construct($use_cookie = false) {

		// Initializes Curl handler
		$this->ch = curl_init();

		// Result will be returned not outputed
		$this->setopt(CURLOPT_RETURNTRANSFER, true);

        if ($use_cookie == true)
        {
            $this->cookie_file = __DIR__ . '/cookies.txt';
            // ensure cookie is writable by attempting to open it up
            $this->open_cookie();
            $this->setopt(CURLOPT_COOKIEFILE, $this->cookie_file);
            $this->setopt(CURLOPT_COOKIEJAR, $this->cookie_file);
        }
	}

	// @TODO Jāizveido iespēja rezultātu uzreiz saglabāt failā uz fopen handle

	/**
	 * Set curl parameter
	 * @param integer $option Curl constant
	 * @see http://php.net/manual/en/function.curl-setopt.php
	 * @param mixed $value
	 */
	public function setopt($option, $value) {

		curl_setopt($this->ch, $option, $value);
	}

	/**
	 * Sets data request method
	 * @param string $method
	 */
	public function set_method($method) {

		switch ($method) {
			case 'POST' : curl_setopt($this->ch, CURLOPT_POST, true);
				break;
			case 'GET' : curl_setopt($this->ch, CURLOPT_HTTPGET, TRUE);
				break;
			default : throw new Exception('Invalid request method. ' . htmlspecialchars($method), 1);
		}
	}

	/**
	 * Set post parameter string.
	 * Data will be sent as post.
	 * @param string $post_string
	 */
	public function set_post_string($post_string) {

		$this->setopt(CURLOPT_POSTFIELDS, $post_string);
	}

	/**
	 * Set execution url. This can be also set in exec method.
	 * @param string $url
	 */
	public function set_url($url) {

		curl_setopt($this->ch, CURLOPT_URL, $url);
	}

    /**
     * Formats and adds custom headers to the current request
     *
     * @return void
     * @access protected
     **/
    protected function set_request_headers()
    {
        $headers = array();
        foreach ($this->headers as $key => $value)
        {
            $headers[] = $key . ': ' . $value;
        }
        $this->setopt(CURLOPT_HTTPHEADER, $headers);
    }

	/**
	 * Execute request
	 * @param string $url
	 * @return string
	 */
	public function exec($url = null) {

		// sets execution url if it is supplied
		if ($url !== null) {
			$this->set_url($url);
		}

        $this->set_request_headers();

		// Received headers must be retrieved
		if($this->fetch_headers) {

			// set curl to return headers
			$this->setopt(CURLOPT_HEADER, true);

			// Executes the request
			$result = curl_exec($this->ch);

			// set curl to NOT return headers
			$this->setopt(CURLOPT_HEADER, false);

            $pattern = '#HTTP/\d\.\d.*?$.*?\r\n\r\n#ims';

            # Extract headers from response
            preg_match_all($pattern, $result, $matches);
            $headers_string = array_pop($matches[0]);
            $headers        = explode("\r\n", str_replace("\r\n\r\n", '', $headers_string));

            # Remove headers from the response body
            $result = str_replace($headers_string, '', $result);

            # Extract the version and status from the first header
            $version_and_status = array_shift($headers);
            preg_match('#HTTP/(\d\.\d)\s(\d\d\d)\s(.*)#', $version_and_status, $matches);
            $this->headers_received['Http-Version'] = $matches[1];
            $this->headers_received['Status-Code']  = $matches[2];
            $this->headers_received['Status']       = $matches[2] . ' ' . $matches[3];

            # Convert headers into an associative array
            foreach ($headers as $header)
            {
                preg_match('#(.*?)\:\s(.*)#', $header, $matches);
                $this->headers_received[$matches[1]] = $matches[2];
            }
		}
		else {
			// Executes the request
			$result = curl_exec($this->ch);
		}



		return $result;
	}

	/**
	 * Atgriež headerus kādi tika nosūtīti uz serveri
	 * @return string
	 */
	public function get_headers_sent() {

		return curl_getinfo($this->ch, CURLINFO_HEADER_OUT);
	}

	/**
	 * Set to fetch received headers.
	 * This must be set before executing request via exec method
	 * afterwars use get_headers_received method
	 */
	public function set_fetch_headers() {

		$this->fetch_headers = true;
	}

	public function get_headers_receved() {
		return $this->headers_received;
	}

	/**
	 * Returns curl handle.
	 * @return type
	 */
	public function get_handle() {
		return $this->ch;
	}

	/**
	 * File download
	 * Save results into a file
	 * @param string $filename
	 */
	public function save_into_file($filename) {

		$file_handle = fopen($filename,'c+x+');

		$this->setopt(CURLOPT_FILE, $file_handle);
	}


    /**
     * open_cookie
     *
     * @access protected
     * @return void
     */
    protected function open_cookie() {
        // ensure file is writable
        if (file_exists($this->cookie_file))
        {
            if (posix_access($this->cookie_file, POSIX_W_OK) === false)
            {
                throw new Exception('File *' . ($this->cookie_file) . '* must be writable ' . 'for cookie storage.');
            }
        }
        // ensure file directory is writable
        else
        {
            $dir = dirname($this->cookie_file);
            if (is_writable($dir) === false)
            {
                throw new Exception('Path *' . ($dir) . '* must be writable for cookie ' . 'storage.');
            }

            // open file
            $resource = fopen($this->cookie_file, 'w');
            fclose($resource);
        }
    }
}