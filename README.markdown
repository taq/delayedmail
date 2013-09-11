# Delayed Mail for PHP

This is a simple app for sending emails through PHP without blocking sending and
waiting answer from the SMTP server. It provides some classes as:

- `Message` to compose the message
- `Server` to connect to the SMTP server
- `Sender` to run and send the queued messages
- `Runner` to fire a Sender object

## How it works

First we need the server configurations. There is a sample file on the `test`
dir, called `delayedmail.ini`:

```
host = smtp.gmail.com
port = 587
user = taq
password = secret
path = /tmp/delayedmailtest
```

The only different parameter there is the `path` parameter. This is where the
mail files will be stored.

## Storing messages to send later

The data store used are just regular plain text files. They are stored on the
`path` configured above. On that dir there will be another two subdirs:

- `delivery` where the queued messages are
- `sent`, where the messages are moved *after* `Sender` send them.

## How to use it

### Queuing messages

Just include the `delayedmail.php` on your app, create a new `Server` object,
configure it, compose and queue a new message:

```
<?php
   include_once "delayedmail.php";

   $server = new DelayedMail\Server("myconfigs.ini");
   $msg    = new DelayedMail\Message();
   $msg->from("taq <eustaquiorangel@gmail.com>")->
           to("Eustaquio Rangel <taq@bluefish.com.br>")->
      subject("DelayedMail test!")->
         text("This is just\na test!");
   self::$server->push($msg);
?>
```

If you check the `delivery` dir now, there will be a file there with the message
contents.

### Running the runner

Just edit the `runner.php` file with the desired interval and configuration file
(usually the same config file as the server) and run it from the command line:

```
<?php
namespace DelayedMail;
include_once "delayedmail.php";

$sender = new Sender(5,"delayedmail.ini");
$sender->run();
?>
```

```
$ php runner.php
- initializing ...
- checking for files in /tmp/delayedmailtest/delivery ...
- no files found.
```
