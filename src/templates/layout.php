<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?=$title?></title>
        <link rel="stylesheet" href="/css/bulma.min.css">
        <link rel="stylesheet" href="/css/fontawesome.min.css">
        <link rel="stylesheet" href="/css/solid.min.css">
        <link rel="stylesheet" href="/css/regular.min.css">
        <link rel="stylesheet" href="/css/custom.css?v=<?=time()?>">
        <link rel="icon" type="image/svg+xml" href="/tsunami.svg">
    </head>
    <body>
        <?=$body_content?>
        <div class="content has-text-centered is-size-6 mb-5">
            <?php if($_ENV['SHOW_BOOKMARKLET'] && \Hdz\ReadLater\Security::isLoggedIn()) : ?> 
                <p><a href="javascript:(function(){var url=encodeURIComponent(location.href);var title=encodeURIComponent(document.title);location.href='<?=$_ENV['BASE_URL']?>/link/add?url='+url+'&title='+title;})();">read later</a> ← Drag this link to your bookmarks</p>
            <?php endif ?>
            <?php if($_ENV['SHOW_CREATOR']) : ?> 
                <p>‘Opinionated Read Later’ by <a target="_blank" href="https://www.hansdezwart.nl">Hans de Zwart</a></p>
            <?php endif ?>
            <?php
                if ($_ENV['SHOW_READ_LINK']) {
                    $menu[] = '<a href="/read">read</a>';
                }
                if ($_ENV['SHOW_EXPIRED_LINK']) {
                    $menu[] = '<a href="/expired">expired</a>';
                }
                $menu[] = '<a href="/snooze/all">snooze all</a>';
                if ($_ENV['SHOW_SIGN_OUT']) {
                    $menu[] = '<a href="/logout">sign out</a>';
                }
                $menu = implode(' &bull; ', $menu);
            ?>
            <?php if(!empty($menu) && \Hdz\ReadLater\Security::isLoggedIn()) : ?> 
                <p><?=$menu?></p>
            <?php endif ?>
        </div>
    </body>
</html>
