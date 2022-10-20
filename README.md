# Course Module type plugin for create Collaborative Activities

### EN
This plugin that allows you to create several collaborative activities and that are automatically assigned in groups based on the answers of the students.

### ES
Este plugin que permite crear varias actividades colaborativas y que se asignan automáticamente en grupos en función de las respuestas de los alumnos.

## Compatibility

This plugin version is tested for:

* Moodle 4.0 (Build: 20220419) - 2022041900.00


## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/mod/tipcoll

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## Configuration

Go to the URL:

    {your/moodle/dirroot}/admin/settings.php?section=modsettingtipcoll

  * 
