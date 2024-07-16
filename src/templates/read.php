<h3 class="title is-4">
    <a href="/" title="Go back to the read later list">home</a> / read
</h3>                            
<?php if (empty($links)) : ?>
<p class="content">
    You haven't read anything yet.
</p>
<?php else : ?>
<?php foreach ($links as $link) : ?>
<article class="media">
    <div class="media-content">
        <div class="content">
            <p>
                <a class="has-text-weight-bold" target="_blank" title="Read this" href="<?=$link['url']?>"><?=$link['title']?></a>
                <br>
                <span class="has-text-grey">
                    <?=$link['domain']?>
                    <span class="is-size-7">
                        <span class="icon-text">
                            <span class="pl-1">
                                &bull;
                            </span>
                        </span>
                    <a title="Mark as unread and set the expiry date for another <?=$_ENV['DAYS_BEFORE_EXPIRY']?> days" href="/link/<?=$link['id']?>/reread" class="icon-text is-size-7 has-text-right">
                            <span class="icon">
                                <i class="fa-solid fa-rotate-right"></i>
                            </span>
                        </a>
                        <span class="icon-text">
                            <span>
                                &bull;
                            </span>
                        </span>
                        <a title="Delete this link" href="/link/<?=$link['id']?>/delete/read" class="icon-text is-size-7 has-text-right">
                            <span class="icon">
                                <i class="fa-solid fa-trash"></i>
                            </span>
                        </a>
                    </span>
                </span>
            </p>
        </div>
    </div>
</article>
<?php endforeach; ?>
<?php endif; ?>
