<?php
$q = $_GET;
$q['campo'] = 'organizadores';
header('Location: home.php?' . http_build_query($q));
exit;
