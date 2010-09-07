<?php
$url = url::site('profiles/' . $row->profile->screen_name);
$h = html::escape_array(array(
    'url'  => $url,
    'name' => $row->profile->screen_name
));
?>
<td><a href="<?=$h['url']?>"><?=$h['name']?></a></td>
