# Home Automation Server (IR Transmitter)
A simple home automation project to transmit IR remote controler signals using Raspberry PI. This project configured to transmit my air conditioner signals.
I used [Lirc](http://www.lirc.org) module to transmit signals, Apache and PHP for web interface.

## Setup

### Raspberry PI Configuration (Lirc, Apache, PHP)
Install and configure Lirc.
```
$ sudo apt-get install lirc

$ sudo vi /etc/modules
  Add following lines:
  lirc_dev
  lirc_rpi gpio_in_pin=2 gpio_out_pin=22

$ sudo vi /etc/lirc/hardware.conf
  DRIVER="default"
  DEVICE="/dev/lirc0"
  MODULES="lirc_rpi"

$ sudo reboot

$ sudo vi /boot/config.txt
  dtoverlay=lirc-rpi,gpio_in_pin=18,gpio_out_pin=17,gpio_in_pull=up

$ vi /etc/lirc/lircd.conf
  Add line:
  include "/var/www/html/ac/action/atp_ac.conf"
```

Install Apache and PHP so we could have a web interface. We should permit Apache to run `sudo` command without password.
```
$ sudo apt-get install apache2 php5 libapache2-mod-php5
$ sudo service apache2 restart
$ sudo visudo
  www-data ALL=(ALL) NOPASSWD:ALL
```

### IR Transmitter Circuit
![IR Transmitter circuit](https://github.com/amijafari/home-automation-server/blob/master/circuits/ir-sender.png?raw=true)