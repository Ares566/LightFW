<?php
/**
 * Class Utility
 *
 * User: Renat Abaidulin
 * Date: 09.08.2019
 */

class Utility
{

    /**
     * Singleton: Create a Memcached instance
     * @return Memcached instance
     */
    private static function mc_cache_client()
    {
        static $mcCache = null;
        if(is_null($mcCache)) {
            $mcd = new Memcached;
            //memcache_connect('10.14.126.2', 11211);
            $mcd->addServer('127.0.0.1', 11211);
            $mcCache = $mcd;
        }

        return $mcCache;
    }

    /**
     * Get Data from cache by key
     * @param string $id key
     * @return int|mixed
     */
    public static function getMCData($id)
    {
        $client = self::mc_cache_client();
        if($client)
            return $client->get($id);
        else return 0;
    }

    /**
     * Save data to cache by key
     * @param string $id key
     * @param mixed $data data to cache
     * @param int $ct expiration time
     */
    public static function setMCData($id,$data,$ct=3600)
    {
        $client = self::mc_cache_client();
        if($client)
            $client->set($id,$data,$ct);
    }



    /**
     * Send email
     *
     * @param $to
     * @param $subject
     * @param $body
     * @param null $from
     * @param bool $html
     * @throws Exception
     */
    public static function sendEmail($to, $subject, $body, $from=null, $html=true)
    {

        $eol="\r\n";

        $matches = null;
        if (preg_match('/(.*?)\s?<(.*?)>/', $from, $matches) > 0) {
            $from = '=?utf-8?B?' . base64_encode($matches[1]).'?=' . ' <' . $matches[2] . '>';
        }

        if (preg_match('/(.*?)\s?<(.*?)>/', $to, $matches)) {
            $to = '=?utf-8?B?' . base64_encode($matches[1]).'?=' . ' <' . $matches[2] . '>';
        }


        // заголовки
        $msg_id = Utility::microtimestr();

        $headers  = "From: $from" . $eol;
        $headers .= "Reply-To: $from" . $eol;
        $headers .= "Return-Path: $from" . $eol;
        $headers .= "Message-ID: <$msg_id>" . $eol;
        $headers .= "X-Mailer: PHP /" . phpversion() . $eol;
        $headers .= "MIME-Version: 1.0". $eol;

        if ($html)
            $headers .= "Content-Type: text/html; charset=UTF-8" . $eol;
        else
            $headers .= "Content-Type: text/plain; charset=UTF-8" . $eol;
        try{
            mail($to, $subject, $body, $headers);

        }
        catch(Exception $ex){
            throw new Exception($ex->getMessage());
        }

    }

    /**
     * Returns string with authTicket
     *
     * @return string
     */
    public static function generateTicket()
    {
        $acceptedChars = 'azertyuiopqsdfghjklmwxcvbnAZERTYUIOPQSDFGHJKLMWXCVBN0123456789';
        $max = strlen($acceptedChars)-1;
        $code1 = null;
        for($i=0; $i < 32; $i++) {
            $code1 .= $acceptedChars{mt_rand(0, $max)};
        }
        $ttcode1 = $code1.microtime(true);
        $ttcode2 = md5($ttcode1);

        $ttcode = substr($ttcode2, 0, 32);
        return $ttcode;
    }

    /**
     * Validate email by regular expression
     *
     * @param $email
     * @return bool
     */
    public static function checkEmail($email)
    {
        $ch1 = '[a-zA-Z0-9\-_\.]';
        $ch2 = '[a-zA-Z0-9\-_]';
        if (preg_match("/^$ch1+@$ch2+(\.$ch2+)+$/", $email) == 0) {
            return false;
        }
        return true;
    }

    public static function microtimestr()
    {
        return preg_replace('/[^0-9]/', "", microtime(true));
    }


    public static function SEOTranslite($str){
        $str = mb_strtolower($str);
        $str = preg_replace('/[^\w\d ]/ui','',$str);
        static $tbl= array(
            'а'=>'a', 'б'=>'b', 'в'=>'v', 'г'=>'g', 'д'=>'d', 'е'=>'e', 'ж'=>'g', 'з'=>'z',
            'и'=>'i', 'й'=>'y', 'к'=>'k', 'л'=>'l', 'м'=>'m', 'н'=>'n', 'о'=>'o', 'п'=>'p',
            'р'=>'r', 'с'=>'s', 'т'=>'t', 'у'=>'u', 'ф'=>'f', 'ы'=>'i', 'э'=>'e','ё'=>"yo", 'х'=>"h",
            'ц'=>"ts", 'ч'=>"ch", 'ш'=>"sh", 'щ'=>"shch", 'ъ'=>"", 'ь'=>"", 'ю'=>"yu", 'я'=>"ya",
            "'"=>"",' '=>'-','_'=>'-','.'=>'-',"\""=>"","?"=>"",":"=>""
        );


        return strtr($str, $tbl);
    }

    public static function mb_reduceText($text, $length = null)
    {
        $text = trim($text);
        $text = str_replace('<br>',' ',$text);
        $text = str_replace('<br/>',' ',$text);
        $text = str_replace('<br />',' ',$text);
        $text = strip_tags($text);

        if (mb_strlen($text) > $length && !is_null($length))
        {
            $retval ='';
            $tretval = '';
            $aText = explode(' ',$text);
            for($i =0;$i<count($aText);$i++){
                $tretval .= $aText[$i].' ';
                if(mb_strlen($tretval)>$length){
                    return trim($retval).'...';
                }else{
                    $retval = $tretval;
                }
            }

        }
        return $text;
    }



    public static function redirect($to, $code=302)
    {


        $location = null;
        $sn = $_SERVER['SCRIPT_NAME'];
        $cp = dirname($sn);
        if (substr($to, 0, 4)=='http')
            $location = $to; // Absolute URL
        else
        {
            $schema = $_SERVER['SERVER_PORT']=='443'?'https':'http';
            $host = strlen($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:$_SERVER['SERVER_NAME'];
            if (substr($to,0,1)=='/') $location = "$schema://$host$to";
            elseif (substr($to,0,1)=='.') // Relative Path
            {
                $location = "$schema://$host/";
                $pu = parse_url($to);
                $cd = dirname($_SERVER['SCRIPT_FILENAME']).'/';
                $np = realpath($cd.$pu['path']);
                $np = str_replace($_SERVER['DOCUMENT_ROOT'],'',$np);
                $location.= $np;
                if ((isset($pu['query'])) && (strlen($pu['query'])>0)) $location.= '?'.$pu['query'];
            }
        }

        $hs = headers_sent();
        if ($hs==false)
        {

            header("Location: $location",TRUE,$code);

        }

        exit(0);
    }



    /**
     * Displays default 404 page and sends 404 header.
     *
     */
    public static function show404()
    {
        //Utility::registerRequest(404);

        // show404
        header("HTTP/1.1 404 Not Found");
        //header("404 Not Found HTTP/1.1");

        $page = new Page();
        $view = new View();
        $page->setTitle('Ошибка 404: Страница не найдена');
        $page->setContent($view->render(TDR.'/404.php'));
        echo $page->renderpage();
        exit(0);
    }


    public static function getUserIP()
    {
        // Get real visitor IP behind CloudFlare network
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
            $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];

        if(filter_var($client, FILTER_VALIDATE_IP))
        {
            $ip = $client;
        }
        elseif(filter_var($forward, FILTER_VALIDATE_IP))
        {
            $ip = $forward;
        }
        else
        {
            $ip = $remote;
        }

        return $ip;
    }


    public static function isMobile() {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
    }



    /**
     * Formats datetime like that:
     * 15 Июн 2006, 15:23
     *
     * @param int $datetime
     * @return string
     */
    public static function formatDateTime8($datetime)
    {
        $result = '';
        $monthNames = array(
            1 => 'Января',
            2 => 'Февраля',
            3 => 'Марта',
            4 => 'Апреля',
            5 => 'Мая',
            6 => 'Июня',
            7 => 'Июля',
            8 => 'Августа',
            9 => 'Сентября',
            10 => 'Окттября',
            11 => 'Ноября',
            12 => 'Декабря'
        );

        $result = date('d ', $datetime).$monthNames[date('n', $datetime)].date(' Y', $datetime).date(', H:i', $datetime);
        return $result;
    }

    /**
     * Formats datetime like that:
     * 15 июня 2006, Понедельник
     *
     * @param string $datetime
     * @return string
     */
    public static function formatDateTime7($datetime)
    {

        $show_week_day  = true;
        $dt = strtotime($datetime);

        $days = array(
            '0' => 'Воскресенье',
            '1' => 'Понедельник',
            '2' => 'Вторник',
            '3' => 'Среда',
            '4' => 'Четверг',
            '5' => 'Пятница',
            '6' => 'Суббота');

        $months = array(
            '1'=>'января',
            '2'=>'февраля',
            '3'=>'марта',
            '4'=>'апреля',
            '5'=>'мая',
            '6'=>'июня',
            '7'=>'июля',
            '8'=>'августа',
            '9'=>'сентября',
            '10'=>'октября',
            '11'=>'ноября',
            '12'=>'декабря');
        return date("j",$dt).' '.$months[date("n",$dt)].' ' . date('Y', $dt).', '.($show_week_day ? $days[date('w', $dt)]  : '' );


    }


}