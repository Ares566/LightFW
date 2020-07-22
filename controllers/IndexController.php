<?php

require_once 'MainController.php';

class IndexController extends MainController
{
    public function IndexAction()
    {
        //ini_set('display_errors',1);
        $view = new View();

        $title = 'title';
        $description = 'description';
        $this->renderpage($view->render(TDR . 'content/index.php'), $title, $description);
    }
}