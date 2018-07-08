FROM alpine
RUN apk add php-cli php-curl php-json unzip
ADD https://github.com/just-containers/s6-overlay/releases/download/v1.21.4.0/s6-overlay-amd64.tar.gz /tmp/
RUN tar xzf /tmp/s6-overlay-amd64.tar.gz -C /
ADD https://github.com/Netatmo/Netatmo-API-PHP/archive/master.zip /tmp/
RUN unzip /tmp/master.zip -d /
COPY cron.sh /
COPY my_weather_docker.php /
RUN echo "*/5 * * * * /usr/bin/php /my_weather_docker.php" > /etc/crontabs/root
RUN rm /tmp/master.zip /tmp/s6-overlay-amd64.tar.gz
ENTRYPOINT ["/init"]
CMD ["/cron.sh"]
