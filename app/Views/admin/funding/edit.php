<?php
/** @var array<string,mixed> $opportunity */
/** @var array<int,array<string,mixed>> $projects */
$isEdit = true;
$action = url('/admin/financements/' . $opportunity['id']);
require __DIR__ . '/_form.php';
