# Keeunit Development Environment
Note: this how-to assumes that Ubuntu 18.04 is used as operating system.

    sudo apt install git php phpunit php-curl php-ssh2 php7.2-zip mysql-client docker-compose

    sudo systemctl stop apache2
    sudo systemctl disable apache2
    mkdir ~/.local/bin


## Edit Local DNS

Append to the top of `/etc/hosts`:

    127.0.0.1 qa.test mails.qa.test


## NPM
Download the current version of [npm](https://nodejs.org/en/), extract the
file to `~/.local/nodejs` and add the bin folder to your `$PATH`.

Bash:

    export PATH=$PATH:$HOME/.local/nodejs/bin

Fish:

    set -gx PATH $HOME/.local/nodejs/bin $PATH

Then run:

    npm install -g npm
    npm install -g vue
    npm install -g @vue/cli


## NVM

To use a fixed Nodejs version and to manage them install
[NVM](https://github.com/creationix/nvm).  
After the installation change the Node version to 9:

    nvm install v9.11.2
    nvm use 9.11.2

### NVM & Fish Shell
To run NVM install [BASS](https://github.com/edc/bass). Then add this to your
`config.fish`

    function nvm
          bass source ~/.nvm/nvm.sh ';' nvm $argv
    end
    abbr -a n nvm


## Composer
Download Composer from [here](getcomposer.org/download/), rename it to just
`composer`.  
Then make it executable and move it into a `$PATH` folder.:

    chmod u+x composer
    mv composer ~/.local/bin

Bash:

    export PATH=$PATH:$HOME/.local/bin

Fish:

    set -gx PATH $HOME/.local/bin $PATH


## VSCode

    sudo snap install --classic vscode


## Docker
Install Docker from [here](https://docs.docker.com/install/linux/docker-ce/ubuntu/#uninstall-old-versions)
and don't forget to also follow the [post-install](https://docs.docker.com/install/linux/linux-postinstall/) steps.


## Backend - Step 1

    git clone git@code.keelearning.de:keelearning/keelearning-php-backend.git backend
    cd backend


### dotenv
Create/get-from-another-developer the `.env` file and copy it into the backend top folder.


### install dependencies

    docker-compose up -d
    composer install
    npm install

### setup folders & permissions

    mkdir storage/{avatars,categories_attachments,learning_materials_attachments}
    chmod -R ugo+rw storage/


## Create The Test Database

    docker exec -it backend_mysql_1 /bin/bash
    
    mysql -p                         # password is DB_PASSWORD from .env
    CREATE DATABASE `quizapp-testing`;
    GRANT ALL PRIVILEGES ON `quizapp-testing`.* TO 'homestead'@'%';
    FLUSH PRIVILEGES;


## DBeaver
Download the [.deb](https://dbeaver.io/download/) and install it.  

In the menubar click on `Database` > `New Connection`.

- `Server Host`: 127.0.0.1
- `User name`: value of `DB_USERNAME` from .env
- `Password`: value of `DB_PASSWORD` from .env
- then select the `Local Client` drop-down and choose: `/usr`
- click `Test Connection`
- click `Finish`

Right click the `quizapp` database and select `Tools` > `Restore database`.  
Then enter the path to the SQL dump and click `Start`.  
Repeat the last step for the `quizapp-testing` database with an appropriate SQL dump.


## Backend - Step 2

    docker exec -it backend_phpfpm_1 /bin/bash
    php artisan update:geolite
    php artisan stats:cache


## Systemd Service
To autostart the Docker containers as local user.

    mkdir -p ~/.local/share/systemd/user

Then create inside `~/.local/share/systemd/user` a file
named `docker-compose.service` with the content:

    [Unit]
    Description=Docker Compose
        
    [Service]
    WorkingDirectory=[ABSOLUTE-PATH-TO-BACKEND]
    ExecStart=/usr/bin/docker-compose up -d
    PrivateTmp=true
        
    [Install]
    WantedBy=default.target

And enable and start the systemd service with:

    systemctl --user enable docker-compose.service
    systemctl --user start docker-compose.service



# Helpers - Fish Shell


## phpvm
Quick access to the php docker container.

    function phpvm
        docker exec -it backend_phpfpm_1 /bin/bash
    end


## phptest
Run a single unit test.

    function phptest
        clear
        docker exec -t backend_phpfpm_1 vendor/bin/phpunit --filter $argv[2] tests/$argv[1]
    end

Usage: `phptest FILE TESTNAME`:

    phptest Feature/PagesTest testPages



# Misc

## Example - Curl Json Get Request

    curl -H "Accept:application/json" http://qa.test/healthcheck
