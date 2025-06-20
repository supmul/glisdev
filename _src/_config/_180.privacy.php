<?php

    /**
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     * TODO qui documentare l'intera organizzazione della privacy nel framework
     *
     *
     *
     *
     */

    /**
     * integrazione dei consensi dal database
     * ======================================
     * 
     * 
     */

    // TODO selezionare consensi_moduli in join con consensi e popolare $cf['privacy']['moduli']['consensi']
    $consensi = mysqlCachedQuery(
        $cf['memcache']['connection'],
        $cf['mysql']['connection'],
        'SELECT consensi_moduli.* FROM consensi_moduli WHERE consensi_moduli.id_lingua = ?',
        array( array( 's' => $cf['localization']['language']['id'] ) )
    );

    // debug
    // die( print_r( $consensi, true ) );
    // die( print_r( $cf['privacy']['moduli'], true ) );

    // aggiungo le richieste di consenso ai moduli
    // TODO questa cosa andrebbe 1) spostata negli specifici moduli (contatti, ecommerce, registrazione) inoltre
    // i consensi andrebbero inseriti direttamente sotto ogni modulo
    if( is_array( $consensi ) ) {
        foreach( $consensi as $consenso ) {

            // aggiungo la richiesta al modulo
            $cf['privacy']['moduli'][ $consenso['modulo'] ]['consensi'][ $consenso['id_consenso'] ] = array(
                'informativa' => array( $cf['localization']['language']['ietf'] => $consenso['informativa'] ),
                'label' => array( $cf['localization']['language']['ietf'] => $consenso['nome'] ),
                'action' => $consenso['azione'],
                'page' => $consenso['pagina'],
                'required' => $consenso['se_richiesto']
            );
    
        }
    }

    // debug
    // die( print_r( $cf['privacy']['moduli'], true ) );
