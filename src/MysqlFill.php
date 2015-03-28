<?php
date_default_timezone_set("UTC");

include_once __DIR__ . '/ConfigLoader.php';
include_once __DIR__ . '/RowProducer.php';
include_once __DIR__ . '/ValueGenerator.php';
include_once __DIR__ . '/ColumnStructure.php';
include_once __DIR__ . '/TableStructureFetcher.php';
include_once __DIR__ . '/OutputHandler.php';

class MySqlFill {
    private $db;

    private $configLoader;
    private $config;

    private $rowProducerFactory;
    private $rowProducer;

    private $tableStructureFetcherFactory;
    private $tableStructureFetcher;
    private $tableStructure; // TODO in the future, we want to be able to fill many tables with one call

    private $outputHandlerFactory;
    private $outputHandler;

    public function __construct(
        ConfigLoader $configLoader = null,
        RowProducerFactory $rowProducerFactory = null,
        TableStructureFetcherFactory $tableStructureFetcherFactory = null,
        OutputHandlerFactory $outputHandlerFactory = null
    ) {
        $this->configLoader = $configLoader ?: new ConcreteConfigLoader();
        $this->rowProducerFactory = $rowProducerFactory ?: new ConcreteRowProducerFactory();
        $this->tableStructureFetcherFactory = $tableStructureFetcherFactory ?: new ConcreteTableStructureFetcherFactory();
        $this->outputHandlerFactory = $outputHandlerFactory ?: new ConcreteOutputHandlerFactory();

        $this->config = $this->configLoader->load();
        $this->tableStructureFetcher = $this->tableStructureFetcherFactory->forConfig($this->config);
        $this->tableStructure = $this->tableStructureFetcher->fetch();
        $this->rowProducer = $this->rowProducerFactory->forTableStructure($this->config, $this->tableStructure);
        $this->outputHandler = $this->outputHandlerFactory->forConfig($this->config, $this->tableStructure, $this->tableStructureFetcher);
    }

    public function run() { // TODO let's check if the table is empty first; if it's not don't do it! Or truncate the table first.
        $this->outputHandler->before();
        for($i = 1; $i <= $this->config["rows_to_fill"]; $i++) { // TODO make this smarter; if an insert fails on a unique constraint, it will not fill all rows. Maybe COUNT(*) after a batch?
            $this->outputHandler->outputRow($this->rowProducer->produce());
        }
        $this->outputHandler->after();
    }
}
