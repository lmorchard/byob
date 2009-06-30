<?=View::factory('repacks/elements/confirm', array(
    'repack'     => $repack,
    'head_title' => 'revert',
    'crumbs'     => 'revert release',
    'message'    => 'Revert the existing release for this browser?',
    'url'        => url::base() . url::current(),
))->render()?>
