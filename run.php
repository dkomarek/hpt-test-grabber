<?php

declare(strict_types=1);

use HPT\Czc\Grabber as CzcGrabber;
use HPT\Dispatcher;
use HPT\Output;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

require_once __DIR__ . "/vendor/autoload.php";

(new SingleCommandApplication())
    ->addArgument("file", InputArgument::REQUIRED, "Cesta k souboru")
    ->setCode(function (InputInterface $consoleInput, OutputInterface $consoleOutput) {
        // Tohle cele by se idealne resilo pres DI a konfiguraci services s injekci do commandu
        $grabber = new CzcGrabber([
            "base_url" => "https://www.czc.cz", // zakladni URL webu
            "search_url" => "/{PRODUCT_ID}/hledat", // relativni URL pro hledni produktu dle kodu
            "filters" => [
                "search_result_item" => "#tiles > .new-tile .tile-title > h5 > a", // cesta k odkazu na detail produktu
                "product_price" => ".pd-price-delivery .total-price .price-vatin", // cesta k cene produktu na jeho karte
                "product_code" => ".pd-next-in-category__item-value", // cesta ke kodu prodejce
            ]
        ]);
        $output = new Output();
        $dispatcher = new Dispatcher($grabber, $output);

        $file = $consoleInput->getArgument("file");

        $consoleOutput->writeln(
            $dispatcher->run($file)
        );
    })
    ->run();