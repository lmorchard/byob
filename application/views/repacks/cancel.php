<?=View::factory('repacks/elements/confirm', array(
    'repack'     => $repack,
    'head_title' => _('cancel'),
    'crumbs'     => _('cancel release'),
    'message'    => _('Cancel a new release for this browser?'),
    'url'        => url::site(url::current()),
))->render()?>
