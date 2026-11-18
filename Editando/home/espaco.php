<?php
$q = $_GET;
$q['campo'] = 'espaco';
header('Location: home.php?' . http_build_query($q));
exit;
