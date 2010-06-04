<?=View::factory('repacks/elements/confirm', array(
    'repack'     => $repack,
    'head_title' => 'make private',
    'crumbs'     => 'make private',
    'message'    => 'Hide this browser from public lists?',
    'url'        => url::base() . url::current(),
    'comments'   => false,
))->render()?>
