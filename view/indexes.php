<table class="table <?= $position ?> indexes-table">
	<thead>
		<tr>
			<td colspan="9999">
				<?= $header ?>

                <?/* if ($changed_indexes) : ?>
                    <div class="r">
                        &nbsp;
                        <input onclick="window.location='<?= THIS_URL ?>&act=push_all_indexes&db_src=<?= $db->get_name() ?>&db_dest=<?= $db_other->get_name() ?>'" type="button" value="Push All Indexes">
                    </div>
                <? endif; */?>
			</td>
		</tr>
	</thead>
	<tbody>
		<tr>
            <td class="category">Table</td>
			<td class="category">Name</td>
			<td class="category">Unique</td>
			<td class="category">Column</td>
			<td class="category">Type</td>
			<td class="category"></td>
		</tr>
		<?
            if ($changed_indexes) {

                foreach ($changed_indexes as $changed_index) {

                    $index = $changed_index->get_object();
                    $push_index_link = THIS_URL.'&act=push_index&table='.$index->get_table_name().'&index='.$index->get_name().'&db_src='.$db->get_name().'&db_dest='.$db_other->get_name();
                    $delete_index_link = THIS_URL.'&act=drop_index&table='.$index->get_table_name().'&index='.$index->get_name().'&db='.$db_other->get_name();

                    if (!$changed_index->has_reason('dne')) {

                        if ($changed_index->has_reason('new'))
                            $class = 'new';
                        else
                            $class = 'diff';

                        $class_column = '';

                        ?>
                            <tr class="<?= $class ?>">
                                <td><?= $index->get_table_name() ?></td>
                                <td><b><?= $index->get_name(); ?></b></td>
                                <td class="<?= $changed_index->has_reason('unique') ? 'changed' : '' ?>"><?= $index->is_unique() ? 'yes' : 'no' ?></td>
                                <td>
                                    <span>
                                        <? foreach ($index->get_columns() as $index_column) : ?>
                                            <? $other_table_has_column = $db_other->get_table($index->get_table_name())->has_column($index_column->get_name()); ?>
                                            <div class="monospace <?= !$other_table_has_column ? 'bad' : '' ?> <?= $changed_index->has_reason('col') ? 'changed' : '' ?>" title="<?= !$other_table_has_column ? 'Column does not exist on other database table.' : '' ?>"><?= $index_column->get_name(); ?></div>
                                        <? endforeach; ?>
                                    </span>
                                </td>
                                <td class="monospace <?= $changed_index->has_reason('type') ? 'changed' : '' ?>"><?= $index->get_type(); ?></td>
                                <td>
                                    <? if (!$changed_index->has_reason('col_dne')) : ?>
                                    <a class="push-link" href="<?= $push_index_link ?>">Push Index</a>
                                    <? endif; ?>
                                </td>
                            </tr>
                        <?

                    } else {
                        ?>
                            <tr class="no">
                                <td colspan="5"></td>
                                <td>
                                    <a class="push-link red" href="<?= $delete_index_link ?>">Drop Index</a>
                                </td>
                            </tr>
                        <?
                    }
                }

            } else { ?>
				<tr>
					<td colspan="9999"><i>None</i></td>
				</tr>
			<? } ?>
	</tbody>
</table>