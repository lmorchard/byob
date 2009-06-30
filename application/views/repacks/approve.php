<?=View::factory('repacks/elements/confirm', array(
    'repack'     => $repack,
    'head_title' => 'approve',
    'crumbs'     => 'approve release',
    'message'    => 'Approve a new release for this browser',
    'url'        => url::base() . url::current(),
))->render()?>
