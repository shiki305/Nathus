<?php
$q = $_GET;
$q['campo'] = 'buffet';
header('Location: home.php?' . http_build_query($q));
exit;
