ReconDB
=======

Git-able database importer / exporter for PHP and MySQL

## Overview

ReconDB is a simple database web interface, used in confunction with Git to deploy databases across servers. The interface is stand-alone (for now) and compresses files exclusively to GZip (for now) and writes the archive to the webroot. This can then be versioned and then deployed across any infrastructure using Git.

Once deployed, the latest backup can be installed via the interface on that web server.

This is meant to be as seamless and simple as possible - bells and whistles and automagic happen in the logic. The configuration is as basic and automatic as possible, although I will inevitably need to advance the configuration. Suggestions welcome :)

## Installation

1. Clone the repo into your *AMP website's webroot - the folder name "ReconDB" is changable
2. (Run through usage steps - local)
3. Git commit on local
4. Git push local to master
5. Git pull master on remote
6. (Run through usage steps - remote)

## Usage

### Configuration

Configuration is a 2-step process which will require that particular server's database credentials, meaning it _should_ differ on each computer it's installed on.

1. It needs the local database server to connect to: host, port, username and password
2. It needs to know which database: it'll give you a list of accessible options

That's it.
But I smell "advanced configuration" in the air.

### Local

Locally, the purpose is generally to back up changes to make available remotely, however, in a development-sense it will also work perfectly for taking database snapshots at any point with roll-back and roll-forward capabilities.

1. There is only one function (for now) - backup - which should pop a GZipped archive in your backup folder, ready to be versioned & posted off to a remote server.

### Remote

On remote, the purpose is generally to update a staged server, live server (on launches, presumably, not every day) or synchronise any development changes across a development team, gain, please use outside the box.

1. Synchronised files should appear on the remote system still bearing the original IP address in order to tell them apart, as well as further metadata.
2. Create a local backup - if you want
3. Install the new database

## Alpha Testers and Developers

Well, this is a project in it's infancy, but I created it to solve a problem I have when deploying website or even going back to old ones after years. I can never find the f*^%ing passwords - SSH, account centers, control panels, FTP, databases, etc! - and the process is different with practically each-and-every server.

This is also angled squarely at being evolved into a module specifically for the SilverStripe CMS, as that is the framework I use. That doesn't stop it from being abstracted and used in any other suitable framework or web system of any kind. Suggestions welcome :)

## Roadmap

During alpha I will concerntrate on expanding the program and getting the GitHub repo ready for collaboration - wiki, tickets, people, etc. Subscribe if you'd like to stay in the loop.
