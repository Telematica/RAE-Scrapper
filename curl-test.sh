#!/etc/bin/bash

# Simple HTTP request to Buscon server (RAE)

curl -v http://buscon.rae.es/drae/srv/search?val=api \
-d "TS014dfc77_id=3&TS014dfc77_cr=c563b25d20a81e2dca49edcbf9852373%3Alllk%3A2OB0CWtp%3A1150579394&TS014dfc77_76=0&TS014dfc77_md=1&TS014dfc77_rf=0&TS014dfc77_ct=0&TS014dfc77_pd=0"
