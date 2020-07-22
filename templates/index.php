<!DOCTYPE html>
<html lang="ru">
<head>

    <meta charset="utf-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
   
    <meta id="viewport" name="viewport" content="width=device-width, initial-scale=1">

    <?
        $this->page_description = str_replace("\"",'',$this->page_description);
        $this->title = str_replace("\"",'',$this->title);

    ?>
    <title><?=$this->title?></title>
    <?if($this->is_noindex){?>
        <meta name="robots" content="noindex">
    <?}?>
    <meta name="description"
          content="<?=$this->page_description?>"/>
    <meta property="og:locale" content="ru_RU"/>
    <meta property="og:type" content="website"/>
    <meta property="og:title" content="<?=$this->title?>"/>
    <meta property="og:description"
          content="<?=$this->page_description?>"/>

    <? if($this->page_image){?>
    <meta property="og:image" content="<?=$this->page_image?>" />
    <?}?>

    <link rel="stylesheet" href="/css/main.css?ver=061219.1">



</head>
<body>

    <div id="app">
        <?=$this->content;?>
    </div>
</body>
</html>
