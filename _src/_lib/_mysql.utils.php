<?php

    function trovaidComune($comune)
    {

        // TODO migliorare prevedendo l'ID provincia come elemento di ricerca vedi sotto inserisciIndirizzo()

        global $cf;

        return mysqlSelectValue(
            $cf['mysql']['connection'],
            'SELECT id FROM comuni WHERE nome = ?',
            array(array('s' => $comune))
        );
    }

    function trovaIdTipologiaAttivita($attivita)
    {

        global $cf;

        return mysqlSelectValue(
            $cf['mysql']['connection'],
            'SELECT id FROM tipologie_attivita WHERE nome = ?',
            array(array('s' => $attivita))
        );
    }

    function trovaIdAnagraficaPerDenominazione($denominazione)
    {

        global $cf;

        return mysqlSelectValue(
            $cf['mysql']['connection'],
            'SELECT id FROM anagrafica WHERE denominazione = ?',
            array(array('s' => $denominazione))
        );
    }

    function trovaIdMatricola($matricola)
    {

        global $cf;

        return mysqlSelectValue(
            $cf['mysql']['connection'],
            'SELECT id FROM matricole WHERE matricola = ?',
            array(array('s' => $matricola))
        );
    }

    function trovaIdAziendaGestita()
    {

        global $cf;

        return mysqlSelectValue(
            $cf['mysql']['connection'],
            'SELECT id_anagrafica FROM anagrafica_categorie WHERE id_categoria = ? LIMIT 1',
            array(array('s' => 5))
        );
    }

    function trovaIdSedeLegale($idAnagrafica)
    {

        global $cf;

        return mysqlSelectValue(
            $cf['mysql']['connection'],
            'SELECT id_indirizzo 
                FROM anagrafica_indirizzi 
                WHERE id_anagrafica = ? 
                AND anagrafica_indirizzi.id_ruolo IN ( 1, 4 ) 
                LIMIT 1',
            array(array('s' => $idAnagrafica))
        );
    }

    function aggiungiImmagini(&$p, $id, $f, $r = null)
    {

        aggiungiDati($p, $id, $f, 'immagini', $r);
    }

    function aggiungiVideo(&$p, $id, $f, $r = null)
    {

        aggiungiDati($p, $id, $f, 'video', $r);
    }

    function aggiungiAudio(&$p, $id, $f, $r = null)
    {

        aggiungiDati($p, $id, $f, 'audio', $r);
    }

    function aggiungiFile(&$p, $id, $f, $r = null)
    {

        aggiungiDati($p, $id, $f, 'file', $r);
    }

    function aggiungiRecensioni(&$p, $id, $f, $l = 1)
    {

        global $cf;

        $p['contents']['recensioni'] = mysqlQuery(
            $cf['mysql']['connection'],
            'SELECT recensioni.* FROM recensioni WHERE ' . $f . ' = ? AND id_lingua = ? AND se_approvata IS NOT NULL',
            array(
                array('s' => $id),
                array('s' => $l)
            )
        );
    }

    function aggiungiDati(&$p, $id, $f, $t, $r = null)
    {

        global $cf;

        switch ($t) {
            case 'immagini':
                #                $tc = 'immagini.orientamento, immagini.taglio, immagini.anno, immagini.path_alternativo FROM immagini ';
                $tc = 'immagini.orientamento, immagini.taglio, immagini.path_alternativo FROM immagini ';
                $tf = 'id_immagine';
                $tk = 'images';
                break;
            case 'video':
                $tc = 'video.id_embed, video.codice_embed FROM video ';
                $tf = 'id_video';
                $tk = 'video';
                break;
            case 'audio':
                // TODO
                break;
            case 'file':
                $tc = 'file.url FROM file ';
                $tf = 'id_file';
                $tk = 'files';
                break;
        }

        $cnt = mysqlQuery(
            $cf['mysql']['connection'],
            'SELECT contenuti.title, contenuti.h1, contenuti.h2, contenuti.h3, ' .
                'contenuti.testo, contenuti.cappello, lingue.ietf, main_lingue.ietf AS main_ietf, ' .
                'metadati.nome AS meta_nome, metadati.testo AS meta_testo, meta_lingue.ietf AS meta_ietf, ' .
                'ruoli_' . $t . '.nome AS ruolo, ' . $t . '.id, ' .
                $t . '.ordine, ' . $t . '.nome, ' . $t . '.path, ' . $tc .
                'LEFT JOIN ruoli_' . $t . ' ON ruoli_' . $t . '.id = ' . $t . '.id_ruolo ' .
                'LEFT JOIN contenuti ON contenuti.' . $tf . ' = ' . $t . '.id ' .
                'LEFT JOIN lingue ON lingue.id = contenuti.id_lingua ' .
                'LEFT JOIN lingue AS main_lingue ON main_lingue.id = ' . $t . '.id_lingua ' .
                'LEFT JOIN metadati ON metadati.' . $tf . ' = ' . $t . '.id ' .
                'LEFT JOIN lingue AS meta_lingue ON meta_lingue.id = metadati.id_lingua ' .
                'WHERE ' . $t . '.' . $f . ' = ? ' .
                (($r !== null) ? 'AND ruoli_' . $t . '.id IN (' . implode(',', $r) . ')' : null),
            array(
                array('s' => $id)
            )
        );

        foreach ($cnt as $cn) {

            $im = array(
                'id'                => $cn['id'],
                'nome'              => $cn['nome'],
                'path'              => (empty($cn['main_ietf'])) ? $cn['path'] : array($cn['main_ietf'] => $cn['path']),
                #    'mimetype'          => findFileType( ( empty( $cn['main_ietf'] ) ) ? $cn['path'] : array( $cn['main_ietf'] => $cn['path'] ) ),     // commentata questa riga, sostituita con la seguente
                'mimetype'          => (empty($cn['main_ietf'])) ? findFileType($cn['path']) : array($cn['main_ietf'] => findFileType($cn['path'])),      // vedere issue #419
                'title'                => array($cn['ietf']    => $cn['title']),
                'h1'                => array($cn['ietf']    => $cn['h1']),
                'h2'                => array($cn['ietf']    => $cn['h2']),
                'h3'                => array($cn['ietf']    => $cn['h3']),
                'testo'                => array($cn['ietf']    => $cn['testo']),
                'cappello'            => array($cn['ietf']    => $cn['cappello']),
                'metadati'          => (
                    (empty($cn['meta_ietf'])) ?
                    array($cn['meta_nome'] => $cn['meta_testo']) :
                    array($cn['meta_nome'] => array($cn['meta_ietf'] => $cn['meta_testo']))
                )
            );

            switch ($t) {
                case 'immagini':
                    $im = array_replace_recursive($im, array(
                        'taglio'            => $cn['taglio'],
                        'path_alternativo'  => (empty($cn['main_ietf'])) ? $cn['path_alternativo'] : array($cn['main_ietf'] => $cn['path_alternativo']),
                        #    'mimetype'          => findFileType( ( empty( $cn['main_ietf'] ) ) ? $cn['path_alternativo'] : array( $cn['main_ietf'] => $cn['path_alternativo'] ) ),     // commentata questa riga, sostituita con la seguente
                        'mimetype'          => (empty($cn['main_ietf'])) ? findFileType($cn['path_alternativo']) : array($cn['main_ietf'] => findFileType($cn['path_alternativo'])),      // vedere issue #419
                        'orientamento'      => $cn['orientamento']
                        #   'anno'              => $cn['anno']
                    ));
                    break;
                case 'audio':
                    break;
                case 'video':
                    $im = array_replace_recursive($im, array(
                        'codice_embed'            => $cn['codice_embed'],
                        'id_embed'              => $cn['id_embed']
                    ));
                    break;
            }

            if (isset($p['contents'][$tk][$cn['ruolo']][$cn['ordine']])) {
                $p['contents'][$tk][$cn['ruolo']][$cn['ordine']] = array_replace_recursive(
                    $p['contents'][$tk][$cn['ruolo']][$cn['ordine']],
                    $im
                );
            } else {
                $p['contents'][$tk][$cn['ruolo']][$cn['ordine']] = $im;
            }
        }
    }


    function aggiungiMacro(&$p, $id, $f)
    {

        global $cf;

        $p['macro'] = array_merge(
            mysqlSelectColumn(
                'macro',
                $cf['mysql']['connection'],
                'SELECT macro FROM macro ' .
                    'WHERE ' . $f . ' = ?',
                array(
                    array('s' => $id)
                )
            ),
            ((isset($p['macro'])) ?  $p['macro'] : array())
        );
    }

    function aggiungiMenu(&$p, $id, $f)
    {

        global $cf;

        $mnu = mysqlQuery(
            $cf['mysql']['connection'],
            'SELECT menu.*, lingue.ietf FROM menu ' .
                'INNER JOIN lingue ON lingue.id = menu.id_lingua ' .
                'WHERE ' . $f . ' = ?',
            array(
                array('s' => $id)
            )
        );

        foreach ($mnu as $mn) {
            $p = array_replace_recursive(
                $p,
                array(
                    'menu'    => array(
                        $mn['menu']    => array(
                            $mn['ancora'] => array(
                                'label'        => array($mn['ietf'] => $mn['nome']),
                                'subpages'    => $mn['sottopagine'],
                                'ancora'    => (isset($mn['ancora'])) ? $mn['ancora'] : NULL,
                                'target'    => (isset($mn['target'])) ? $mn['target'] : NULL,
                                'priority'    => $mn['ordine']
                            )
                        )
                    )
                )
            );
        }
    }

    function aggiungiCaratteristiche(&$p, $id, $t, $f, $l = 1)
    {

        global $cf;

        $caratteristiche = mysqlQuery(
            $cf['mysql']['connection'],
            'SELECT ' . $t . '.ordine, coalesce( contenuti.testo, caratteristiche.nome ) AS caratteristica, ' . $t . '.valore FROM ' . $t . ' ' .
                'LEFT JOIN caratteristiche ON caratteristiche.id = ' . $t . '.id_caratteristica ' .
                'LEFT JOIN contenuti ON contenuti.id_caratteristica = caratteristiche.id AND contenuti.id_lingua = ? ' .
                'WHERE ' . $t . '.' . $f . ' = ? AND ' . $t . '.id_lingua = ? ',
            array(
                array('s' => $l),
                array('s' => $id),
                array('s' => $l)
            )
        );

        // print_r( $caratteristiche );

        foreach ($caratteristiche as $caratteristica) {

            $p['contents']['caratteristiche'][$caratteristica['ordine']] = array(
                $caratteristica['caratteristica'] => $caratteristica['valore']
            );
        }
    }

    function aggiungiMetadati(&$p, $id, $f)
    {

        global $cf;

        $meta = mysqlQuery(
            $cf['mysql']['connection'],
            'SELECT metadati.*, lingue.ietf FROM metadati ' .
                'LEFT JOIN lingue ON lingue.id = metadati.id_lingua ' .
                'WHERE ' . $f . ' = ?',
            array(
                array('s' => $id)
            )
        );

        // print_r( $meta );

        #        foreach( $meta as $mta ) {
        /*
                if( empty( $mta['ietf'] ) ) {
                    $p['metadati'][ $mta['nome'] ] = $mta['testo'];
                } else {
                    $p['metadati'][ $mta['nome'] ][ $mta['ietf'] ] = $mta['testo'];
                }
    */
        #                $p['metadati'] = array_replace_recursive(
        #                    $p['metadati'],
        #                    metadati2associativeArray( $mta )
        #                );

        if (isset(($p['metadati'])) && is_array($p['metadati'])) {

            $p['metadati'] = array_replace_recursive(
                $p['metadati'],
                metadati2associativeArray($meta)
            );
        } else {

            $p['metadati'] = metadati2associativeArray($meta);
        }

        #        }

    }

    /**
     * 
     */
    function aggiungiGruppi(&$p, $id, $f = 'id_pagina', $t = '__acl_pagine__')
    {

        // TODO l'assetto dei gruppi cambierà, probabilmente per usare le ACL

        global $cf;

        $groups = mysqlSelectColumn(
            'nome',
            $cf['mysql']['connection'],
            'SELECT gruppi.nome FROM gruppi ' .
                'INNER JOIN __acl_pagine__ ON gruppi.id = __acl_pagine__.id_gruppo ' .
                'WHERE __acl_pagine__.id_entita = ?',
            array(
                array('s' => $id)
            )
        );

        if (! empty($groups)) {
            $p['auth']['groups']    = $groups;
        }
    }

    function aggiungiContenuti(&$p, $id, $f)
    {

        global $cf;

        $cnt = mysqlQuery(
            $cf['mysql']['connection'],
            'SELECT contenuti.*, lingue.ietf FROM contenuti ' .
                'INNER JOIN lingue ON lingue.id = contenuti.id_lingua ' .
                'WHERE ' . $f . ' = ?',
            array(
                array('s' => $id)
            )
        );

        foreach ($cnt as $cn) {
            $p = array_replace_recursive(
                $p,
                array(
                    'short'             => array($cn['ietf']    => $cn['path_custom']),
                    'forced'            => array($cn['ietf']    => $cn['url_custom']),
                    'custom'            => array($cn['ietf']    => $cn['rewrite_custom']),
                    'title'                => array($cn['ietf']    => $cn['title']),
                    'cappello'            => array($cn['ietf']    => $cn['cappello']),
                    'h1'                => array($cn['ietf']    => $cn['h1']),
                    'h2'                => array($cn['ietf']    => $cn['h2']),
                    'h3'                => array($cn['ietf']    => $cn['h3']),
                    'og_type'            => array($cn['ietf']    => $cn['og_type']),
                    'og_title'            => array($cn['ietf']    => $cn['og_title']),
                    'og_image'            => array($cn['ietf']    => $cn['og_image']),
                    'og_audio'            => array($cn['ietf']    => $cn['og_audio']),
                    'og_video'            => array($cn['ietf']    => $cn['og_video']),
                    'og_description'    => array($cn['ietf']    => $cn['og_description']),
                    'og_determiner'     => array($cn['ietf']    => $cn['og_determiner'])
                )
            );
        }
    }

    function triggerOff($entita, $task = NULL)
    {

        global $cf;

        logWrite('richiesto spegnimento trigger per ' . $entita . ' da task ' . $task, 'cron');

        #    logWrite( 'spengo i trigger per ' . $entita, 'cron' );

        $troff = mysqlQuery(
            $cf['mysql']['connection'],
            'SET @TRIGGER_LAZY_' . strtoupper($entita) . ' = 1'
        );
    }

    function triggerOn($entita)
    {

        global $cf;

        logWrite('accendo i trigger per ' . $entita, 'cron');

        $tron = mysqlQuery(
            $cf['mysql']['connection'],
            'SET @TRIGGER_LAZY_' . strtoupper($entita) . ' = NULL'
        );
    }

    function trovaTabellaDestinazioneConstraint($t, $f)
    {

        global $cf;

        return mysqlSelectCachedValue(
            $cf['memcache']['connection'],
            $cf['mysql']['connection'],
            'SELECT referenced_table_name ' .
                'FROM information_schema.key_column_usage ' .
                'WHERE table_name = ' . $t . ' AND table_schema = database() ' .
                'AND referenced_table_name IS NOT NULL AND column_name = ' . $f
        );
    }

    function trovaRigaDaElaborare($c, $t, $q, $f1 = 'timestamp_sincronizzazione', $f2 = 'timestamp_aggiornamento', &$o = NULL, $e = array())
    {

        // default
        $r = NULL;

        // condizioni extra
        if (! empty($e)) {
            $ew = ' AND ' . implode(' AND ', $e);
        } else {
            $ew = NULL;
        }

        // ricerca di un elemento con timestamp_aggiornamento > timestamp_sincronizzazione in ordine di timestamp_aggiornamento
        if (isset($q['id'])) {

            // recupero dati
            $r    = mysqlSelectRow(
                $c,
                'SELECT ' . $t . '.* FROM ' . $t . '
                    WHERE ' . $t . '.id = ?',
                array(
                    array('s' => $q['id'])
                )
            );

            // output
            $o .= '<p>elemento da importare specificato</p>';
        } elseif (isset($q['f'])) {

            // recupero dati
            $r    = mysqlSelectRow(
                $c,
                'SELECT ' . $t . '.* FROM ' . $t . '
                    ORDER BY ' . $t . '.' . $f1 . ' ASC, ' . $t . '.id ASC LIMIT 1'
            );

            // output
            $o .= '<p>ricerca forzata di un elemento da importare</p>';
        } else {

            // recupero dati
            $r    = mysqlSelectRow(
                $c,
                'SELECT ' . $t . '.* FROM ' . $t . '
                    WHERE ( ' . $t . '.' . $f1 . ' < ' . $t . '.' . $f2 . '
                    OR ' . $t . '.' . $f1 . ' < ?
                    OR ' . $t . '.' . $f1 . ' IS NULL ) ' . $ew . '
                    ORDER BY ' . $t . '.' . $f1 . ' ASC, ' . $t . '.id ASC LIMIT 1',
                array(array('s' => strtotime('-2 days')))
            );

            // output
            $o .= '<p>ricerca di un corso da importare</p>';
        }

        // aggiornamento timestamp di importazione
        if (! empty($r['id'])) {
            mysqlQuery(
                $c,
                'UPDATE ' . $t . ' SET ' . $f1 . ' = ? WHERE id = ?',
                array(
                    array('s' => time()),
                    array('s' => $r['id'])
                )
            );
        }

        return $r;
    }

    function inserisciIndirizzo($indirizzo, $cap, $comune, $provincia, $localita = NULL, $stato = NULL, $idComune = NULL, $idProvincia = NULL, $idStato = NULL)
    {

        // dati globali
        global $cf;

        // pulisco gli spazi ai lati
        $indirizzo = trim(strtolower($indirizzo));

        // elenco tipologie
        $regexp = '/^\b(' . implode('|', mysqlSelectColumn('nome', $cf['mysql']['connection'], 'SELECT nome FROM tipologie_indirizzi')) . ')\b/';

        // trovo la tipologia
        preg_match($regexp, $indirizzo, $matches);
        $tipologia = (count($matches) > 0) ? $matches[0] : NULL;

        // debug
        // echo 'tipologia: ' . $tipologia . PHP_EOL;

        // trovo l'ID della tipologia
        $idTipologia = mysqlSelectValue($cf['mysql']['connection'], 'SELECT id FROM tipologie_indirizzi WHERE nome = ?', array(array('s' => $tipologia)));

        // debug
        // echo 'ID tipologia: ' . $idTipologia . PHP_EOL;

        // individuazione civico
        $regexp = '/[0-9]+[0-9a-zA-Z\/]*$/';

        // trovo il civico
        preg_match($regexp, $indirizzo, $matches);
        $civico = $matches[0];

        // debug
        // echo 'civico: ' . $civico . PHP_EOL;

        // trovo la parte nominale
        $nominale = ucwords(trim(str_replace(array($tipologia, $civico), NULL, $indirizzo)));

        // individuazione numeri romani
        $regexp = '/\b([IVLXCDM]{1,3}[LVCD]{0,1}[IXMC]{0,3})\b/';

        // trovo eventuali numeri romani nella parte nominale
        preg_match_all($regexp, strtoupper($nominale), $matches);

        // rimetto in maiuscolo i numeri romani
        $nominale = str_replace(array_map('ucwords', array_map('strtolower', $matches[0])), array_map('strtoupper', $matches[0]), $nominale);

        // debug
        // echo 'parte nominale: ' . $nominale . PHP_EOL;

        // trovo l'ID del comune e della provincia per CAP
        if (empty($idComune)) {
            $row = mysqlSelectRow(
                $cf['mysql']['connection'],
                'SELECT indirizzi.id_comune, comuni.id_provincia FROM indirizzi INNER JOIN comuni ON comuni.id = indirizzi.id_comune WHERE indirizzi.cap = ?',
                array(array('s' => $cap))
            );
            if (! empty($row)) {
                $idComune = $row['id_comune'];
                if (empty($idProvincia)) {
                    $idProvincia = $row['id_provincia'];
                }
            }
        }

        // trovo l'ID della provincia
        if (empty($idProvincia)) {
            $row = mysqlSelectRow(
                $cf['mysql']['connection'],
                'SELECT provincie.id AS id_provincia, provincie.id_regione, regioni.id_stato AS id_stato FROM provincie INNER JOIN regioni ON regioni.id = provincie.id_regione WHERE provincie.sigla = ? OR provincie.nome = ?',
                array(
                    array('s' => $provincia),
                    array('s' => $provincia)
                )
            );
            if (! empty($row)) {
                $idProvincia = $row['id_provincia'];
            }
        }

        // trovo l'ID del comune
        if (empty($idComune)) {
            $row = mysqlSelectRow(
                $cf['mysql']['connection'],
                'SELECT comuni.id FROM comuni WHERE nome = ? AND id_provincia = ?',
                array(
                    array('s' => $comune),
                    array('s' => $idProvincia)
                )
            );
            if (! empty($row)) {
                $idComune = $row['id'];
            }
        }

        // località
        $localita = ucfirst(strtolower($localita));

        // debug
        // echo 'località: ' . $localita . PHP_EOL;
        // echo 'CAP: ' . $cap . PHP_EOL;
        // echo 'ID comune: ' . $idComune . PHP_EOL;
        // echo 'ID provincia: ' . $idProvincia . PHP_EOL;

        // inserisco l'indirizzo
        $idIndirizzo = mysqlInsertRow(
            $cf['mysql']['connection'],
            array(
                'id_tipologia' => $idTipologia,
                'id_comune' => $idComune,
                'cap' => $cap,
                'localita' => $localita,
                'indirizzo' => $nominale,
                'civico' => $civico
            ),
            'indirizzi'
        );

        // debug
        // echo 'ID indirizzo: ' . $idIndirizzo . PHP_EOL;

        // return
        return $idIndirizzo;
    }

    function unisciAnagrafiche($sorgente, $destinazione)
    {

        // dati globali
        global $cf;

        // variabili di lavoro
        $tabella = 'anagrafica';
        $colonna = 'id';

        // chiamata a funzione
        unisciOggetti($sorgente, $destinazione, $tabella, $colonna);

        // aggiorno le viste statiche
        updateAnagraficaViewStatic($destinazione);
        cleanAnagraficaViewStatic();

        updateAttivitaViewStatic();

        // mysqlQuery( $cf['mysql']['connection'], 'REPLACE INTO anagrafica_view_static SELECT * FROM anagrafica_view WHERE id = ?', array( array( 's' => $destinazione ) ) );
        // mysqlQuery( $cf['mysql']['connection'], 'DELETE FROM anagrafica_view_static WHERE id = ?', array( array( 's' => $sorgente ) ) );

        // mysqlQuery( $cf['mysql']['connection'], 'REPLACE INTO anagrafica_archiviati_view_static SELECT * FROM anagrafica_archiviati_view WHERE id = ?', array( array( 's' => $destinazione ) ) );
        // mysqlQuery( $cf['mysql']['connection'], 'DELETE FROM anagrafica_archiviati_view_static WHERE id = ?', array( array( 's' => $sorgente ) ) );

        // mysqlQuery( $cf['mysql']['connection'], 'REPLACE INTO anagrafica_attivi_view_static SELECT * FROM anagrafica_attivi_view WHERE id = ?', array( array( 's' => $destinazione ) ) );
        // mysqlQuery( $cf['mysql']['connection'], 'DELETE FROM anagrafica_attivi_view_static WHERE id = ?', array( array( 's' => $sorgente ) ) );

    }

    function unisciOggetti($sorgente, $destinazione, $tabella, $colonna = 'id')
    {

        // dati globali
        global $cf;

        // trovo tutte le referenze a tabella.id
        $chiavi = mysqlQuery(
            $cf['mysql']['connection'],
            'SELECT TABLE_NAME,COLUMN_NAME,CONSTRAINT_NAME, REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME ' .
                'FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE ' .
                'WHERE ' .
                'REFERENCED_TABLE_SCHEMA = DATABASE() AND ' .
                'REFERENCED_TABLE_NAME = ? AND ' .
                'REFERENCED_COLUMN_NAME = ? ',
            array(
                array('s' => $tabella),
                array('s' => $colonna)
            )
        );

        // debug
        // print_r( $chiavi );

        // per ogni chiave...
        foreach ($chiavi as $chiave) {

            // debug
            // echo 'modifico tutte le referenze di ' . $chiave['TABLE_NAME'] . '.' . $chiave['COLUMN_NAME'] . ' da ' , $sorgente . ' a ' . $destinazione . PHP_EOL;
            logger('modifico tutte le referenze di ' . $chiave['TABLE_NAME'] . '.' . $chiave['COLUMN_NAME'] . ' da ' . $sorgente . ' a ' . $destinazione, 'deduplica');

            // eseguo la migrazione
            mysqlQuery(
                $cf['mysql']['connection'],
                'UPDATE ' . $chiave['TABLE_NAME'] . ' SET ' . $chiave['COLUMN_NAME'] . ' = ? WHERE ' . $chiave['COLUMN_NAME'] . ' = ?',
                array(
                    array('s' => $destinazione),
                    array('s' => $sorgente)
                )
            );

            // se ci sono degli errori...
            if (mysqli_errno($cf['mysql']['connection'])) {

                // debug
                // echo mysqli_errno( $cf['mysql']['connection'] ) . ' ' . mysqli_error( $cf['mysql']['connection'] ) . PHP_EOL;

            }

            // elimino le referenze eventualmente rimaste indietro
            mysqlQuery(
                $cf['mysql']['connection'],
                'DELETE FROM ' . $chiave['TABLE_NAME'] . ' WHERE ' . $chiave['COLUMN_NAME'] . ' = ?',
                array(
                    array('s' => $sorgente)
                )
            );
        }

        // recupero i campi della tabella principale
        $fields = mysqlQuery(
            $cf['mysql']['connection'],
            'SHOW COLUMNS FROM ' . $tabella
        );

        // debug
        // print_r( $fields );

        // prelevo la riga sorgente
        $rigaSorgente = mysqlSelectRow(
            $cf['mysql']['connection'],
            'SELECT * FROM ' . $tabella . ' WHERE ' . $colonna . ' = ?',
            array(
                array('s' => $sorgente)
            )
        );

        // elimino la riga sorgente
        mysqlQuery(
            $cf['mysql']['connection'],
            'DELETE FROM ' . $tabella . ' WHERE ' . $colonna . ' = ?',
            array(
                array('s' => $sorgente)
            )
        );

        // unisco i dati della tabella principale
        foreach ($fields as $field) {
            if ($field['Field'] != $colonna) {
                if (! empty($rigaSorgente[$field['Field']])) {
                    mysqlQuery(
                        $cf['mysql']['connection'],
                        'UPDATE ' . $tabella . ' SET ' . $field['Field'] . ' = ? WHERE ' . $colonna . ' = ? ',
                        array(
                            array('s' => $rigaSorgente[$field['Field']]),
                            array('s' => $destinazione)
                        )
                    );
                }
            }
        }
    }

    /**
     * 
     * 
     * 
     * 
     */
    function updateAnagraficaViewStatic($id)
    {

        global $cf;

        // var_dump( $id );

        $riga = mysqlSelectRow(
            $cf['mysql']['connection'],
            'SELECT
                    anagrafica.id,
                    tipologie_anagrafica.nome AS tipologia,
                    anagrafica.codice,
                    anagrafica.riferimento,
                    anagrafica.nome,
                    anagrafica.cognome,
                    anagrafica.denominazione,
                    anagrafica.soprannome,
                    anagrafica.sesso,
                    anagrafica.codice_fiscale,
                    anagrafica.partita_iva,
                    ranking.nome AS ranking,
                    anagrafica.recapiti,
                    anagrafica.anno_nascita,
                    anagrafica.mese_nascita,
                    anagrafica.giorno_nascita,
                    concat_ws( \'-\', anagrafica.anno_nascita, lpad( anagrafica.mese_nascita, 2, \'0\' ), lpad( anagrafica.giorno_nascita, 2, \'0\' ) ) AS data_nascita,
                    anagrafica.id_comune_nascita,
                    anagrafica.data_archiviazione,
                    anagrafica.id_account_inserimento,
                    anagrafica.timestamp_inserimento,
                    anagrafica.id_account_aggiornamento,
                    anagrafica.timestamp_aggiornamento
                FROM anagrafica
                    LEFT JOIN tipologie_anagrafica ON tipologie_anagrafica.id = anagrafica.id_tipologia
                    LEFT JOIN ranking ON ranking.id = anagrafica.id_ranking
                WHERE anagrafica.id = ? ',
            array(
                array('s' => $id)
            )
        );

        // print_r( $riga );

        if (! empty($riga['id'])) {

            updateAnagraficaViewStaticCategorie($id, $riga);

            updateAnagraficaViewStaticTelefoni($id, $riga);

            updateAnagraficaViewStaticMail($id, $riga);

            updateAnagraficaViewStaticIndirizzi($id, $riga);

            $tRef = time();

            foreach (array('timestamp_inserimento', 'timestamp_aggiornamento') as $fRef) {
                if (empty($riga[$fRef])) {
                    $riga[$fRef] = $tRef;
                    mysqlQuery(
                        $cf['mysql']['connection'],
                        'UPDATE anagrafica SET ' . $fRef . ' = ? WHERE id = ?',
                        array(
                            array('s' => $riga[$fRef]),
                            array('s' => $riga['id'])
                        )
                    );
                }
            }

            // $riga['__label__'] = ( ! empty( $riga['soprannome'] ) ) ? $riga['soprannome'] : ( ( ! empty( $riga['denominazione'] ) ) ? $riga['denominazione'] : ( trim( implode( ' ', array( $riga['nome'], $riga['cognome'] ) ) ) ) );
            $riga['__label__'] = (
                (
                    (! empty($riga['codice']))
                    ? $riga['codice'] . ' '
                    : NULL
                )
                .
                (
                    (! empty($riga['soprannome']))
                    ? $riga['soprannome']
                    : (
                        (! empty($riga['denominazione']))
                        ? $riga['denominazione']
                        : (trim(implode(' ', array($riga['nome'], $riga['cognome']))))
                    )
                )
            );

            mysqlInsertRow(
                $cf['mysql']['connection'],
                $riga,
                'anagrafica_view_static'
            );
        } else {

            mysqlQuery($cf['mysql']['connection'], 'DELETE FROM anagrafica_view_static WHERE id = ?', array(array('s' => $id)));
        }
    }

    /**
     * 
     * 
     * 
     * 
     */
    function updateAnagraficaViewStaticCategorie($id, &$riga = NULL)
    {

        global $cf;

        $categorie = mysqlSelectRow(
            $cf['mysql']['connection'],
            'SELECT
                    max( categorie_anagrafica.se_prospect ) AS se_prospect,
                    max( categorie_anagrafica.se_lead ) AS se_lead,
                    max( categorie_anagrafica.se_cliente ) AS se_cliente,
                    max( categorie_anagrafica.se_fornitore ) AS se_fornitore,
                    max( categorie_anagrafica.se_produttore ) AS se_produttore,
                    max( categorie_anagrafica.se_collaboratore ) AS se_collaboratore,
                    max( categorie_anagrafica.se_interno ) AS se_interno,
                    max( categorie_anagrafica.se_esterno ) AS se_esterno,
                    max( categorie_anagrafica.se_commerciale ) AS se_commerciale,
                    max( categorie_anagrafica.se_concorrente ) AS se_concorrente,
                    max( categorie_anagrafica.se_gestita ) AS se_gestita,
                    max( categorie_anagrafica.se_amministrazione ) AS se_amministrazione,
                    max( categorie_anagrafica.se_notizie ) AS se_notizie,
                    group_concat( DISTINCT categorie_anagrafica_path( categorie_anagrafica.id ) SEPARATOR \' | \' ) AS categorie
                FROM anagrafica_categorie
                    LEFT JOIN categorie_anagrafica ON categorie_anagrafica.id = anagrafica_categorie.id_categoria
                WHERE anagrafica_categorie.id_anagrafica = ? ',
            array(
                array('s' => $id)
            )
        );

        if (! empty($riga)) {

            $riga = array_merge(
                $riga,
                $categorie
            );
        } else {

            $riga['id'] = $id;

            mysqlInsertRow(
                $cf['mysql']['connection'],
                $riga,
                'anagrafica_view_static'
            );
        }

        $tCat = mysqlSelectValue(
            $cf['mysql']['connection'],
            'SELECT max( timestamp_aggiornamento ) FROM anagrafica_categorie WHERE id_anagrafica = ?',
            array(array('s' => $riga['id']))
        );

        if ($tCat > $riga['timestamp_aggiornamento']) {
            $riga['timestamp_aggiornamento'] = $tCat;
        }
    }

    /**
     * 
     * 
     * 
     * 
     */
    function updateAnagraficaViewStaticIndirizzi($id, &$riga = NULL)
    {

        global $cf;

        $indirizzi = mysqlSelectRow(
            $cf['mysql']['connection'],
            'SELECT regioni.id_stato, comuni.id_provincia
                FROM anagrafica_indirizzi
                    LEFT JOIN indirizzi ON indirizzi.id = anagrafica_indirizzi.id_indirizzo
                    LEFT JOIN comuni ON comuni.id = indirizzi.id_comune
                    LEFT JOIN provincie ON provincie.id = comuni.id_provincia
                    LEFT JOIN regioni ON regioni.id = provincie.id_regione
                    LEFT JOIN ruoli_indirizzi ON ruoli_indirizzi.id = anagrafica_indirizzi.id_ruolo
                WHERE anagrafica_indirizzi.id_anagrafica = ? 
                AND anagrafica_indirizzi.indirizzo IS NULL
                ORDER BY ruoli_indirizzi.se_sede_legale DESC
                LIMIT 1 ',
            array(
                array('s' => $id)
            )
        );

        if (! empty($riga)) {

            $riga = array_merge(
                $riga,
                $indirizzi
            );
        } else {

            $riga['id'] = $id;

            mysqlInsertRow(
                $cf['mysql']['connection'],
                $riga,
                'anagrafica_view_static'
            );
        }
    }

    /**
     * 
     * 
     * 
     * 
     */
    function updateAnagraficaViewStaticTelefoni($id, &$riga = NULL)
    {

        global $cf;

        $telefoni = mysqlSelectRow(
            $cf['mysql']['connection'],
            'SELECT
                    group_concat( DISTINCT telefoni.numero SEPARATOR \' | \' ) AS telefoni
                FROM telefoni
                WHERE id_anagrafica = ? ',
            array(
                array('s' => $id)
            )
        );

        if (! empty($riga)) {

            $riga = array_merge(
                $riga,
                $telefoni
            );
        } else {

            $riga['id'] = $id;

            mysqlInsertRow(
                $cf['mysql']['connection'],
                $riga,
                'anagrafica_view_static'
            );
        }
    }

    /**
     * 
     * 
     * 
     * 
     */
    function updateAnagraficaViewStaticMail($id, &$riga = NULL)
    {

        global $cf;

        $mail = mysqlSelectRow(
            $cf['mysql']['connection'],
            'SELECT
                    group_concat( DISTINCT mail.indirizzo SEPARATOR \' | \' ) AS mail
                FROM mail
                WHERE id_anagrafica = ? ',
            array(
                array('s' => $id)
            )
        );

        if (! empty($riga)) {

            $riga = array_merge(
                $riga,
                $mail
            );
        } else {

            $riga['id'] = $id;

            mysqlInsertRow(
                $cf['mysql']['connection'],
                $riga,
                'anagrafica_view_static'
            );
        }
    }

    /**
     * 
     * TODO documentare
     * 
     * TODO implementare la cancellazione di specifiche righe
     * 
     */
    function cleanAnagraficaViewStatic()
    {

        global $cf;

        return mysqlQuery(
            $cf['mysql']['connection'],
            'DELETE anagrafica_view_static FROM anagrafica_view_static
                LEFT JOIN anagrafica ON anagrafica.id = anagrafica_view_static.id
                WHERE anagrafica.id IS NULL;'
        );
    }

    /**
     * 
     * TODO documentare
     * 
     */
    function emptyAnagraficaViewStatic()
    {

        global $cf;

        return mysqlQuery(
            $cf['mysql']['connection'],
            'DELETE FROM anagrafica_view_static'
        );
    }
