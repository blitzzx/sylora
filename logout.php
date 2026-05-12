<?php
require_once 'includes/config.php';
requireLogin();
logoutUser();
redirect('index.php', 'Logout efetuado com sucesso.', 'success');
?>
