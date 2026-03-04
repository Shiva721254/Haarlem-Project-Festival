<?php
$hash = '$2y$12$48/mAMTpsHZ0vcFitlPY9O3Gk.nnYHt9ZNA/ULGtzKYMdrUZQMJUW';
$password = 'Admin123!';
var_dump(['hash' => $hash, 'password' => $password]);
echo password_verify($password, $hash) ? 'VERIFIED!' : 'FAILED!';
echo "\n";
