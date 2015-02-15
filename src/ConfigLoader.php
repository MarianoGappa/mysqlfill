<?php

abstract class ConfigLoader {
    abstract public function __construct(
        ConfigFromArguments $configFromArguments = null,
        ConfigFromFile $configFromFile = null,
        DefaultConfig $defaultConfig = null,
        ConfigValidator $configValidator = null
        );

    abstract public function load($args = null, $configPath = null);
}

class ConcreteConfigLoader extends ConfigLoader {
    private $configFromArguments;
    private $configFromFile;
    private $defaultConfig;

    public function __construct(
        ConfigFromArguments $configFromArguments = null,
        ConfigFromFile $configFromFile = null,
        DefaultConfig $defaultConfig = null,
        ConfigValidator $configValidator = null
        ) {

        $this->configFromArguments  = $configFromArguments ?: new ConcreteConfigFromArguments();
        $this->configFromFile       = $configFromFile ?: new ConcreteConfigFromFile();
        $this->defaultConfig        = $defaultConfig ?: new ConcreteDefaultConfig();
        $this->configValidator      = $configValidator ?: new ConcreteConfigValidator();
    }

    public function load($args = null, $configPath = null) {
        global $argv;

        $args = $args ?: $argv;
        $configPath = $configPath ?: $this->defaultConfig->get()["config_path"];

        $defaultConfig          = $this->defaultConfig->get();
        $configFromFile         = $this->configFromFile->get($configPath);
        $configFromArguments    = $this->configFromArguments->get($args);

        $config = array_merge($defaultConfig, $configFromFile, $configFromArguments);

        $this->configValidator->validate($config);

        return $config;
    }
}

abstract class ConfigFromFile {
    abstract public function get($filename);
}

class ConcreteConfigFromFile extends ConfigFromFile {
    public function get($filename) {
        $config = @include $filename;

        if(!is_array($config) || !count($config) > 0) {
            $config = [];
        }

        return $config;
    }
}

abstract class DefaultConfig {
    abstract public function get();
}

class ConcreteDefaultConfig extends DefaultConfig {
    public function get() {
        return [
            // Where is the main configuration file?
            "config_path" => __DIR__ . "/../mysqlfill.conf", // TODO implement

            // (i.e. mysql -h ????)
            "hostname" => "localhost", // TODO implement

            // (i.e. mysql -u ????)
            "username" => "root", // TODO implement

            // (i.e. mysql -p ???? )
            "password" => "", // TODO implement

            // How many rows should mysqlfill create by default?
            "rows_to_fill" => 5, // TODO implement

            // if the table to fill is not empty, what should mysqlfill do? ["abort", "truncate", "append"]
            "on_table_not_empty" => "abort", // TODO implement

            // On string-based column types, should I try non-latin characters?
            "utf8" => false, // TODO implement

            // Should mysqlfill try to guess what the column contains and put data it thinks it'd fit on it?
            "predictive" => true, // TODO implement

            // MySQL Db configuration for running tests
            "tests" => [
                "hostname" => "localhost",
                "username" => "root",
                "password" => ""
            ]
        ];
    }
}

abstract class ConfigFromArguments {
    abstract public function get($args);
}

class ConcreteConfigFromArguments extends ConfigFromArguments {
    const HOSTNAME = "hostname";
    const USERNAME = "username";
    const PASSWORD = "password";
    const EXTRA = "extra";

    public function get($args) {
        if(count($args) <= 1)
            return [];

        $config = [];
        $extra = [];
        $awaiting = self::EXTRA;
        for($i = 1; $i < count($args); $i++) {
            switch($awaiting) {
                case self::HOSTNAME:
                    $config[self::HOSTNAME] = $args[$i];
                    $awaiting = self::EXTRA;
                    break;
                case self::USERNAME:
                    $config[self::USERNAME] = $args[$i];
                    $awaiting = self::EXTRA;
                    break;
                case self::PASSWORD:
                    $config[self::PASSWORD] = $args[$i];
                    $awaiting = self::EXTRA;
                    break;
                case self::EXTRA:
                    switch($args[$i]) {
                        case "-h":
                            $awaiting = self::HOSTNAME;
                            break;
                        case "-u":
                            $awaiting = self::USERNAME;
                            break;
                        case "-p":
                            $awaiting = self::PASSWORD;
                            break;
                        default:
                            $extra[] = $args[$i];
                    }
                    break;
            }
        }

        if(count($extra) == 1)
            $config["table_name"] = $extra[0];
        elseif(count($extra) >= 2) {
            $config["database_name"] = $extra[0];
            $config["table_name"] = $extra[1];
        }

        return $config;
    }
}

abstract class ConfigValidator {
    abstract public function validate($config);
}

class ConcreteConfigValidator extends ConfigValidator {
    public function validate($config) {
        if(!isset($config["database_name"]) || !$config["database_name"]) {
            die("Fatal: please specify the database name.\n");
        }

        if(!isset($config["hostname"]) || !$config["hostname"]) {
            die("Fatal: please specify the hostname.\n");
        }

        if(!isset($config["table_name"]) || !$config["table_name"]) {
            die("Fatal: please specify the table name.\n");
        }
    }
}

$configLoaderDir = __DIR__; // N.B. any other way to get absolute path from different file?
