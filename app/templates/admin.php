<html>
    <head>
        <title>Admin</title>
        <?= helpers::includeCss(array('default', 'admin'))?>
         <link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
          <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
          <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
 
    
    </head>
    <body id='<?= METHOD ?>'>
        <? include('_admin_menu.php') ?>
        <div id='content'>
            <?= $content ?>
        </div>
    </body>
</html>
