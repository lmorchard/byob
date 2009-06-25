<ul class="history">
    <?php foreach ($logevents as $event): ?>
        <?php
            $action_titles = array(
                'created'         => 'Created',
                'modified'        => 'Modified',
                'requestRelease'  => 'Release requested',
                'cancelRelease'   => 'Release request cancelled',
                'rejectRelease'   => 'Release rejected',
                'approveRelease'  => 'Release approved',
                'beginRelease'    => 'Build started',
                'failRelease'     => 'Build failed',
                'finishRelease'   => 'Build completed',
                'revertRelease'   => 'Release reverted',
            );
            $h = html::escape_array(array(
                'when' => $event->created,
                'what' => $action_titles[$event->action],
                'who'  => (null===$event->profile_id) ? 
                    null : $event->profile->screen_name,
                'why'  => (empty($event->details) || 
                    in_array($event->action, array('created', 'modified'))) ?
                        null : $event->details
            ));
            $u = html::urlencode_array(array(
                'who'  => (null===$event->profile_id) ? 
                    null : $event->profile->screen_name,
            ));
        ?>
        <li class="event">
            <span class="summary">
                <?=$h['when']?>: <?=$h['what']?> 
                <?php if ($h['who']): ?>
                    by <a href="<?=url::base().'profiles/'.$u['who']?>"><?=$h['who']?></a>
                <?php endif ?>
            </span>
            <?php if ($h['why']): ?> 
                <p class="comments"><?=$h['why']?></p>
            <?php endif ?>
        </li>
    <?php endforeach ?>
</ul>
