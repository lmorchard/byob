<?=View::factory('repacks/elements/confirm', array(
    'repack'     => $repack,
    'head_title' => _('make public'),
    'crumbs'     => _('make public'),
    'message'    => _('Show this browser in public lists?'),
    'url'        => url::site(url::current()),
    'comments'   => false,
))->render()?>
