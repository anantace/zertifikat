<?php

class ZertifikatConfigEntry extends \SimpleORMap
{
    protected static function configure($config = array())
    {
        $config['db_table'] = 'zertifikat_config';

        parent::configure($config);
    }
}
