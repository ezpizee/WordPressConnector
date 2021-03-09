<?php

namespace Ezpizee\SupportedCMS\Joomla;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

class Properties
{
    /**
     * @var Document
     */
    public $jdoc;
    /**
     * @var Registry
     */
    public $menuParams;
    /**
     * @var HtmlDocument
     */
    public $htmlDoc;
    public $bodyClasses = '';
    public $tmpl = '';
    public $theme = '';
    public $webRoot = '';
    public $tmplWebRoot = '';
    public $langUrlPath = '';
    public $urlPathExt = '';
    public $bodyStyle = '';
    public $errors = [];
    public $isAuthed = false;

    public function __construct(HtmlDocument $htmlDoc, CMSApplication $app)
    {
        $this->isAuthed = !(\Joomla\CMS\Factory::getUser()->guest > 0);
        $this->jdoc = $app->getDocument();
        $this->htmlDoc = $htmlDoc;
        $this->menuParams = Menu::getActiveMenuParams();
        $this->tmpl = $this->menuParams->get('tmpl', 'index.php');
        $this->theme = $this->menuParams->get('theme', 'main.php');
        $this->webRoot = Uri::root();
        $this->tmplWebRoot = Uri::root(true).'/templates/'.$this->htmlDoc->template;
        $this->langUrlPath = explode('-', $this->jdoc->language)[0];
        if ($app->get('sef_suffix')) {$this->urlPathExt = '.html';}
        if ($this->menuParams->get('menu_image')) {
            $this->bodyStyle = ' style="background-image: url(\''.$this->menuParams->get('menu_image').'\')"';
        }
        if ($this->menuParams->get('menu-meta_description')) {
            $this->htmlDoc->setDescription($this->menuParams->get('menu-meta_description'));
        }
        if ($this->menuParams->get('menu-meta_keywords')) {
            $this->htmlDoc->setMetaData('keywords', $this->menuParams->get('menu-meta_keywords'));
        }
        $this->jdoc->setGenerator('');
        $this->errors = $app->getMessageQueue();
        $this->bodyClasses();
        $this->removePlatformClientLib();
    }

    public function hasModule($position): bool {
        if (is_numeric($position)) {
            return $this->htmlDoc->countModules('position-' . $position);
        }
        return $this->htmlDoc->countModules($position);
    }

    private function bodyClasses(): void {
        $bodyClasses = [];
        $bodyClasses[] = str_replace('.php', '', $this->tmpl);
        $bodyClasses[] = str_replace('.php', '', $this->theme);
        $bodyClasses[] = $this->isAuthed ? 'is-authed' : 'is-public';
        $this->bodyClasses = implode(' ', $bodyClasses);
    }

    private function removePlatformClientLib(): void {
        $excludes = [
            '/media/jui/js/jquery.min.js',
            '/media/jui/js/jquery-noconflict.js',
            '/media/jui/js/jquery-migrate.min.js',
            '/media/jui/js/bootstrap.min.js'
        ];
        $scrips = [];
        foreach ($this->htmlDoc->_scripts as $src=>$type) {
            if (!in_array($src, $excludes)) {
                $scrips[$src] = $type;
            }
        }
        $this->htmlDoc->_scripts = $scrips;
    }
}