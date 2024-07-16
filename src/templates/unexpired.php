<?php if($_ENV['SHOW_ADD_LINK_FORM']) : ?>
                <form class="content" action="/link/add" method="get">
                    <div class="field has-addons">
                        <div class="control has-icons-left is-expanded">
                            <input class="input" type="url" name="url" placeholder="URL of the link you want to add">
                            <span class="icon is-small is-left">
                                <i class="fa-solid fa-globe"></i>
                            </span>
                        </div>
                        <div class="control">
                            <button type="submit" class="button is-link">
                                Add
                            </button>
                        </div>
                    </div>
                </form>
<?php endif; ?>
<?php if (empty($links)) : ?>
                            <p class="content has-text-centered">
                                You have read all there is to read.
                            </p>
<?php else : ?>
    <script>
        function refreshParent() {
            setTimeout(function() {
                window.location.reload();
            }, 100);
        }
    </script>
    <?php foreach ($links as $key => $link) : ?>
                                <article class="media">
                                    <div class="media-content">
                                        <div class="content">
                                            <p>
                                                <?php if($_ENV['FORCE_READ_FIRST'] && $key != 0) : ?>
                                                    <?=$link['title']?>
                                                <?php else : ?>
                                                    <a class="has-text-weight-bold" target="_blank" title="Read this" href="/link/<?=$link['id']?>/go" onClick="refreshParent()"><?=$link['title']?></a>
                                                <?php endif; ?>
                                                <br>
                                                <span class="has-text-grey">
                                                    <?=$link['domain']?>
                                                    <span class="is-size-7">
                                                    <?php if ($_ENV['SHOW_EXPIRY_TIME']) : ?>
                                                        <span class="icon-text">
                                                            <span class="pl-1">
                                                                &bull;
                                                            </span>
                                                        </span>
                                                        <span class="icon-text">
                                                            <span class="pl-1">
                                                                <?=$link['expiryString']?> to go
                                                            </span>
                                                        </span>
                                                    <?php endif; ?>
                                                        <span class="icon-text">
                                                            <span class="pl-1">
                                                                &bull;
                                                            </span>
                                                        </span>
                                                        <a title="Snooze for another <?=$_ENV['DAYS_PER_SNOOZE']?> days" href="/link/<?=$link['id']?>/snooze" class="icon-text is-size-7 has-text-right">
                                                            <span class="icon">
                                                                <i class="fa-regular fa-calendar-plus"></i>
                                                            </span>
                                                            <span style="margin-left:-6px">
                                                                <?=($link['snoozes'] > 0) ? "&nbsp;(" . $link['snoozes'] . ")" : ''?>
                                                            </span>
                                                        </a>
                                                        <span class="icon-text">
                                                            <span>
                                                                &bull;
                                                            </span>
                                                        </span>
                                                        <a title="Expire this link" href="/link/<?=$link['id']?>/expire" class="icon-text is-size-7 has-text-right">
                                                            <span class="icon">
                                                                <i class="fa-regular fa-calendar-xmark"></i>
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
