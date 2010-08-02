<?php
$privs = $repack->checkPrivileges(array(
    'see_failed'
));
?>
<ul class="history">
    <?php foreach ($logevents as $event): ?>
        <?php 
            if ('modified' == $event->action) continue;
            if ('failed' == $event->action && !$privs['see_failed']) continue;
        ?>
        <?php
            $action_titles = array(
                'new'        => _('Created'),
                'edited'     => _('Modified'),
                'requested'  => _('Release requested'),
                'pending'    => _('Build completed'),
                'rejected'   => _('Release rejected'),
                'reverted'   => _('Release reverted'),
                'started'    => _('Build started'),
                'failed'     => _('Build failed'),
                'released'   => _('Release approved'),
                'deleted'    => _('Release deleted'),
                'cancelled'  => _('Release cancelled'),
                
                'created'         => _('Created'),
                'modified'        => _('Modified'),
                'requestRelease'  => _('Release requested'),
                'cancelRelease'   => _('Release request cancelled'),
                'rejectRelease'   => _('Release rejected'),
                'approveRelease'  => _('Release approved'),
                'beginRelease'    => _('Build started'),
                'failRelease'     => _('Build failed'),
                'finishRelease'   => _('Build completed'),
                'revertRelease'   => _('Release reverted'),
                 
            );
            $h = html::escape_array(array(
                'when' => gmdate('m/d/Y h:i A', strtotime($event->created)),
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
            <li class="event event-<?=$event->action?> clearfix">
            <div class="when"><?=$h['when']?></div>
            <p class="summary">
                <?=$h['what']?> 
                <?php if ($h['who']): ?>
                    <?=sprintf(_('by <a href="%1$s">%2$s</a>'), url::site('profiles/'.$u['who']), $h['who'])?>
                <?php endif ?>
            </p>
            <?php if ($h['why']): ?> 
                <p class="comments"><?=$h['why']?></p>
            <?php endif ?>
        </li>
    <?php endforeach ?>
</ul>
