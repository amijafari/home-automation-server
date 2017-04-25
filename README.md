# Home Automation Server (IR Transmitter)
A simple home automation project to transmit IR remote controler signals using [Raspberry PI](https://www.raspberrypi.org/). This project configured to transmit my air conditioner signals so I can control air conditioner (or any appliance have a IR remote control) through a nice web interface from anywhere. I used [Lirc](http://www.lirc.org) module to transmit signals, Apache and PHP for web interface.

## Setup

### Raspberry PI Configuration (Lirc, Apache, PHP)
Install and configure Lirc.
```
$ sudo apt-get install lirc
```

Add following lines to `/etc/modules` file:
```
lirc_dev
lirc_rpi gpio_in_pin=2 gpio_out_pin=22
```

Edit `/etc/lirc/hardware.conf` like below:
```
DRIVER="default"
DEVICE="/dev/lirc0"
MODULES="lirc_rpi"
```

Reboot to load module.
```
$ sudo reboot
```

Add following line to `/boot/config.txt` file:
```
dtoverlay=lirc-rpi,gpio_in_pin=2,gpio_out_pin=22,gpio_in_pull=up
```

Add following line to `/etc/lirc/lircd.conf` file in order to load the our custom remote configuration:
```
include "/var/www/html/ac/action/atp_ac.conf"
```

Install Apache and PHP so we could have a web interface:
```
$ sudo apt-get install apache2 php5 libapache2-mod-php5
$ sudo service apache2 restart
```

We should permit Apache to run `sudo` command without password. Use `sudo visudo` and add following line:
```
www-data ALL=(ALL) NOPASSWD:ALL
```

### IR Transmitter Circuit
[View IR transmitter circuit desing live preview](https://circuits.io/circuits/3359340-ir-sender)
![IR Transmitter circuit](https://github.com/amijafari/home-automation-server/blob/master/circuits/ir-sender.png?raw=true)