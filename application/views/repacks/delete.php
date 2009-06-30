<?=View::factory('repacks/elements/confirm', array(
    'repack'     => $repack,
    'head_title' => 'delete',
    'crumbs'     => 'delete browser',
    'message'    => ($repack->isRelease()) ?
        'Delete this browser?' :
        'Abandon changes to this browser?',
    'url'        => url::base() . url::current(),
))->render()?>
