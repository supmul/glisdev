#!/bin/bash

# TODO documentare
# questo script è un buon posto dove mettere la documentazione su come GlisWeb gestisce i permessi
#

## pulizia schermo
clear

## livelli per la root del sito
# NOTA questo script deve girare nella cartella SUPERIORE a quella di installazione!
RL="../../"
RP="../"

## directory corrente
cd $(dirname "$0")

## funzioni
. ./_lib/_functions.sh

## verifica utente root
check-root

## passo alla cartella del deploy
cd $RL

## ricaavo il nome del deploy
SUB=$( basename $( pwd ) )

## passo alla cartella principale
cd $RP

## informazioni
echo "lavoro su: $(pwd)"

## utente FTP
if [ -f "ftpuser.conf" ]; then
    FTPUSER=$( cat ftpuser.conf | tr -d '[:space:]' )
    echo "utente FTP rilevato: $FTPUSER"
else
    FTPUSER="www-data"
fi

## cambio proprietario
chown -R root:www-data ./$SUB/
#find ./$SUB/src/templates                                                           -exec chown -R $FTPUSER:www-data {} \;
#find ./$SUB/tmp                                                                     -exec chown -R www-data:www-data {} \;
#find ./$SUB/var                                                                     -exec chown -R $FTPUSER:www-data {} \;
#find ./$SUB/var/cache                                                               -exec chown -R www-data:www-data {} \;

chown -R $FTPUSER:www-data ./$SUB/src/templates
chown -R www-data:www-data ./$SUB/tmp
chown -R $FTPUSER:www-data ./$SUB/var
chown -R www-data:www-data ./$SUB/var/cache

## cartella .git
if [ -d ".git" ]; then
    chown -R root:root ./$SUB/.git
fi

## cartella .github
if [ -d ".github" ]; then
    chown -R root:root ./$SUB/.github
fi

## informazioni
echo "impostati proprietari e gruppi, modifico i permessi"

## cambio permessi (silenzioso)
find ./$SUB/                    -type d         -not \( -path ".git" -prune \)      -exec chmod 550 {} \;
find ./$SUB/                    -type f         -not \( -path ".git" -prune \)      -exec chmod 640 {} \;
find ./$SUB/                    -name '*.sh'    -not \( -path ".git" -prune \)      -exec chmod 550 {} \;

# permessi aggiuntivi per le cartelle
find ./$SUB/.git/hooks          -type f                                             -exec chmod ug+x {} \;
find ./$SUB/src/templates       -type d                                             -exec chmod 770 {} \;
find ./$SUB/tmp                 -type d                                             -exec chmod 770 {} \;
find ./$SUB/var                 -type d                                             -exec chmod 770 {} \;

find ./$SUB/src/templates       -type f                                             -exec chmod 660 {} \;
find ./$SUB/mod/*/src/templates -type f                                             -exec chmod 660 {} \;
find ./$SUB/tmp                 -type f                                             -exec chmod 660 {} \;
find ./$SUB/var                 -type f                                             -exec chmod 660 {} \;

# informazioni
echo "permessi modificati"

## TODO
# fare una modalità "paranoia" in cui:
# - il server web può solo leggere a parte quelle due o tre cartelle dove deve scrivere
# - le cartelle .git .github e il file .gitignore sono di proprietà di root
# - valutare altre restrizioni
#
# NOTE
#
# file su cui il framework può scrivere
# 640 -> rw-r-----
#
# cartelle su cui il framework può scrivere
# 750 -> rwxr-x---
#
# file su cui il framework non può scrivere
# 440 -> r--r-----
#
# cartelle su cui il framework non può scrivere
# script che il framework può eseguire ma non scrivere
# 550 -> r-xr-x---
#
