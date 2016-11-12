#!/etc/bin/bash

# Simple HTTP request to Buscon server (RAE)

#deprecated
#curl -v http://buscon.rae.es/drae/srv/search?val=api \
#-d "TS014dfc77_id=3&TS014dfc77_cr=c563b25d20a81e2dca49edcbf9852373%3Alllk%3A2OB0CWtp%3A1150579394&TS014dfc77_76=0&TS014dfc77_md=1&TS014dfc77_rf=0&TS014dfc77_ct=0&TS014dfc77_pd=0"

#upgraded for new endpoint http://dle.rae.es/
curl -H "Upgrade-Insecure-Requests:1" \
-H "User-Agent:Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.116 Safari/537.36" \ 
-X GET http://dle.rae.es/srv/fetch?w=manijero

#@todo new implementation
#http://stackoverflow.com/questions/5080988/how-to-extract-string-following-a-pattern-with-grep-regex-or-perl#answer-5081519
curl -H "Upgrade-Insecure-Requests:1" -H "User-Agent:Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.116 Safari/537.36" \
-X GET http://dle.rae.es/srv/fetch?w=manijero | grep -Po 'name="\K.*?(?=")'
