Nesatmo-s6-auto will allow you to store Netatmo Weather station metrics in any InfluxDB server.

### How does it works?

It pulls data from the Netatmo API using PHP ;
Change the data format so it can be used with InfluxDB ;
Send the data to InfluxDB server ;

### Other info

Based on Alpine so that the image is as light as possible.
Uses s6-overlays.
Automatic build support (work in progress).
Once the data is inside InfluxDB, it can be seen with Grafana (for example).

### How to use

1. Create a [developer app](https://dev.netatmo.com/myaccount/createanapp), this should give you a client_id and client_secret.
2. Use Docker environment variables (see example below) for your various credentials.

### Example

With docker-compose, you can use the below docker-compose.yml file :

```
version: '2'
services:
    nesatmo:
        container_name: nesatmo
        image: nesousx/nesatmo
        restart: unless-stopped
        environment:
          - NETATMO_CLIENT_ID=yourclientid
          - NETATMO_CLIENT_SECRET=yourclientsecret
          - NETATMO_CLIENT_USERNAME=your-netatmo-user
          - NETATMO_CLIENT_PASSWORD=your-netatmo-pass
          - INFLUX_URL_WITH_PORT=https://my.server.com:8086
          - INFLUX_DB=testdb
          - INFLUX_USER=testuser
          - INFLUX_PASS=testpass
```

### Grafana Dashboard

My dashboard can be found [here](https://gist.github.com/Nesousx/3941d33ee6c2282c29fa70e69c54fb1f).

Search and replace the following elements :

* "nesoweath" by your station's name ;
* "indoor" by your indoor's sensor's name ;
* "outdoor" by your outdoor's sensor's name.

### Known issues and limitations

For now, I am not pulling data from the "battery" sensor from the outdoor module.

### Want to contribute?

Feel free to email me if you want to contribute.

### Thanks

Big thanks to [phenxdesign](https://twitter.com/phenxdesign) for most of the code and the internet for the rest of it. :)
