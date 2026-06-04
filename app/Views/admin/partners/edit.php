<?php
/** @var array<string,mixed> $partner */
$isEdit = true;
$action = url('/admin/partenaires/' . $partner['id']);
require __DIR__ . '/_form.php';
