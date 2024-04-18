FROM ubuntu:22.04

LABEL mantainer="danilo.sanchi@gmail.com"
# https://hub.docker.com/r/khanhicetea/php7-fpm-docker/dockerfile

# Si possono passare lo uid e lo gid dell'utente dell'host...
ARG HOST_UID=1000
ARG HOST_GID=1000

# L'installazione di git richiede interazione per inserire la timezone. La evitiamo preimpostandola
ENV TZ=Europe/Rome
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# installiamo quello che serve
RUN apt-get update && apt-get install -y \
    software-properties-common &&\
    LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php &&\
    apt-get update && apt-get install -y \
    software-properties-common \
    php8.3-fpm \
    php8.3-cli \
    php8.3-common \
    php8.3-opcache \
    php8.3-mysql \
    php8.3-sqlite \
    php8.3-phpdbg \
    php8.3-mbstring \
    php8.3-gd \
    php8.3-imap \
    php8.3-ldap \
    php8.3-pgsql \
    php8.3-pspell \
    php8.3-snmp \
    php8.3-tidy \
    php8.3-dev \
    php8.3-intl \
    php8.3-curl \
    php8.3-zip \
    php8.3-xml \
    php8.3-dom \
    php8.3-redis \
    php8.3-bcmath \
    php8.3-xdebug \
    vim \
    git \
    curl \
    zip unzip \
    iproute2 \
    rsync \
    wget \
    && \
    apt-get remove --purge -y software-properties-common && \
    apt-get autoremove -y && \
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# creiamo la directory in cui fpm mette il suo pid (?)
RUN mkdir -p /run/php && \
# Sostituiamo www-data:www-data con user:user per fpm (?)
    sed -i "s/user = www-data/user = user/" /etc/php/8.3/fpm/pool.d/www.conf && \
    sed -i "s/group = www-data/group = user/" /etc/php/8.3/fpm/pool.d/www.conf && \
# Facciamo in modo che l'output emesso dai worker fpm sia intercettato (?)
    sed -i "s/;catch_workers_output = .*/catch_workers_output = yes/" /etc/php/8.3/fpm/pool.d/www.conf && \
#    sed -i -e "s/pid =.*/pid = \/var\/run\/php8.3-fpm.pid/" /etc/php/8.3/fpm/php-fpm.conf && \
#    sed -i -e "s/error_log =.*/error_log = \/proc\/self\/fd\/2/" /etc/php/8.3/fpm/php-fpm.conf && \
# Mettiamo fpm in ascoto sulla porta 9000 anziché su una socket (?)
    sed -i "s/listen = .*/listen = 9000/" /etc/php/8.3/fpm/pool.d/www.conf && \
# Disabilitando l'opzione `daemonize` facciamo si che l'output di fpm venga sparato sullo stdout del container (?)
    sed -i -e "s/;daemonize\s*=\s*yes/daemonize = no/g" /etc/php/8.3/fpm/php-fpm.conf && \
# Impostiamo a 2 minuti il timeout delle connessioni ssh (?)
    echo "ServerAliveInterval 120" >> /etc/ssh/ssh_config

# Configuriamo il php.ini aggiungendo un'estensione custom
COPY conf/custom.ini /etc/php/8.3/mods-available/custom.ini
RUN phpenmod custom

# Configuro Xdebug
COPY conf/xdebug.ini /etc/php/8.3/mods-available/xdebug.ini
# Chiedo il l'ip del gateway con `ip route|awk '/default/ { print $3 }'` e lo imposto nell'xdebug.ini
RUN sed -i "s/xdebug\.client_host=.*/xdebug\.client_host="$(ip route|awk '/default/ { print $3 }')"/" /etc/php/8.3/mods-available/xdebug.ini && \
# individio la posizione esatta di xdebug.so e la imposto nell'xdebug.ini
#    sed -i "s|zend_extension=xdebug.so|zend_extension="$(find / -iname xdebug.so 2>&1 | grep -v 'Permission denied' | tail -1)"|" /etc/php/8.3/mods-available/xdebug.ini
    sed -i "s|zend_extension=xdebug.so|zend_extension="/usr/lib/php/20230831/xdebug.so"|" /etc/php/8.3/mods-available/xdebug.ini
# `application` è l'aggregatore che insieme a `_` compare quando xdebug è attivo (?)
ENV PHP_IDE_CONFIG="serverName=application"

RUN phpenmod xdebug

# Installiamo composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# Installiamo symfony-cli
RUN wget https://get.symfony.com/cli/installer -O - | bash \
    && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

# Creiamo l'utente `user:user`
RUN mkdir /home/user && \
    groupadd -g $HOST_GID user && \
    useradd -u $HOST_UID --gid $HOST_GID -d /home/user -s /bin/bash user && \
#    usermod -a -G www-data user && \
    chown user /home/user

COPY --chown=user:user git/.gitconfig /home/user/
COPY --chown=user:user composer /home/user/.composer

ENV HISTFILE=/home/user/history/.bash_history
RUN mkdir /home/user/history && touch /home/user/history/.bash_history

COPY --chown=user:user bash/.bash_aliases /home/user/.bash_aliases
RUN echo '. ~/.bash_aliases' >> /home/user/.bashrc


RUN chown -R user:user /home/user

WORKDIR /var/www/project

#EXPOSE 9000

# Nel command impostiamo alcune variabili d'ambiente contenute in .env che prima non erano disponibili,
# aggiorniamo composer ed infine lanciamo php-fpm
CMD sed -i "s/{email}/${GIT_USER_EMAIL}/" /home/user/.gitconfig && \
    sed -i "s/{name}/${GIT_USER_NAME}/" /home/user/.gitconfig && \
    /usr/local/bin/composer self-update && \
    php-fpm8.3

