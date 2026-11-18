<?php
$q = $_GET;
$q['editar'] = '1';
header('Location: homeAnt.php?' . http_build_query($q));
exit;
