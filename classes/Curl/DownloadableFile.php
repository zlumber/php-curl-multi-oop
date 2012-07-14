<?php

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
