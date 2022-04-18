# This is a collection of ChurchTools commandline scripts (CLI)

## Prerequisites

1. create a credential store

```bash
cp assets/CT-credentialstore.php.template private/CT-credentialstore.php
```

now edit `CT-credentialstore.php`. you see the placeholder which you should fill.



## usage

```
php bin/ctcli.php {showcase-glob}
```

where {showcase} matches a filename in folder `src`

If no showcase is specified as an argumnt, all showcases will be processed.

Please investigate the showcases to see the pattern applied:

* a showcase shall provide an array named `$report`. This array is stored
  in the `{showcase}.response.json`
* a showcase can provide any data of interest in this array.
* we advise to store the result in `$report['response]`
* we advise to place `responses` in a GIT of you own such that you can
  track changes.

every showcase produces at least the following file

* `workdir/{showcase}.response.json`

## using with docker

1. install docker on your machine
2. cd to the workdir of your choice

```
docker run -it -v `pwd`:/ctcli/workdir -v `pwd`/private:/ctcli/private bwl21/ctcli auth
```

Note that you need a credentials file in your `private` folder. If this file is missing, you get
a template for the file in stdout.

# available scripts

## churchauth.php

this generates reports about access rights in your instance. It furtheron creates plantuml and a graphml
files for your groups. Note that you need tools to visualize these files

* [plantuml](https://plantuml.com),
* [yed](https://yworks.com/products/yed)
* [yed-live](https://www.yworks.com/yed-live/)

### configuration

You can configure this script in the credentials file, thereby add grouptypes which you might ignore.
The grouptypes are numerical references to grouptype-ids in CT. Also see the grouptype definitons
provided in the results `{ctinstance)_churchauth.grouptypedefs.json`

