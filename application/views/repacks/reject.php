<?=View::factory('repacks/elements/confirm', array(
    'repack'     => $repack,
    'head_title' => _('reject'),
    'crumbs'     => _('reject release'),
    'message'    => _('Reject a new release for this browser'),
    'url'        => url::site(url::current()),
))->render()?>
