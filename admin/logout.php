<?php
/**
 * Pizzaria TT — Projeto de Extensão
 * Arquivo: admin/logout.php
 * Responsável principal: Eduardo Daniel (Back-end PHP)
 * Suporte: —
 * Descrição: Encerra a sessão do administrador e redireciona para o login.
 */

session_start();

// Destroi todos os dados da sessão atual
session_unset();
session_destroy();

// Volta para a tela de login
header('Location: login.php');
exit;
