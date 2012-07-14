<?php

/**
 * Example:
 *
 * $multi_curl = new Curl_Multi();
 * $download_directory = 'downloads/'
 *
 * // Add files to queue
 * $files = array(
 *		'http://www.bildites.lv/images/thjoo72thgbe1zchm29.jpg',
 *		'http://www.bildites.lv/images/ldkdbsfypmu02h8s256.jpg',
 *		'http://www.bildites.lv/images/icujtts8m2xpm9j4cvzo.jpg',
 *		'http://www.bildites.lv/images/5lno9l1sncshe2f8gos.jpg',
 *		'http://www.bildites.lv/images/d4qgj1lbv1nnf81ahtb.jpg',
 *		);
 * foreach ($files as $file)
 * {
 *     $file_to_download = new DownloadableFile($download_directory, $file);
 *     $file_to_download->set_referer('http://www.bildites.lv/');
 *     $file_to_download->headers['Host'] = 'www.bildites.lv';
 *     $multi_curl->add_job($file_to_download);
 * }
 *
 * // Download all files at the same time.
 * $multi_curl->exec();
 */
class DownloadableFile extends Curl implements Curl_MultiReady
{
    public function __construct($download_directory, $url)
    {
        parent::__construct(true);

        $this->set_directory($download_directory);
        $this->set_url($url);
    }

    /**
     * Directory where to download file
     *
     * @var string
     */
    public $directory;

    public function set_directory($directory)
    {
        $this->directory = $directory;
    }

    /**
     * Extend set url method.
     * When usr is set download it into a file
     *
     * @param type $url
     */
    public function set_url($url)
    {

        $filename   = basename($url);
        $parsed_url = parse_url($url);
        $pathinfo   = pathinfo($parsed_url['path']);
        if (is_dir(rtrim($this->directory, '/') . $pathinfo['dirname']) == false)
        {
            if (!mkdir(rtrim($this->directory, '/') . $pathinfo['dirname'] . '/', 0755, true))
            {
                throw new Exception(sprintf('Can not create directory %s', rtrim($this->directory, '/') . $pathinfo['dirname']));
            }
        }
        $this->save_into_file(rtrim($this->directory, '/') . $pathinfo['dirname'] . '/' . $filename);

        parent::set_url($url);
    }

    /**
     * Executed when file downloaded
     */
    public function executed()
    {

    }
}
