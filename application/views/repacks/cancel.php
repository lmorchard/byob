<?=View::factory('repacks/elements/confirm', array(
    'repack'     => $repack,
    'head_title' => 'cancel',
    'crumbs'     => 'cancel release',
    'message'    => 'Cancel a new release for this browser?',
    'url'        => url::base() . url::current(),
))->render()?>
