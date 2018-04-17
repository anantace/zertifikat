<?php

class ZertifikatSentEntry extends \SimpleORMap
{
    protected static function configure($config = array())
    {
        $config['db_table'] = 'zertifikat_sent';

        parent::configure($config);
    }
}
