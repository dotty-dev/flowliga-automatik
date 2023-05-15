# flowliga-automatik
Tool to automatically generate game reports for [FlowLiga Online Darts League](https://www.dartzentrum-augsburg.de/flow-dartsliga) from [lidarts](https://lidarts.org) games api.


Webhook for posting to Discord are saved in file app_data\webhook.php the file should look like this:
``` 
<?php 

$webhookurl_report = "[YOUR WEBHOOK GOES HERE]";
$webhookurl_ticker = "[YOUR WEBHOOK GOES HERE]";
``` 
The webhook.php file should be replaced with the leagues one from app_data_live on build (to be added).

Graphics in assets are placeholders with the same dimensions as the leagues actual ones and should be replaced with graphics from folder assets_live in build script (to be added).
The original graphics are excluded from this repo since I hold no rights to them and am not associated to the league.

Patters in app_data/patters.txt are placeholders and should be replaced with patters.txt from app_data_live in build script (to be added).

Uses [Iosevka](https://github.com/be5invis/Iosevka) [![License: Open Font-1.1](https://img.shields.io/badge/License-OFL_1.1-lightgreen.svg)](https://opensource.org/licenses/OFL-1.1) by Belleve Invis as primary display font.
