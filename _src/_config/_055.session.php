<?php

    /**
     * file di applicazione della configurazione della sessione PHP
     *
     * In questo file vengono applicate le configurazioni delle sessioni PHP.
     *
     *
     *
     *
     *
     *
     *
     * TODO documentare
     *
     *
     *
     */

    // debug
    // print_r( $cf['debug'] );
    // error_reporting( E_ALL );
    // ini_set( 'display_errors', TRUE );
    // echo 'OUTPUT';

    /**
     * dichiarazione delle costanti
     * ============================
     * 
     * 
     */

    // costante per la durata massima della sessione
    if( ! defined( 'SESSION_LIMIT' ) ) {
        define( 'SESSION_LIMIT', 3600 );
    }

    /**
     * avvio della sessione
     * ====================
     * 
     * 
     */

    // controllo output
    if( headers_sent( $wf, $wl ) ) {
        die( 'output iniziato in ' . $wf . ' linea ' . $wl );
    }

    // timer
    timerCheck( $cf['speed'], '-> inizio avvio sessione' );

    // policy di performance
    ini_set( 'session.lazy_write', 0 );

    // policy di sicurezza
    // ini_set( 'session.cookie_samesite', 'strict' );
    ini_set( 'session.cookie_httponly', 1 );
    ini_set( 'session.cookie_secure', 1 );

    // avvio della sessione php
    if( session_start() ) {

        // registro l'id della sessione nell'array $cf
        $_SESSION['id']                = session_id();

        // imposto il tempo se la sessione è appena stata creata
        if( ! isset( $_SESSION['used'] ) ) {
            $_SESSION['used']            = time();
        }

        // log
        logger( 'avviata la sessione ' . $_SESSION['id'], 'session' );

    } else {

        // log
        logger( 'impossibile avviare la sessione', 'session', LOG_CRIT );

        // errore
        die( 'impossibile avviare la sessione' );

    }

    // timer
    timerCheck( $cf['speed'], '-> fine avvio sessione' );

    // debug
    // $h = fopen( DIRECTORY_BASE . 'var/log/sessions.debug', 'a+' );
    // fwrite( $h, date('Y-m-d H:i:s') . ' ' . session_id() . ' -> current' . PHP_EOL );
    // fclose( $h );

    /**
     * connessione della sessione a $cf e $ct
     * ======================================
     * 
     * 
     */

    // connetto i dati della sessione all'array $cf
    $cf['session'] = &$_SESSION;

    // collegamento all'array $ct
    $ct['session'] = &$cf['session'];

    /**
     * debug del runlevel
     * ==================
     * 
     * 
     */

    // debug
    // echo 'sessione ' . session_id();
    // print_r( $cf['session'] );
    // echo 'OUTPUT';
