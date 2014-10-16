# KillerBees #

## Introduction  ##

KillerBees is a stress test tool for web projects. It is ideal to test products or features before their release. It uses Amazon's computing cloud to simulate high concurrency by running parallel requests from many machines. The product/feature has to be accessible publicly.

## Prerequisites ##

Killerbees runs in Amazon's cloud so you need an AWS account to use it. We provide you with an AMI (OS image) that works seamlessly with KillerBees but you'll also have the oppotunity to change this.

KillerBees will automatically launch the number of Amazon instances you specify and distriute itself to them. To do this, you'll need to have a public-private key pair. To get this, login to the [AWS Console](https://console.aws.amazon.com/ "AWS console") go to the *EC2 Dashboard* and select *Key Pairs* from the menu on the left. Click *Create Key Pair* and give your new key pair a name. Your private key will be downloaded. Save it tot he machine you'll use to control KillerBees (Linux only), for example to `/home/your_user/test.pem`.

For KillerBees to work you'll need to have the public key too, which you don't get from Amazon. However, you can extract it from the private key:

    $ ssh-keygen -y -f /home/your_user/test.pem > /home/your_user/test.pem.pub

You'll also need to know your AWS access key and secret key. You can get these from the AWS console by clicking your account name in the top right corner, selecting *Security Credentials* then going to the *Access Keys* section.

With that you are ready to install KillerBees.

Note that by default you are [limited to 20 EC2 instances](http://aws.amazon.com/ec2/faqs/#How_many_instances_can_I_run_in_Amazon_EC2 "EC2 limits"). If you need more juice than this (test it first, 20 is more than you think!), you can [request more](https://aws.amazon.com/contact-us/ec2-request/ "Request to Increase Amazon EC2 Instance Limit").

## Installation ##

To install KillerBees you need [Composer](http://getcomposer.org/ "Composer"). If you don't have it yet, you can get it easily:

    $ curl -sS https://getcomposer.org/installer | php

...then download and install KillerBees:

    $ git clone [REPO_URL]
    $ cd killerbees/
    $ ../composer.phar install

## Configuration ##

### Amazon configuration ###

Once Composer installs the dependencies, you can start configuring it.

    $ bin/killerbees configure:amazon

Follow the instructions on the screen.

- *amazon_ami*: We recommend using the default AMI that we published, it works the best with KillerBees.

- *amazon_instance_type*: Micro instances (default) are just fine for this job and they are easy on your wallet.

- *amazon_security_group*: This sets the secority group (firewall settings) for your EC2 instance. If you leave it empty, default security group will be sued which is just fine for our purposes as it allows SSH (port 22).

- *amazon_key_name*: Name of the key pair you generated for EC2. In our example in _Prerequisites_ it's `test`. This key will be used to log into the created instances and run the attack.

- *amazon_private_key_filepath*: Full path to the private key you generated for EC2. In our example in _Prerequisites_ it's `/home/your_user/test.pem`.

- *amazon_public_key_filepath*: Full path to the public key you generated for EC2. In our example in _Prerequisites_ it's `/home/your_user/test.pem.pub`.

- *amazon_user*: It's the default user with SSH access to the machine. If you used our AMI, it's 'ec2-user'.

- *amazon_credentials.key*: Your Amazon AWS credentials key, see _Prerequisites_. Used to launch instances.

- *amazon_credentials.secret*: Your Amazon AWS secret key, see _Prerequisites_. Used to launch instances.

### URLs to attack ####

You need to create a file with the URLs to attack. The file format is YAML.

Here is a minimalistic example of the config:

    bootstrap_requests:

    requests:
        - method: GET
          url: http://www.example.com

This config will make KillerBees attack a single URL (using GET method) with no bootstrapping. Create this file and save it somewhere, for example under `/home/your_user/attack1.yml`.

## Usage ##

### Starting instances ###

The first step is to fire up the EC2 machines.

$ bin/killerbees ec2:run --instances 10

*Don't forget to terminate the instances once your test is done*, otherwise receiving your monthly AWS bill will be a disturbing experience.