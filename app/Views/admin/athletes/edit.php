<?php
/** @var array<string,mixed> $athlete */
$isEdit = true;
$action = url('/admin/athletes/' . $athlete['id']);
require __DIR__ . '/_form.php';
