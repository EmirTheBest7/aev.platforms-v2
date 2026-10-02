<?php

declare(strict_types=1);

// Prints a random APP_KEY (64 hex chars). Usage: php scripts/generate-key.php
echo bin2hex(random_bytes(32)), PHP_EOL;
