<div class="section">
    <h2>Mass approvals</h2>

    <?php if (!empty($not_found)): ?>
        <div class="msg">
            <h3>Could not find repacks for these items:</h3>
            <textarea cols="70" rows="4"><?=html::specialchars(join(' ', $not_found))?></textarea>
        </div>
    <?php endif ?>

    <?php if (!empty($rejects)): ?>
        <div class="msg">
            <h3>Approvals <em>not</em> queued for these repacks:</h3>
            <table>
                <tr>
                    <th>State</th>
                    <th>Screen name</th>
                    <th>Display title</th>
                </tr>
                <?php foreach ($rejects as $repack): ?>
                    <tr>
                        <td><?=html::specialchars($repack->getStateName())?></td>
                        <td><?=html::specialchars($repack->profile->screen_name)?></td>
                        <td><a href="<?=$repack->url()?>"><?=html::specialchars($repack->display_title)?></a></td>
                    </tr>
                <?php endforeach ?>
            </table>
        </div>
    <?php endif ?>

    <?php if (!empty($repacks)): ?>
        <div class="msg">
            <h3>Approvals queued for these repacks:</h3>
            <table>
                <tr>
                    <th>State</th>
                    <th>Screen name</th>
                    <th>Display title</th>
                </tr>
                <?php foreach ($repacks as $repack): ?>
                    <tr>
                        <td><?=html::specialchars($repack->getStateName())?></td>
                        <td><?=html::specialchars($repack->profile->screen_name)?></td>
                        <td><a href="<?=$repack->url()?>"><?=html::specialchars($repack->display_title)?></a></td>
                    </tr>
                <?php endforeach ?>
            </table>
        </div>
    <?php endif ?>

    <p>
        This page allows you to approve multiple repacks all at once,
        identified by a naming convention of <code>{screen_name}_{short_name}</code>
        or just <code>{short_name}</code>.  These repacks must have completed 
        building and be waiting for approval.
    </p>
    <p>
        These identifiers are most easily obtained by listing the contents of
        the <code>downloads/private</code> directory after a mass rebuild in
        response to a product update.  For example:
    </p>
    <pre>
        drolnitzky_EyMzQ1MDY1OQ  lmorchard_YyNjU5MTEyOA   lmorchard_k0NDU2NzUyMQ
        admin_E3Njg1NDA4NQ       lmorchard_AxMTcwNDQzMA   lmorchard_cwMTA3MjM3MQ
    </pre>
    <form method="POST">
        <p>Copy and paste a whitespace-delimited list of identifiers here:</p>
        <textarea cols="70" rows="10" name="repack_txt"></textarea>
        <p>Supply an optional approval comment here:</p>
        <textarea cols="70" rows="2" name="comment"></textarea>
        <p><input type="submit" name="start" value="Approve repacks" /></p>
    </form>

</div>
