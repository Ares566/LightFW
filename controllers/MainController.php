<?php

class MainController
{

    protected $page;
    protected $vars = array();

    function __construct($var = null)
    {

        global $PAGE;
        $this->page = $PAGE;
        if (!is_null($var)) $this->vars = $var;

    }

    /**
     * Заполняет глобальную страницу данными
     *
     * $index = true значит индексировать
     * $follow = true значит следовать по ссылкам
     * @param string $content
     * @param string $title
     * @param string $description
     * @param string $keywords
     * @param bool $index
     * @param bool $follow
     */
    protected function renderpage($content = '', $title = '', $description = '', $keywords = '', $index = true, $follow = true)
    {
        $this->page->setTitle($title);
        $this->page->setContent($content);
        $this->page->setDescription($description);
        $this->page->setKeywords($keywords);
        $this->page->setNoIndex(!$index);
        $this->page->setNoFollow(!$follow);

        $canonical = $this->page->getCanonical();

        $this->setCanonical($canonical);

    }

    protected function setPageImage($path)
    {
        $this->page->setPageImage($path);
    }

    protected function addCustomTag($tag)
    {
        $this->page->addCustomTag($tag);
    }

    protected function setCanonical($canonical)
    {

        $this->page->setCanonical($canonical);
    }

}

