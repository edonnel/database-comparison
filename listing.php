<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300;400;700&family=Roboto+Mono:wght@400;700&display=swap" rel="stylesheet">

<style>
    <? require_once(THIS_DIR.'/src/css/style.css') ?>
</style>

<script>
    <? require_once(THIS_DIR.'/src/js/javascript.js') ?>
</script>

<div>
	<? show_messages(); ?>

	<h1>Database Comparison</h1>

	&nbsp;

	<div style="margin-top:10px;">
		<div class="w_50 l p_r p_10">
			<h2>Staging</h2>
			<div class="monospace" style="font-size:16px; color:#262626;"><?= $db_stag['name'] ?></div>
		</div>

		<div class="w_50 l p_l p_10">
			<h2>Production</h2>
			<div class="monospace" style="font-size:16px; color:#262626;"><?= $db_prod['name'] ?></div>
		</div>
	</div>
	<div class="c"></div>

    <div class="section-tables" style="margin-top:10px;">
        <div class="w_50 l p_r p_10">
            <? render_tables($database_stag, $database_prod, 'table changes on staging', 'left'); ?>
        </div>
        <div class="w_50 l p_l p_10">
            <? render_tables($database_prod, $database_stag, 'table changes on production', 'right'); ?>
        </div>
    </div>
    <div class="c"></div>

	&nbsp;

    <div class="section-columns" style="margin-top:10px;">
        <div class="w_50 l p_r p_10">
            <? render_columns($database_stag, $database_prod, 'column changes on staging', 'left'); ?>
        </div>
        <div class="w_50 l p_l p_10">
            <? render_columns($database_prod, $database_stag, 'column changes on production', 'right'); ?>
        </div>
    </div>
    <div class="c"></div>

    &nbsp;

    <div class="section-indexes" style="margin-top:10px;">
        <div class="w_50 l p_r p_10">
            <? render_indexes($database_stag, $database_prod, 'index changes on staging', 'left'); ?>
        </div>
        <div class="w_50 l p_l p_10">
            <? render_indexes($database_prod, $database_stag, 'index changes on production', 'right'); ?>
        </div>
    </div>
    <div class="c"></div>

    &nbsp;

    <div class="section-constraints" style="margin-top:10px;">
        <div class="w_50 l p_r p_10">
            <div class="table-container">
                <? render_constraints($database_stag, $database_prod, 'constraint changes on staging', 'left'); ?>
            </div>
        </div>
        <div class="w_50 l p_l p_10">
            <div class="table-container">
                <? render_constraints($database_prod, $database_stag, 'constraint changes on production', 'right'); ?>
            </div>
        </div>
    </div>
    <div class="c"></div>

    &nbsp;

    <? if ($_SESSION['last_stmt']) : ?>
    <div class="last-stmt" style="padding:15px; background-color:#ececec;">
        <div><b>Last Successful Statement:</b></div>
        <pre><?= $_SESSION['last_stmt'] ?></pre>
    </div>
    <? endif; ?>

</div>