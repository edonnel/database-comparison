<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300;400;700&family=Roboto+Mono:wght@400;700&family=Roboto:ital,wght@0,300;0,400;0,700;1,400&display=swap" rel="stylesheet">

<style>
    <? require_once(THIS_DIR.'/src/css/style.css') ?>
</style>

<div>

	<h1>Database Comparison</h1>

    <? $critical = alerts::are_critical(); ?>

    <?= alerts::get(); ?>

    <? if (!$critical) : ?>

	&nbsp;

	<div class="db-headers db-cols">
		<div class="db-header db-col">
			<h2>Staging</h2>
			<div class="monospace" style="font-size:16px; color:#262626;"><?= db_cred_stag['name'] ?></div>
		</div>

		<div class="db-header db-col">
			<h2>Production</h2>
			<div class="monospace" style="font-size:16px; color:#262626;"><?= db_cred_prod['name'] ?></div>
		</div>
	</div>

    <div class="section-tables db-cols">
        <div class="db-col">
            <? \database_comparison\render_tables($database_stag, $database_prod, 'table changes on staging', 'left'); ?>
        </div>
        <div class="db-col">
            <? \database_comparison\render_tables($database_prod, $database_stag, 'table changes on production', 'right'); ?>
        </div>
    </div>

	&nbsp;

    <div class="section-columns db-cols">
        <div class="db-col">
            <? \database_comparison\render_columns($database_stag, $database_prod, 'column changes on staging', 'left'); ?>
        </div>
        <div class="db-col">
            <? \database_comparison\render_columns($database_prod, $database_stag, 'column changes on production', 'right'); ?>
        </div>
    </div>

    &nbsp;

    <div class="section-indexes db-cols">
        <div class="db-col">
            <? \database_comparison\render_indexes($database_stag, $database_prod, 'index changes on staging', 'left'); ?>
        </div>
        <div class="db-col">
            <? \database_comparison\render_indexes($database_prod, $database_stag, 'index changes on production', 'right'); ?>
        </div>
    </div>

    &nbsp;

    <div class="section-constraints db-cols">
        <div class="db-col">
            <div class="table-container">
                <? \database_comparison\render_constraints($database_stag, $database_prod, 'constraint changes on staging', 'left'); ?>
            </div>
        </div>
        <div class="db-col">
            <div class="table-container">
                <? \database_comparison\render_constraints($database_prod, $database_stag, 'constraint changes on production', 'right'); ?>
            </div>
        </div>
    </div>

    &nbsp;

    <? if ($_SESSION['last_stmt']) : ?>
    <div class="last-stmt" style="padding:15px; background-color:#ececec;">
        <div><b>Last Successful Statement:</b></div>
        <pre><?= $_SESSION['last_stmt'] ?></pre>
    </div>
    <? endif; ?>

    <? endif; ?>

</div>

<script>
    <? require_once(THIS_DIR.'/src/js/javascript.js') ?>
</script>