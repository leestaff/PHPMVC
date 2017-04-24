<?php
$routing = array(
		'/admin\/(.*?)\/(.*?)\/(.*)/' => 'admin/\1_\2/\3',
);

$default['controller'] = 'Home';
$default['action'] = 'Index';
