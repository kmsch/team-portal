<?php
// config.php

use TeamPortal\Gateways\JoomlaGateway;
use TeamPortal\UseCases\IJoomlaGateway;
use DI\Container;
use TeamPortal\Common\Database;
use TeamPortal\Gateways\BarcieGateway;
use TeamPortal\Gateways\NevoboGateway;
use TeamPortal\UseCases\IBarcieGateway;
use TeamPortal\UseCases\INevoboGateway;

return [
    IJoomlaGateway::class => DI\factory(function () {
        return new JoomlaGateway();
    }),
    IBarcieGateway::class => DI\factory(function (Container $container) {
        $database = $container->get(Database::class);
        return new BarcieGateway($database);
    }),
    INevoboGateway::class => DI\factory(function () {
        return new NevoboGateway();
    })
];
