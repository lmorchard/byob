<?=View::factory('repacks/elements/confirm', array(
    'repack'     => $repack,
    'head_title' => 'cancel release :: ' . $repack->title,
    'page_title' => 'Cancel release for ' . $repack->title,
    'message'    => 'Cancel a new release for this browser?',
    'url'        => url::base() . url::current(),
))->render()?>
