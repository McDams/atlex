<?php
/** @var array<string,mixed> $project */
/** @var array<int,array<string,mixed>> $funding */
$isEdit = true;
$action = url('/admin/projets/' . $project['id']);
require __DIR__ . '/_form.php';
