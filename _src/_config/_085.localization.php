<?php

    /**
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
    // print_r( $_REQUEST );

    /**
     * integrazione della configurazione da file Json/Yaml
     * ===================================================
     * 
     * 
     */

    // configurazione extra
    if( isset( $cx['localization'] ) ) {
        $cf['localization'] = array_replace_recursive( $cf['localization'], $cx['localization'] );
    }

    /**
     * collegamento di $ct a $cf tramite puntatore
     * ===========================================
     * 
     * 
     */

    // collegamento all'array $ct
    $ct['localization'] = &$cf['localization'];

    /**
     * individuazione della lingua corrente
     * ====================================
     * 
     * 
     */

    // lingua richiesta o lingua di default
    if( isset( $_REQUEST['__lg__'] ) && ! empty( $_REQUEST['__lg__'] ) ) {
        if( array_key_exists( $_REQUEST['__lg__'], $cf['site']['name'] ) ) {
            $cf['localization']['lg'] = $_REQUEST['__lg__'];
        } else {
            logger( 'lingua ' . $_REQUEST['__lg__'] . ' non supportata', 'localization', LOG_NOTICE );
        }
    }

    // TODO se non è ancora settata la lingua, ricavarla dal dominio

    // TODO se non è ancora settata la lingua, ricavarla dalla lingua del browser

    // lingua di default
    if( ! isset( $cf['localization']['lg'] ) || empty( $cf['localization']['lg'] ) ) {
        if( ! empty( array_keys( $cf['site']['name'] ) ) ) {
            $cf['localization']['lg'] = current( array_keys( $cf['site']['name'] ) );
        } else {
            die( 'nome del sito non specificato' );
        }
    }

    // localizzazione di default ricavata dal titolo del sito
    $cf['localization']['language'] = &$cf['localization']['languages'][ $cf['localization']['lg'] ];

    // SCORCIATOIA lingua corrente del sito
    $ct['ietf'] = &$cf['localization']['language']['ietf'];

    /**
     * debug del runlevel
     * ==================
     * 
     * 
     */

    // debug
    // echo 'OUTPUT';
