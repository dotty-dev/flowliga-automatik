rm -r release/*

cp -r app_data/ release/
cp app_data_live/* release/app_data/
cp -r assets/ release/
cp assets_live/* release/assets/
cp index.php release/
cp .htaccess release/

rm release/app_data/game-pairings.csv
rm release/app_data/Teilnehmer-Liga-Lidarts-DiscordID.csv
rm release/app_data/ergebnisse.csv
rm release/app_data/errors.csv
rm release/app_data/bericht.svg


7z a /home/swi/spielbericht_automatik_release/spielbericht_automatik_v2_$(date -d "today" +"%Y%m%d%H%M%S").zip ./release/{*,.[!.]*}

