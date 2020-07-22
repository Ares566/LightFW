<?

    define('RTR_PATH',__DIR__.'/');
    define('TDR', RTR_PATH.'templates/');


    try{
        // TODO: add page caching
        include_once 'include.php';

        $PAGE = new Page();
        $oRouter = new Router();

        $oRouter->addRoute('/','Index','Index');

        $oRouter->route();

        echo $PAGE->renderpage();

    }catch (Exception $e){
        //TODO: logging exception
        throw new Exception('Нет контроллера');

    }
