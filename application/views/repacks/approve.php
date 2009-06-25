<?=View::factory('repacks/elements/confirm', array(
    'repack'     => $repack,
    'head_title' => 'approve release :: ' . $repack->title,
    'page_title' => 'Approve release for ' . $repack->title,
    'message'    => 'Approve a new release for this browser',
    'url'        => url::base() . url::current(),
))->render()?>
