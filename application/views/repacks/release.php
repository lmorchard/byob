<?=View::factory('repacks/elements/confirm', array(
    'repack'     => $repack,
    'head_title' => 'request release :: ' . $repack->title,
    'page_title' => 'Request release for ' . $repack->title,
    'message'    => 'Request a new release for this browser',
    'url'        => url::base() . url::current(),
))->render()?>
