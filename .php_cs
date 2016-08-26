<?php

return Symfony\CS\Config\Config::create()
    ->setUsingCache(true)
    ->finder(
        Symfony\CS\Finder::create()
            ->in('src')
            // otherwise DummyUser entity gets renamed to "entities", ain't right in this specific case
            ->notPath('Modera/ServerCrudBundle/Tests/Functional/entities.php')
    )
;