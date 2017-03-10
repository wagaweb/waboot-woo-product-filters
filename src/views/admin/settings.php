<ul class="wbwpf-admin-page-menu">
	<li><a href="<?php echo add_query_arg(["tab"=>"filters"],$current_url) ?>"><?php _ex("Filters","Page menu",$textdomain); ?></a></li>
    <li><a href="<?php echo add_query_arg(["tab"=>"options"],$current_url) ?>"><?php _ex("Options","Page menu",$textdomain); ?></a></li>
</ul>

<?php

if(!isset($_GET['tab']) || $_GET['tab'] == "filters"){
    require_once __DIR__."/_tab_filters.php";
}elseif($_GET['tab'] == "options"){
	require_once __DIR__."/_tab_options.php";
}