<section class="section">
    <div class="container">
        <div class="columns">
            <div class="column is-6 is-offset-3">
                <h3 class="title is-4">
                    <a href="/" title="Go back to the read later list">home</a> / snooze all links
                </h3>                            
                <p class="content">
                    When would you like to start reading links again?
                    <?php if(\Hdz\ReadLater\Link::getEndOfSnooze() > time()) : ?>
                        Right now, you are already on a reading break till <?=date("l, j F, Y", \Hdz\ReadLater\Link::getEndOfSnooze())?>.
                    <?php endif; ?>
                </p>
                <form class="content" action="" method="post">
                    <div class="field has-addons">
                        <div class="control is-expanded">
                            <input class="input" type="date" name="date" min="<?=date('Y-m-d')?>" placeholder="Date">
                        </div>
                        <div class="control">
                            <button type="submit" class="button is-link">
                                Snooze
                            </button>
                        </div>
                    </div>
                </form>
                <?php if($_ENV['SHOW_SNOOZE_ALL_EXPLANATION']) : ?>
                    <p class="content">
                        This will shift all the links in your “read later” list backward by the number of days between now and the date you pick above. This is useful if, for example, you go on leave and offline for a few days. Nothing will stop you from ignoring your own break and read items from your list between now and the date you choose.
                    </p>
                    <p class="content">
                        The system will account for this break when adding links between now and the break period. So if you normally give yourself <?=$_ENV['DAYS_BEFORE_EXPIRY']?> days to read a link, and you snooze all links for a week today, then when you add a link in 4 days time, it will get an expiry of <?=$_ENV['DAYS_BEFORE_EXPIRY']?> + (7 - 4) = <?=$_ENV['DAYS_BEFORE_EXPIRY']+7-4?> days. This ensures that the links are still displayed in the order that you've added them.
                    </p>
                    <p>
                        It is possible to shorten your break. If you first pick a date four weeks ahead, all links will expire four weeks later. If you then pick a date one week ahead, all links will move forward three weeks.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
