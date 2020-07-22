<?php

/**
 * Class Page
 *
 * html page template
 */
class Page {
    private $view;
    private $title;
    private $content;
    private $scripts = array();
    private $styles = array();
    private $page_description;
    private $page_keywords;
    private $canonical;
    private $templateName;
    private $is_noindex = true;
    private $is_nofollow = true;
    private $page_image;
    private $customTags = array();
    
    public $ptitle = '';
    public $globAlert = '';
    public $ptitle_lnk = '';
    public $breadcrumbs = array();

    function __construct() {
        $this->view = new View();
        $this->templateName = TDR.'/index.php';
    }

    public function renderpage(){
        
        $this->view->title = $this->title;

        $this->view->styles = $this->styles;
        $this->view->scripts = $this->scripts;
        $this->view->content = $this->content;
        $this->view->page_description = $this->page_description;
        $this->view->page_keywords = $this->page_keywords;
        $this->view->canonical = $this->canonical;
        $this->view->customTags = $this->customTags;

        $this->view->is_noindex = $this->is_noindex;
        $this->view->is_nofollow = $this->is_nofollow;
        $this->view->ptitle = $this->ptitle;
        $this->view->globAlert = $this->globAlert;

        $this->view->ptitle_lnk = $this->ptitle_lnk;
        $this->view->breadcrumbs = $this->breadcrumbs;

        $content = $this->view->render($this->templateName);

        //TODO: Memcached
        //$content = self::CacheQuery($content);
        //$content = self::PageMinimize($content);


        return $content;
    }

    public static function PageMinimize($content)
    {		
            $content = str_replace("\r\n", "\n", $content);

            return $content;
    }

    public static function ClearUserCache($user)
    {
            
    }

    /**
     * Enter description here...
     *
     * @param int $lastModified
     * @param string $etag
     */
    public static function IsCached($lastModified=null, $etag=null)
    {
            // Выдаём заголовок HTTP Last-Modified
            if ($etag) {
                    header("ETag: \"$etag\"");
            }

            if ($lastModified) {
                    header ("Last-Modified: ".gmdate("D, d M Y H:i:s",$lastModified)." GMT");
            }

            if ($etag || $lastModified) {
                    header('Cache-Control: must-revalidate, max-age=0');
                    header("Cache-Control: private", false);
            }

            // Получаем заголовки запроса клиента ? только для Apache

            if(function_exists('getallheaders'))
                    $request = getallheaders();
            else{

                    foreach($_SERVER as $h=>$v){
                            if(preg_match('/HTTP_(.+)/',$h,$hp)) {
                                $headers[$hp[1]] = $v;
                            }
                    }
                    $request = $headers;

            }

            if ($etag && isset($request['If-None-Match']))
            {
                    if (preg_match('/^"(.+?)"$/', $request['If-None-Match'], $match) > 0)
                    {
                            if ($etag === $match[1])
                            {

                                    header('HTTP/1.1 304 Not Modified');
                                    exit();
                            }
                    }
            }

            if ($lastModified && isset($request['IF_MODIFIED_SINCE']))
            {
                    $modifiedSinceA = explode(';', $request['IF_MODIFIED_SINCE']);
                    $modifiedSince = strtotime($modifiedSinceA[0]);
                    //if($_GET['aaa']==1)echo "lastModified=$lastModified and modifiedSince=$modifiedSince";
                    if ($lastModified <= $modifiedSince)
                    {

                            header('HTTP/1.1 304 Not Modified');
                            exit();
                    }
            }
    }
    protected static function CacheQuery($content)
    {
        return $content;
    }

    /**
     * Add css file references
     *
     * @param string $filename
     */
    public function addStyleFile($filename)
    {
            $this->styles[] = $filename;
    }

    /**
     * Add script references
     *
     * @param string $filename
     */
    public function addScriptFile($filename)
    {
            $this->scripts[] = $filename;
    }
    /**
     * Set page title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
            $this->title = $title;
    }
    public function setPageImage($page_image)
    {
           $this->view->page_image = $page_image;
    }
    /**
     * Sets content
     *
     * @param string $sectionName
     */
    public function setContent($content)
    {
            $this->content = $content;
    }

    public function setDescription($pagedescr)
    {
            $this->page_description = $pagedescr;
    }

    public function setKeywords($pagekeywords)
    {
            $this->page_keywords = $pagekeywords;
    }

    public function setCanonical($canonical)
    {
            $this->canonical = $canonical;
    }

    public function getCanonical()
    {
            return $this->canonical;
    }

    public function setTemplateName($value)
    {
            $this->templateName = $value;
    }
    /**
     *
     * @param boolean $value
     */
    public function setNoIndex($value)
    {
            $this->is_noindex = $value;
    }

    /**
     *
     * @param boolean $value
     */
    public function setNoFollow($value)
    {
            $this->is_nofollow = $value;
    }
    public function addCustomTag($value)
    {
            $this->customTags[] = $value;
    }
}
?>
