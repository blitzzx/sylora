<?php
requireLogin();
logoutUser();
redirect('/', 'Logout efetuado com sucesso.', 'success');
?>
