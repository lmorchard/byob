<?=View::factory('repacks/elements/confirm', array(
    'repack'     => $repack,
    'head_title' => 'reject release :: ' . $repack->title,
    'page_title' => 'Reject release for ' . $repack->title,
    'message'    => 'Reject a new release for this browser',
    'url'        => url::base() . url::current(),
))->render()?>
