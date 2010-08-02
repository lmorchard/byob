<?=View::factory('repacks/elements/confirm', array(
    'repack'     => $repack,
    'head_title' => _('revert'),
    'crumbs'     => _('revert release'),
    'message'    => _('Revert the existing release for this browser?'),
    'url'        => url::site(url::current()),
))->render()?>
