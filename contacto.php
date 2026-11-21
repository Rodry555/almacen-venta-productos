<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

// Ya no hay una página de contacto: toda consulta se resuelve por WhatsApp.
header('Location: ' . WHATSAPP_ADMIN_URL, true, 302);
exit;
