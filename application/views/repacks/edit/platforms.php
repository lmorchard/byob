<div>

    <p>You can choose the operating systems for which your browser
    will be made available.</p>

    <div>
        <?php
            $osen = form::value('os');
            if (empty($osen)) $osen = array();
        ?>
        <label for="os[]">Operating Systems:</label>
        <ul class="repack-os">
            <?php foreach (Repack_Model::$os_choices as $name=>$label): ?>
                <li>
                    <?= form::checkbox("os[]", $name, in_array($name, $osen)) ?>
                    <?= html::specialchars($label) ?>
                </li>
            <?php endforeach ?>
        </ul>
    </div>

</div>
