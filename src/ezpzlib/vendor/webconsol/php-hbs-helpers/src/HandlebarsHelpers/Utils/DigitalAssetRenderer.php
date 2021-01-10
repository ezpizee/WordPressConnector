<?php

namespace HandlebarsHelpers\Utils;


use Exception;
use HandlebarsHelpers\Exception\Error;
use RuntimeException;

class DigitalAssetRenderer
{
    private $debug = false;
    private $root = '';
    private $requestFile = '';

    public function __construct(string $root, string $requestFile='')
    {
        if (file_exists($root)) {
            $this->root = $root;
            if ($requestFile) {
                $this->requestFile = $requestFile;
            }
            else {
                $this->requestFile = trim(filter_input(INPUT_SERVER, 'REQUEST_URI'), '/');
            }
        }
    }

    public function render()
    {
        if ($this->root && $this->requestFile)
        {
            $file = StringUtil::removeDoubleSlashes($this->root.'/'.$this->requestFile);

            if(file_exists($file))
            {
                $mimeType = $this->getMimeType($file);
                if ($this->canAccess($this->requestFile)) {
                    header('HTTP/2.0 200 OK');
                    header('Content-Type: '.$mimeType);
                    //header('Content-disposition: attachment; filename="'.md5($this->requestFile).'.'.pathinfo($file, PATHINFO_EXTENSION).'"');
                    header('Content-Length: ' . filesize($file));
                    echo file_get_contents($file);
                }
                else {
                    http_response_code(403);
                    new Error('You are not allowed to access: '.$_SERVER['REQUEST_URI'], 403);
                }
            }
            else
            {
                http_response_code(404);
                new Error('404 Page Not Found: '.$_SERVER['REQUEST_URI'], 404);
            }
        }
    }

    public function setDebug(bool $b){$this->debug=$b;}

    protected function canAccess(string $path): bool {
        if (!$path) {
            return false;
        }
        return true;
    }

    protected function getMimeType($file): string
    {
        try {
            $extConfigFile = __DIR__.'/data/mimetypes.json';
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            $exts = file_exists($extConfigFile) ? json_decode(file_get_contents($extConfigFile), true) : [];
            if (!empty($exts) && isset($exts[$ext])) {
                $mimeType = $exts[$ext];
            }
            else {
                $mimeType = mime_content_type($file);
            }
        }
        catch (Exception $e) {
            throw new RuntimeException($e->getMessage(), 500);
        }
        return $mimeType;
    }
}