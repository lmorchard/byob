<?php if ('live' == $bookmark['type']): ?>
item.<?= $idx ?>.type=livemark
item.<?= $idx ?>.title=<?= $bookmark['title'] . "\n" ?>
item.<?= $idx ?>.siteLink=<?= $bookmark['location'] . "\n" ?>
item.<?= $idx ?>.feedLink=<?= $bookmark['feed'] . "\n" ?>
<?php else: ?>
item.<?= $idx ?>.type=bookmark
item.<?= $idx ?>.title=<?= $bookmark['title'] . "\n" ?>
item.<?= $idx ?>.link=<?= $bookmark['location'] . "\n" ?>
<?php if (!empty($bookmark['description'])): ?>
item.<?= $idx ?>.description=<?= $bookmark['description'] . "\n" ?>
<?php endif ?>
<?php endif ?>
