<?php slot::start('preamble') ?>
    <div>
        <p><?=_('Abandoning changes will delete the current set of modifications, and cannot be undone. If these changes are to an existing release, that release will still be available. If the changes are for an unreleased browser, the browser will be deleted. If you wish to cancel this request, click on the "No" button below. If you wish to continue, enter a short reason for abandoning the changes to the release and click on the "Yes" button.')?></p>
    </div>
<?php slot::end() ?>
<?=View::factory('repacks/elements/confirm', array(
    'repack'     => $repack,
    'head_title' => _('delete'),
    'crumbs'     => _('delete browser'),
    'message'    => ($repack->isRelease()) ?
        _('Delete this browser?') :
        _('Abandon changes to this browser?'),
    'url'        => url::site(url::current()),
))->render()?>
