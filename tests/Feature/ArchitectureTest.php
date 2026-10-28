<?php

arch('el dominio nunca depende de la capa de presentacion Livewire ni Http')
    ->expect('App\Domain')
    ->not->toUse([
        'App\Livewire',
        'App\Http',
    ]);

arch('el dominio de reportes es de solo lectura y no invoca Actions de otros dominios')
    ->expect('App\Domain\Reports')
    ->not->toUse([
        'App\Domain\Catalog\Actions',
        'App\Domain\Inventory\Actions',
    ]);

arch('las Actions del catalogo no dependen del dominio de inventario ni de reportes')
    ->expect('App\Domain\Catalog\Actions')
    ->not->toUse([
        'App\Domain\Inventory',
        'App\Domain\Reports',
    ]);
