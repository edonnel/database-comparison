<table class="table <?= $position ?> constraints-table">
	<thead>
		<tr>
			<td colspan="9999">
				<?= $header ?>

                <?/* if ($changed_constraints) : ?>
                    <div class="r">
                        &nbsp;
                        <input onclick="window.location='<?= THIS_URL ?>&act=push_all_constraints&db_src=<?= $db->get_name() ?>&db_dest=<?= $db_other->get_name() ?>'" type="button" value="Push All Constraints">
                    </div>
                <? endif; */?>
			</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="category">Table</td>
			<td class="category">Name</td>
			<td class="category">Column</td>
			<td class="category">Ref. DB</td>
			<td class="category">Ref. Table</td>
			<td class="category">Ref. Column</td>
            <td class="category">On Delete</td>
            <td class="category">On Update</td>
            <td class="category"></td>
            <td class="category"></td>
		</tr>
		<?
            if ($changed_constraints) {

                foreach ($changed_constraints as $changed_constraint) {

                    $constraint             = $changed_constraint->get_object();
                    $push_constraint_link   = THIS_URL.'&act=push_constraint'.'&db_src='.$db->get_name().'&db_dest='.$db_other->get_name().'&table='.$constraint->get_table_name().'&constraint='.$constraint->get_name();
                    $delete_constraint_link = THIS_URL.'&act=drop_constraint'.'&db='.$db_other->get_name().'&table='.$constraint->get_table_name().'&constraint='.$constraint->get_name();

                    if (!$changed_constraint->has_reason('dne')) {

                        $col_dne            = $changed_constraint->has_reason('col_dne');
                        $ref_col_dne        = $changed_constraint->has_reason('ref_col_dne');
                        $ref_table_dne      = $changed_constraint->has_reason('table_dne');
                        $ref_mne            = $changed_constraint->has_reason('ref_mne');
                        $diff_engine        = $changed_constraint->has_reason('diff_engine');

                        $class_name         = '';
                        $class_col          = '';
                        $class_ref_table    = '';
                        $class_ref_col      = '';
                        $class_delete       = '';
                        $class_update       = '';

                        $title_name         = '';
                        $title_col          = '';
                        $title_ref_table    = '';
                        $title_ref_col      = '';
                        $title_delete       = '';
                        $title_update       = '';

                        if ($changed_constraint->has_reason('new')) {
                            $class_name = 'changed';
                            $title_name = 'Constraint does not exist of other database.';
                        }

                        if ($col_dne) {
                            $class_col = 'bad';
                            $title_col = 'Column does not exist on other database.';
                        } elseif ($changed_constraint->has_reason('col')) {
                            $class_col = 'changed';
                            $title_col = 'Column is different on other database.';
                        }

                        if ($changed_constraint->has_reason('delete')) {
                            $class_delete = 'changed';
                            $title_delete = 'Delete rule is different on other database table.';
                        }

                        if ($changed_constraint->has_reason('update')) {
                            $class_update = 'changed';
                            $title_update = 'Update rule is different on other database table.';
                        }

                        if ($ref_mne) {
                            $class_ref_table = 'meh';
                            $title_ref_table = 'Table might not exist on other database.';

                            $class_ref_col = 'meh';
                            $title_ref_col = 'Column might not exist on other database.';
                        } else {
                            if ($ref_table_dne) {
                                $class_ref_table = 'bad';
                                $title_ref_table = 'Table does not exist on other database.';
                            } elseif ($changed_constraint->has_reason('ref_table')) {
                                $class_ref_table = 'changed';
                                $title_ref_table = 'Table is different on other database.';
                            }

                            if ($ref_col_dne) {
                                $class_ref_col = 'bad';
                                $title_ref_col = 'Referenced column does not exist on other database table.';
                            } elseif ($changed_constraint->has_reason('ref_col')) {
                                $class_ref_col = 'changed';
                                $title_ref_col = 'Referenced column is different on other database table.';
                            }
                        }

                        ?>
                            <tr class="<?= $changed_constraint->has_reason('new') ? 'new' : 'diff' ?>">
                                <td><?= $constraint->get_table_name() ?></td>
                                <td class="<?= $class_name ?>" title="<?= $title_name ?>"><b><?= $constraint->get_name() ?></b></td>
                                <td class="<?= $class_col ?>" title="<?= $title_col ?>"><?= $constraint->get_col_name() ?></td>
                                <td class="<?= $changed_constraint->has_reason('ref_db') ? 'changed' : '' ?>"><?= $constraint->get_ref_db_name() ?></td>
                                <td class="<?= $class_ref_table ?>" title="<?= $title_ref_table ?>"><?= $constraint->get_ref_table_name() ?></td>
                                <td class="<?= $class_ref_col ?>" title="<?= $title_ref_col ?>"><?= $constraint->get_ref_col_name() ?></td>
                                <td class="<?= $class_delete ?>" title="<?= $title_delete ?>"><?= $constraint->get_delete_rule() ?></td>
                                <td class="<?= $class_update ?>" title="<?= $title_update ?>"><?= $constraint->get_update_rule() ?></td>
                                <td <?= $diff_engine ? 'style="color:red;" title="Other database table doesn\'t use InnoDB"' : '' ?>>
                                    <? if ($diff_engine) : ?>
                                        <i class="fa fa-car" aria-hidden="true"></i>
                                    <? endif; ?>
                                </td>
                                <td>
                                    <? if (!$ref_mne && !$col_dne && !$ref_col_dne && !$ref_table_dne && !$diff_engine) : ?>
                                    <a class="push-link" href="<?= $push_constraint_link ?>">Push Constraint</a>
                                    <? endif; ?>
                                </td>
                            </tr>
                        <?
                    } else {
                        ?>
                            <tr class="no">
                                <td colspan="9">&nbsp;</td>
                                <td>
                                    <a class="push-link red" href="<?= $delete_constraint_link ?>">Drop Constraint</a>
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