<?=View::factory('repacks/elements/confirm', array(
    'repack'     => $repack,
    'head_title' => _('make private'),
    'crumbs'     => _('make private'),
    'message'    => _('Hide this browser from public lists?'),
    'url'        => url::site(url::current()),
    'comments'   => false,
))->render()?>
