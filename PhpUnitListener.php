<?php

// docker run -it --rm -v `pwd`:/mnt/tmp -w /mnt/tmp modera/php7 bash -c "vendor/bin/phpunit -d mtr_functional=1"

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class PhpUnitListener extends \PHPUnit_Framework_BaseTestListener
{
    /**
     * @var string
     */
    private $activeRootDir = null;

    private $envVarsToClean = [];

//    /**
//     * @var string[]
//     */
//    private $envVars = ['KERNEL_DIR', 'db_host', 'db_port', 'db_user', 'db_password'];

    private $isFunctional = false;

    /**
     * @var mysqli
     */
    private $db;

    public function __construct()
    {
        global $argv;

        $this->isFunctional = false !== array_search('mtr_functional=1', $argv);
    }

    // override
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        if (class_exists($suite->getName())) {
            $reflClass = new ReflectionClass($suite->getName());

            $rootDir = null;
            $composerJson = array();

            $path = explode(DIRECTORY_SEPARATOR, $reflClass->getFileName());
            for ($i=count($path); $i>=-1; $i--) {
                $currentRootDir = implode(DIRECTORY_SEPARATOR, array_slice($path, 0, $i));
                if (file_exists($currentRootDir.'/composer.json')) {
                    $rootDir = $currentRootDir;
                    $composerJson = json_decode(file_get_contents($currentRootDir.'/composer.json'), true);

                    break;
                }
            }

            if ($this->activeRootDir && $rootDir !== $this->activeRootDir) {
                $this->onLeave($this->activeRootDir, $composerJson);

                $this->onEnter($rootDir, $composerJson);
            } elseif (!$this->activeRootDir) {
                $this->onEnter($rootDir, $composerJson);
            }

            $this->activeRootDir = $rootDir;
        }
    }

    private function onEnter($dir, array $composerJson)
    {
        $phpUnitXmlPath = $dir.'/phpunit.xml.dist';
        if (!file_exists($phpUnitXmlPath)) {
            return;
        }

        $this->envVarsToClean = [];

        // setting env variables
        $xml = new \SimpleXMLElement(file_get_contents($phpUnitXmlPath));
        foreach ($xml as $child) {
            /* @var \SimpleXMLElement $child */

            if ($child->getName() == 'php') {
                foreach ($child->children() as $phpChild) {
                    /* @var \SimpleXMLElement $phpChild */
                    if ($phpChild->getName() == 'server') {
                        $attrs = array();
                        foreach ($phpChild->attributes() as $name=>$value) {
                            $attrs[$name] = (string)$value;
                        }

                        if (isset($attrs['name']) && isset($attrs['value'])) {
                            // transforming paths like "./Tests/Fixtures/App/app" to "path-to-bundle/Tests/Fixtures/App/app"
                            if ('KERNEL_DIR' == $attrs['name']) {
                                if (substr($attrs['value'], 0, strlen('./')) == './') {
                                    $attrs['value'] = substr($attrs['value'], strlen('./'));
                                }

                                $attrs['value'] = $dir.DIRECTORY_SEPARATOR.$attrs['value'];
                            }

                            $this->envVarsToClean[] = $attrs['name'];

                            $_SERVER[$attrs['name']] = $attrs['value'];
                        }
                    }
                }
            }
        }

        if ($this->isFunctional) {
            mysqli_report(MYSQLI_REPORT_STRICT);

            $config = array(
                'db_host' => 'mysql',
                'db_port' => 3306,
                'db_user' => 'root',
                'db_password' => 123123,
                'db_attempts' => 40
            );
            foreach ($_SERVER as $key=>$value) {
                if (isset($config[$key])) {
                    $config[$key] = $value;
                }
            }

            foreach ($config as $key=>$value) {
                $_SERVER['SYMFONY__'.$key] = $value;
            }

            $this->db = $this->connectToDb($config);
            $this->db->query("CREATE DATABASE ".$this->formatTableName($composerJson['name']));
        }
    }

    private function connectToDb(array $config, $currentAttempt = 0) {
        try {
            return new mysqli($config['db_host'], $config['db_user'], $config['db_password']);
        } catch (\Exception $e) {
            if ($currentAttempt < $config['db_attempts']) {
                if (0 == $currentAttempt) {
                    echo "Waiting for MySQL to become available: ";
                }
                echo ".";

                sleep(1);

                return $this->connectToDb($config, 1+$currentAttempt);
            } else {
                echo sprintf("\nUnable to connect to database: %s\n", $e->getMessage());

                exit(2);
            }
        }
    }

    private function onLeave($dir, array $composerJson)
    {
        if ($this->isFunctional && $this->db) {
            $this->db->query("DROP DATABASE ".$this->formatTableName($composerJson['name']));
        }

        foreach ($this->envVarsToClean as $name) {
            if (isset($_SERVER[$name])) {
                unset($_SERVER[$name]);
            }
        }
    }

    /**
     * @param string $packageName
     *
     * @return string
     */
    private function formatTableName($packageName)
    {
        $segments = [];
        if (strpos($packageName, '/')) {
            list ($vendor, $packageName) = explode('/', $packageName);

            $segments = array_merge([$vendor], explode('-', $packageName));
        } else {
            $segments = explode('-', $packageName);
        }

        $tableName = implode('_', $segments);

        return $tableName;
    }
}
