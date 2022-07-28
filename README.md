# Home Automation Server (IR Transmitter)
A simple home automation project to transmit IR remote controler signals using [Raspberry PI](https://www.raspberrypi.org/). This project configured to transmit my air conditioner signals so I can control air conditioner (or any appliance have a IR remote control) through a nice web interface from anywhere. I used [Lirc](http://www.lirc.org) module to transmit signals, Apache and PHP for web interface.

## Setup

:warning: I could not configure and get proper result in newer version of raspbian with kernel version > 4.14. But it fully tested with raspbian Jessie.

### Raspberry PI Configuration (Lirc, Apache, PHP)
Install and configure Lirc.
```
$ sudo apt-get update
$ sudo apt-get install lirc
```

Add following lines to `/etc/modules` file:
```
lirc_dev
lirc_rpi gpio_in_pin=2 gpio_out_pin=22
```

Edit `/etc/lirc/hardware.conf` like below (no need in raspbian Kernel >= 4.19):
```
LIRCD_ARGS="--uinput --listen"
LOAD_MODULES=true
DRIVER="default"
DEVICE="/dev/lirc0"
MODULES="lirc_rpi"
```

Update the following lines in `/etc/lirc/lirc_options.conf` (if file exists):
```
driver=default
device=/dev/lirc0
```

Reboot to load module.
```
$ sudo reboot
```

Add following line to `/boot/config.txt` file:
```
dtoverlay=lirc-rpi,gpio_in_pin=2,gpio_out_pin=22,gpio_in_pull=up
```

For newer version of raspbian (Kernel >= 4.19) add the following instead:
```
dtoverlay=gpio-ir,gpio_pin=2
dtoverlay=gpio-ir-tx,gpio_pin=22
```

Install Apache and PHP so we could have a web interface:
```
$ sudo apt-get install apache2 php libapache2-mod-php
$ sudo service apache2 restart
```

We should permit Apache to run `sudo` command without password. Execute `sudo visudo` command and add following line:
```
www-data ALL=(ALL) NOPASSWD:ALL
```

Add virtual host to apache with home directory as `/home/pi/public_html/`.<br />
Put the contents of the `web` directory of this repository to the `/home/pi/public_html/ac/` directory.

Create link to remote config file:
```
sudo ln -s /home/pi/public_html/ac/conf/atp_ac.conf /etc/lirc/lircd.conf.d/atp_ac.conf
```

Add write permissions to web server user:
```
chmod 775 /home/pi/public_html/ac/conf/atp_ac.conf
chgrp www-data /home/pi/public_html/ac/conf/atp_ac.conf
chmod 775 /home/pi/public_html/ac/action/STATE
chgrp www-data /home/pi/public_html/ac/action/STATE
```

### IR Transmitter Circuit
[View IR transmitter circuit desing live preview](https://circuits.io/circuits/3359340-ir-sender)
![IR Transmitter circuit](https://github.com/amijafari/home-automation-server/blob/master/circuits/ir-sender.png?raw=true)
