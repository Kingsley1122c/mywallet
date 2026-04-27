<?php

require_once __DIR__ . '/users_bootstrap.php';

repairUsersStore(__DIR__ . '/users.json');
ensureAdminLogin(__DIR__ . '/users.json');