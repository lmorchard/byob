<?=View::factory('repacks/elements/confirm', array(
    'repack'     => $repack,
    'head_title' => _('approve'),
    'crumbs'     => _('approve release'),
    'message'    => _('Approve a new release for this browser'),
    'url'        => url::site(url::current()),
))->render()?>
