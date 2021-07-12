<table class="table tables <?= $position ?> condensed">
	<thead>
		<tr>
			<td colspan="9999">
				<?= $header ?>

                <?/* if ($changed_tables) : ?>
                    <div class="r">
                        &nbsp;
                        <input onclick="window.location='<?= THIS_URL ?>&act=push_all_tables&db_src=<?= $db->get_name() ?>&db_dest=<?= $db_other->get_name() ?>'" type="button" value="Push All Tables">
                    </div>
                <? endif; */?>
			</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="category">Name</td>
			<td class="category">Engine</td>
            <td class="category">Charset</td>
			<td class="category"></td>
		</tr>
        <?
            if ($changed_tables) {

                foreach ($changed_tables as $changed_table) {

                    $table              = $changed_table->get_object();
                    $push_table_link   = THIS_URL.'&act=push_table'.'&db_src='.$db->get_name().'&db_dest='.$db_other->get_name().'&table='.$table->get_name();
                    $delete_table_link = THIS_URL.'&act=drop_table'.'&db='.$db_other->get_name().'&table='.$table->get_name();

                    if (!$changed_table->has_reason('dne')) {

                        ?>
                        <tr class="<?= $changed_table->has_reason('new') ? 'new' : 'diff' ?>">
                            <td><?= $table->get_name() ?></td>
                            <td class="<?= $changed_table->has_reason('engine') ? 'changed' : '' ?>"><?= $table->get_engine() ?></td>
                            <td class="<?= $changed_table->has_reason('charset') ? 'changed' : '' ?>"><?= $table->get_charset() ?></td>
                            <td>
                                <? if ($changed_table->has_reason('new') || $changed_table->has_reason('engine')) : ?>
                                    <a class="push-link" href="<?= $push_table_link ?>">Push table</a>
                                <? endif; ?>
                            </td>
                        </tr>
                        <?
                    } else {
                        ?>
                        <tr class="no">
                            <td colspan="3">&nbsp;</td>
                            <td>
                                <a class="push-link red" href="<?= $delete_table_link ?>">Drop table</a>
                            </td>
                        </tr>
                        <?
                    }
                }
            } else {
                ?>
                <tr>
                    <td colspan="9999"><i>None</i></td>
                </tr>
                <?
            }
        ?>
	</tbody>
</table>