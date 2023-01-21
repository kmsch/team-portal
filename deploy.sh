# build angular application
npm -f
npm run build

# build php code
cd php
composer install
composer dumpautoload
cd ..

# remove any caches
rm  -R ./php/cache
rm  -R ./php/errors

# tar all files into 1 file
cd dist && tar -cf ../deployment.tar * && cd -
tar --append -f deployment.tar php static-pages

# remove anything on server, except for Configuration.php
ssh -T deb105013n2@skcvolleybal.nl -i ~/.ssh/antagonist-ssh <<- 'END'
cd /home/deb105013n2/public_html/team-portal
shopt -s extglob
rm -R !("configuration.php"|".htaccess")
shopt -u extglob
END

# copy files to server
scp -i ~/.ssh/antagonist-ssh deployment.tar deb105013n2@skcvolleybal.nl:~/public_html/team-portal

# Extract tar-file (+ remove afterwards) and move Configuration file to correct location
ssh -T deb105013n2@skcvolleybal.nl -i ~/.ssh/antagonist-ssh <<- 'END'
cd /home/deb105013n2/public_html/team-portal
tar -xf ./deployment.tar
cp /home/deb105013n2/public_html/team-portal/configuration.php /home/deb105013n2/public_html/team-portal/php/configuration.php
rm ./deployment.tar
END

# remove locally
rm ./deployment.tar

curl https://www.skcvolleybal.nl/team-portal/api/tasks/daily-tasks &> /dev/null