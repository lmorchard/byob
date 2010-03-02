<div class="section">
    <h2>Rebuild all repacks</h2>

    <?php if (!empty($results['pending'])): ?>
        <div class="msg">
            <p>Skipped these repacks, which had changes in progress:</p>
            <ul>
                <?php foreach ($results['pending'] as $repack): ?>
                <li><a href="<?=$repack->url()?>"><?= $repack->profile->screen_name . ' - ' . $repack->short_name ?></a></li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endif ?>

    <?php if (!empty($results['locked'])): ?>
        <div class="msg">
            <p>Skipped these repacks, which were locked for changes and probably already queued for build:</p>
            <ul>
                <?php foreach ($results['locked'] as $repack): ?>
                <li><a href="<?=$repack->url()?>"><?= $repack->profile->screen_name . ' - ' . $repack->short_name ?></a></li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endif ?>

    <?php if (!empty($results['rebuilding'])): ?>
        <div class="msg">
            <p>Scheduled rebuild for the following repacks:</p>
            <ul>
                <?php foreach ($results['rebuilding'] as $repack): ?>
                <li><a href="<?=$repack->url()?>"><?= $repack->profile->screen_name . ' - ' . $repack->short_name ?></a></li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endif ?>

    <p>
        This page allows you to schedule a rebuild of all currently released 
        repacks, mainly useful when a new product version is released.

        <b><i>Since this has an irreversible effect on all repacks, think twice 
        before pressing this button.</i></b>
    </p>

    <form method="POST" onsubmit="javascript:return confirm('Rebuild all releases, are you sure?')">
        <input type="submit" name="start" value="Rebuild all released repacks (<?=$repack_count?>)" />
    </form>

</div>
