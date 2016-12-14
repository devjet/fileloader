<?php

namespace devjet\fileloader;


final class FileLoader
{

    const ALLOWED_EXTENSIONS = ['gif', 'jpg', 'png'];
    const DIRECTORY_SEPARATOR = '/';

    private $_allowedExtensions;

    private $_savePath;
    private $_loadURL;
    private $_createSavePathIfNotExists;
    private $_allowAllExtensions;

    private $_remoteFileName;
    private $_localFilePath;

    public function __construct()
    {
        $this->_allowAllExtensions = false;
        $this->_localFilePath = null;
        $this->_allowedExtensions = self::ALLOWED_EXTENSIONS;
    }


    /**
     * Create shorthand instance
     * @return FileLoader
     */
    public static function getLoader()
    {
        return new FileLoader();
    }

    /**
     * When load is finished, returns file path, if not - null.
     * @return mixed
     */
    public function getLoadedFilePath()
    {
        return $this->_localFilePath;
    }


    /**
     * Setter for File Save path
     *
     * @param $path
     * @param bool $createIfNotExists
     * @return FileLoader
     */
    public function setSavePath($path, $createIfNotExists = false)
    {
        $this->_createSavePathIfNotExists = $createIfNotExists;
        $this->_savePath = $path;
        return $this;
    }


    /**
     * Setter for file load URL
     *
     * @param $URL
     * @return FileLoader
     */
    public function setLoadURL($URL)
    {
        $this->_loadURL = $URL;
        return $this;
    }

    /**
     * Turn off limit fro file extensions.
     *
     * @param bool $allow
     * @return FileLoader
     */
    public function setAllowAllExtensions($allow = true)
    {
        $this->_allowAllExtensions = boolval($allow);
        return $this;
    }

    /**
     * Add file extension by parameters or array
     *
     * @return FileLoader
     */
    public function addExtension()
    {
        if (func_num_args() > 0) {
            $args = func_get_args();
            foreach ($args as $arg) {
                if (!is_array($arg)) {
                    $this->_allowedExtensions[] = $arg;
                } else {
                    $this->_allowedExtensions = array_merge($this->_allowedExtensions, $arg);
                }
            }
            $this->_allowedExtensions = array_unique(array_map(function ($e) {
                return strtolower(trim($e, ' .'));
            }, $this->_allowedExtensions));
        }
        return $this;
    }

    /**
     * File Load launcher
     *
     * @return FileLoader
     */
    public function load()
    {
        $this->initLoader();
        $this->_downloadFile();

        return $this;
    }

    /**
     * Initial checks before file download
     */
    private function initLoader()
    {
        $this->_initSavePath();
        $this->_initDownloadURL();
    }


    /**
     * Check save path for existence,
     * and if there's parameter - trying to create new folder
     *
     * @throws \Exception
     */
    private function _initSavePath()
    {
        $realPath = realpath($this->_savePath);
        if ($realPath === false AND !is_dir($realPath)) {
            if ($this->_createSavePathIfNotExists) {
                umask(0);
                if (!mkdir($this->_savePath, 0755)) {
                    throw new \Exception("Save Path Exception: Could not create directory by path '" . $this->_savePath);
                }
            } else {
                throw  new \Exception("Save Path Exception: Directory \"" . $this->_savePath . "\" does not exists");
            }
        }
    }


    /**
     * Main checks for download URLs
     * -Valid URL
     * -Header checking for Host resolving and everything except HTTP 200
     * -Getting real file name from headers, and if not, trying to get it from url
     * -Checking for Allowed extension
     *
     * @throws \Exception
     */
    private function _initDownloadURL()
    {
        //Check for valid URL
        if (filter_var($this->_loadURL, FILTER_VALIDATE_URL) === FALSE) {
            throw new \Exception("URL Exception: not valid URL");
        }

        //Check for !=404 or other things
        $headers = @get_headers($this->_loadURL);
        if (!$headers || !stripos($headers[0], "200 OK")) {
            throw new \Exception("URL Exception: file is not accessible on URL: \"" . $this->_loadURL . "\"");
        }

        //Trying to get exact file name
        if (isset($headers['Content-Disposition'])) {
            $fileRegexp = '/^Content-Disposition: .*?filename=(?<f>[^\s]+|\x22[^\x22]+\x22)\x3B?.*$/m';
            if (preg_match($fileRegexp, $headers['Content-Disposition'], $result)) {
                $this->_remoteFileName = trim($result['f'], ' ";');
            }
        } else {
            $this->_remoteFileName = basename(parse_url($this->_loadURL, PHP_URL_PATH));
        }

        //Check file for right extension
        $fileExtension = pathinfo($this->_remoteFileName)['extension'];
        if (!in_array($fileExtension, $this->_allowedExtensions) && !$this->_allowAllExtensions) {
            throw new \Exception("URL Exception: remote file has forbidden extension: \"" . $fileExtension . "\"");
        }

    }


    /**
     * Download and store file
     * when no errors, saved file path is accessible by getLoadedFilePath()
     *
     * @throws \Exception
     */
    private function _downloadFile()
    {
        $remote = fopen($this->_loadURL, "rb");

        if (!$remote) {
            throw new \Exception('Download Exception: URL open failed.');
        }

        $fileSavePath = realpath($this->_savePath) . self::DIRECTORY_SEPARATOR . $this->_remoteFileName;

        $local = fopen($fileSavePath, 'w');
        if (!$local) {
            throw new \Exception("Download Exception: Local file opening failed");
        }

        while (!feof($remote)) {
            $content = fread($remote, 8192);
            fwrite($local, $content);
        }
        fclose($local);
        fclose($remote);

        $this->_localFilePath = $fileSavePath;

    }


}