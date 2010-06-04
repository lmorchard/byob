<?=View::factory('repacks/elements/confirm', array(
    'repack'     => $repack,
    'head_title' => 'make public',
    'crumbs'     => 'make public',
    'message'    => 'Show this browser in public lists?',
    'url'        => url::base() . url::current(),
    'comments'   => false,
))->render()?>
