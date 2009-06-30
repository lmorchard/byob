<?=View::factory('repacks/elements/confirm', array(
    'repack'     => $repack,
    'head_title' => 'release',
    'crumbs'     => 'request release',
    'message'    => 'Request a new release for this browser',
    'url'        => url::base() . url::current(),
))->render()?>
