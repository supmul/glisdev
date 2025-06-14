<?php

    /**
     * questo file definisce gli account, gli utenti e i gruppi del framework
     *
     * il sistema di autenticazione del framework
     * ==========================================
     * Il framework supporta un potente e flessibile sistema di autenticazione
     * e autorizzazione basato su account e gruppi di account. Account e gruppi
     * sono impostati nel file _200.auth.php e personalizzati nel file 200.auth.php.
     * Le password sono archiviate in forma crittografata tramite md5() per
     * ragioni di sicurezza.
     *
     * Se avete a disposizione una shell Bash, generare una password e cifrarla in MD5 è
     * molto semplice:
     *
     * \code{.bash}
     * pwgen -nyc 16 1
     * echo -n "<password>" | md5sum
     * \endcode
     *
     *
     *
     *
     *
     * account
     * -------
     * Gli account rappresentano i soggetti riconosciuti dal framework e autorizzati a operare
     * su di esso a vari livelli.
     *
     *
     *
     *
     *
     *
     *
     * gruppi
     * ------
     *
     *
     *
     *
     *
     * utenti
     * ------
     *
     *
     *
     *
     *
     * il sistema di autorizzazione del framework
     * ==========================================
     *
     *
     *
     * permessi
     * --------
     *
     * [...] sostanzialmente quali azioni un utente o un gruppo possono compiere su un'entità in generale [...]
     *
     *
     * diritti
     * -------
     *
     * [...] sostanzialmente quali azioni un utente o un gruppo possono compiere su una istanza (o riga) specifica di un'entità [...]
     *
     * privilegi
     * ---------
     *
     * [...] azioni speciali e particolari, come ad esempio [...]
     *
     *
     *
     * TODO documentare
     *
     */

    /**
     * definizione dei gruppi
     * ======================
     * 
     * 
     */

    // gruppi
    $cf['auth']['groups'] = array(
        'roots' => array(
            'id' => NULL,
            'nome' => 'roots',
            'privilegi' => array(
                'EDIT_CONFIGURAZIONE',
                'GESTIONE_ACCOUNT',
                'INVIO_DIRETTO_MAIL'
            )
        ),
        'staff' => array(
            'id' => NULL,
            'nome' => 'staff'
        ),
        'users' => array(
            'id' => NULL,
            'nome' => 'users'
        )
    );

    // recupero dei gruppi dal database
    $gruppi = mysqlQuery(
        $cf['mysql']['connection'],
        'SELECT * FROM gruppi_view'
    );

    // integro i gruppi trovati nel database
    if( is_array( $gruppi ) ) {
        foreach( $gruppi as $gruppo ) {
            $cf['auth']['groups'][ $gruppo['nome'] ] = array(
                'id' => $gruppo['id'],
                'nome' => $gruppo['nome']
            );
        }
    }

    /**
     * definizione dei privilegi
     * =========================
     * 
     * 
     */

    // privilegi
    $cf['auth']['privileges'] = array(
        'EDIT_CONFIGURAZIONE' => array(
            'id' => NULL,
            'nome' => 'editare la configurazione del framework'
        ),
        'INVIO_ANAGRAFICA_ARCHIVIUM' => array(
            'id' => NULL,
            'nome' => 'inviare una anagrafica ad Archivium'
        ),
        'CANCELLAZIONE_RICORSIVA' => array(
            'id' => NULL,
            'nome' => 'cancellare ricorsivamente oggetti dal database'
        ),
        'INVIO_DIRETTO_MAIL' => array(
            'id' => NULL,
            'nome' => 'inviare mail da API REST'
        )
    );

    /**
     * definizione degli utenti
     * ========================
     * 
     * 
     */

    // account
    $cf['auth']['accounts'] = array(
        'root' => array(
            'id' => NULL,
            'username' => 'root',
            'password' => NULL,
            'gruppi' => array(
                'roots'
            ),
            'permissions' => array(),
            'privilegi' => array(
                'EDIT_CONFIGURAZIONE',
                'INVIO_ANAGRAFICA_ARCHIVIUM'
            )
        )
    );

    /**
     * definizione dei profili di creazione nuovi account
     * ==================================================
     * 
     * 
     */

    // profili di creazione nuovi account
    $cf['auth']['profili'] = array(
        'admin' => array(
            'nome' => 'utente',
            'cognome' => 'amministratore',
            'gruppi' => array( 'roots', 'staff', 'users' ),
            'categorie' => array( 'collaboratori' ),
            'username' => true,
            'sms' => false,
            'mail' => 'DEFAULT_NUOVO_ACCOUNT_ATTIVO',
            'landing' => 'dashboard',
            'attivo' => true
        )
    );

    /**
     * configurazioni per JWT
     * ======================
     * 
     * 
     */

    // salt e scadenza della chiave JWT
    // NOTA calcolato in questo modo il salt cambia ogni giorno quindi non è possibile riusarlo troppo a lungo
    // TODO non c'è un modo più fine per farlo?
    $cf['auth']['jwt']['salt'] = date( 'Y-m-d' );

    // inizializzazione della password per JWT
    $cf['auth']['jwt']['pass'] = NULL;

    // debug
    // die( print_r( $cf['auth'], true ) );
