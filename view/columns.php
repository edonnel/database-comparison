<table class="table <?= $position ?> columns-table condensed">
	<thead>
		<tr>
			<td colspan="9999">
				<?= $header ?>

                <?/* if ($changed_columns) : ?>
                    <div class="r">
                        &nbsp;
                        <input onclick="window.location='<?= THIS_URL ?>&act=push_all_columns&db_src=<?= $db->get_name() ?>&db_dest=<?= $db_other->get_name() ?>'" type="button" value="Push All Columns">
                    </div>
                <? endif; */?>
			</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="category">Table</td>
			<td class="category">Name</td>
			<td class="category">Type</td>
            <td class="category">Null</td>
            <td class="category">Default</td>
            <td class="category">Auto Increment</td>
			<td class="category"></td>
		</tr>
		<?
			if ($changed_columns) {

				foreach ($changed_columns as $changed_column) {

					$column             = $changed_column->get_object();
					$push_column_link   = THIS_URL.'&act=push_column'.'&db_src='.$db->get_name().'&db_dest='.$db_other->get_name().'&table='.$column->get_table_name().'&column='.$column->get_name();
					$delete_column_link = THIS_URL.'&act=drop_column'.'&db='.$db_other->get_name().'&table='.$column->get_table_name().'&column='.$column->get_name();

					if (!$changed_column->has_reason('dne')) {
					    if ($column->get_default() == null) {
					        if ($column->is_null())
					            $default = 'NULL';
					        else
					            $default = '<i>None</i>';
                        } else
                            $default = $column->get_default();
						?>
						<tr class="<?= $changed_column->has_reason('new') ? 'new' : 'diff' ?>">
							<td><?= $column->get_table_name() ?></td>
							<td><b><?= $column->get_name() ?></b></td>
							<td class="<?= $changed_column->has_reason('type') ? 'changed' : '' ?>"><?= $column->get_type() ?></td>
                            <td class="<?= $changed_column->has_reason('null') ? 'changed' : '' ?>"><?= $column->is_null() ? 'Yes' : 'No' ?></td>
                            <td class="<?= $changed_column->has_reason('default') ? 'changed' : '' ?>"><?= $default ?></td>
                            <td class="<?= $changed_column->has_reason('auto_increment') ? 'changed' : '' ?>"><?= $column->is_auto_increment() ? 'Yes' : 'No' ?></td>
                            <td>
								<? if ($changed_column->has_reason('new') || $changed_column->has_reason('type') || $changed_column->has_reason('null') || $changed_column->has_reason('default') || $changed_column->has_reason('auto_increment')) : ?>
									<a class="push-link" href="<?= $push_column_link ?>">Push column</a>
								<? endif; ?>
							</td>
						</tr>
						<?
					} else {
						?>
						<tr class="no">
							<td colspan="6">&nbsp;</td>
							<td>
								<a class="push-link red" href="<?= $delete_column_link ?>">Drop column</a>
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
