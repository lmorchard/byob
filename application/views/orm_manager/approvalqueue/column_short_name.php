<?php
$url = $row->url();
$h = html::escape_array(array(
    'url'  => $url,
    'name' => $row->short_name,
));
?>
<td><a href="<?=$h['url']?>"><?=$h['name']?></a></td>

