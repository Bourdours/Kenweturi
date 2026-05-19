<?php

/**
 * Stubs pour les fonctions globales CodeIgniter 4.
 * Ce fichier est uniquement pour l'analyse statique (Intelephense).
 * Il n'est jamais chargé à l'exécution.
 */

use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Session\Session;

function esc(array|string $data, string $context = 'html', ?string $encoding = null): array|string { throw new \RuntimeException('stub'); }

function site_url(array|string $relativePath = '', ?string $scheme = null): string { throw new \RuntimeException('stub'); }

function base_url(array|string $relativePath = '', ?string $scheme = null): string { throw new \RuntimeException('stub'); }

function csrf_field(?string $id = null): string { throw new \RuntimeException('stub'); }

function csrf_token(): string { throw new \RuntimeException('stub'); }

function session(?string $val = null): Session|string|null { throw new \RuntimeException('stub'); }

function view(string $name, array $data = [], array $options = []): string { throw new \RuntimeException('stub'); }

function lang(string $line, array $args = [], ?string $locale = null): string { throw new \RuntimeException('stub'); }

function redirect(?string $route = null): RedirectResponse { throw new \RuntimeException('stub'); }
