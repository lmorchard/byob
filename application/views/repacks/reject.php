<?=View::factory('repacks/elements/confirm', array(
    'repack'     => $repack,
    'head_title' => 'reject',
    'crumbs'     => 'reject release',
    'message'    => 'Reject a new release for this browser',
    'url'        => url::base() . url::current(),
))->render()?>
