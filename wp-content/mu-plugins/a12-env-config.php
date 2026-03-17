<?php
/**
 * Must-Use Plugin: A12 Environment Config
 *
 * Carregado automaticamente pelo WordPress antes de qualquer plugin.
 * Responsável por configurações globais do ambiente.
 *
 * @package A12
 */

// Previne acesso direto
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define o ambiente atual com base na variável de ambiente do container
define( 'A12_ENV', getenv( 'A12_ENV' ) ?: 'local' );

// Em ambiente local, ativa saída de erros PHP no log do WordPress
if ( A12_ENV === 'local' ) {
    ini_set( 'display_errors', '0' );
    ini_set( 'log_errors', '1' );
}
