<?=View::factory('repacks/elements/confirm', array(
    'repack'     => $repack,
    'head_title' => 'revert release :: ' . $repack->title,
    'page_title' => 'Revert release for ' . $repack->title,
    'message'    => 'Revert the existing release for this browser?',
    'url'        => url::base() . url::current(),
))->render()?>
