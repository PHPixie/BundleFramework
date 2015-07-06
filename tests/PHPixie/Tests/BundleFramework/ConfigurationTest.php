<?php

namespace PHPixie\Tests\BundleFramework;

/**
 * @coversDefaultClass \PHPixie\BundleFramework\Configuration
 */
class ConfigurationTest extends \PHPixie\Test\Testcase
{
    protected $builder;
    protected $configuration;
    
    protected $assets;
    protected $components;
    
    protected $configStorage;
    
    public function setUp()
    {
        $this->builder = $this->builder();
        $this->configuration = $this->configurationMock();
        
        $this->assets = $this->assets();
        $this->method($this->builder, 'assets', $this->assets, array());
        
        $this->configStorage = $this->quickMock('\PHPixie\Config\Storages\Storage');
        $this->method($this->assets, 'configStorage', $this->configStorage, array());
        
        $this->components = $this->components();
        $this->method($this->builder, 'components', $this->components, array());
    }
    
    /**
     * @covers ::__construct
     * @covers \PHPixie\BundleFramework\Configuration::__construct
     * @covers ::<protected>
     */
    public function testConstruct()
    {
    
    }
    
    /**
     * @covers ::databaseConfig
     * @covers ::<protected>
     */
    public function testDatabaseConfig()
    {
        $this->configSliceTest('database');
    }
    
    /**
     * @covers ::frameworkConfig
     * @covers ::<protected>
     */
    public function testfFrameworkConfig()
    {
        $this->configSliceTest('framework');
    }
    
    /**
     * @covers ::routeTranslatorConfig
     * @covers ::<protected>
     */
    public function testRouteTranslatorConfig()
    {
        $this->configSliceTest('routeTranslator', 'route');
    }
    
    /**
     * @covers ::templateConfig
     * @covers ::<protected>
     */
    public function testTemplateConfig()
    {
        $this->configSliceTest('template');
    }
    
    /**
     * @covers ::filesystemRoot
     * @covers ::<protected>
     */
    public function testFilesystemRoot()
    {
        $root = $this->quickMock('\PHPixie\Fielsystem\Root');
        $this->method($this->assets, 'root', $root, array(), 0);
        
        $this->assertSame($root, $this->configuration->filesystemRoot());
    }
    
    /**
     * @covers ::orm
     * @covers ::<protected>
     */
    public function testOrm()
    {
        $slice   = $this->prepareComponent('slice');
        $bundles = $this->prepareComponent('bundles');
        
        $bundlesOrm = $this->quickMock('\PHPixie\Bundles\ORM');
        $this->method($bundles, 'orm', $bundlesOrm, array());
        
        $orm = $this->configuration->orm();
        $this->assertInstance($orm, '\PHPixie\BundleFramework\Configuration\ORM', array(
            'slice'      => $slice,
            'bundlesOrm' => $bundlesOrm
        ));
        
        $this->assertSame($orm, $this->configuration->orm());
    }
    
    /**
     * @covers ::ormConfig
     * @covers ::<protected>
     */
    public function testOrmConfig()
    {
        $this->configuration = $this->configurationMock(array('orm'));
        $orm = $this->prepareConfigurationOrm();
        
        $config = $this->getSliceData();
        $this->method($orm, 'configData', $config, array(), 0);
        
        $this->assertSame($config, $this->configuration->ormConfig());
    }
    
    /**
     * @covers ::ormWrappers
     * @covers ::<protected>
     */
    public function testOrmWrappers()
    {
        $this->configuration = $this->configurationMock(array('orm'));
        $orm = $this->prepareConfigurationOrm();
        
        $wrappers = $this->quickMock('\PHPixie\ORM\Wrappers');
        $this->method($orm, 'wrappers', $wrappers, array(), 0);
        
        $this->assertSame($wrappers, $this->configuration->ormWrappers());
    }
    
    /**
     * @covers ::httpProcessor
     * @covers ::<protected>
     */
    public function testHttpProcessor()
    {
        $httpProcessors = $this->prepareComponent('httpProcessors');
        $bundles        = $this->prepareComponent('bundles');
        
        $registry = $this->quickMock('\PHPixie\Processors\Registry');
        $this->method($bundles, 'httpProcessors', $registry, array());
        
        $processor = $this->quickMock('\PHPixie\Processors\Processor');
        $this->method($httpProcessors, 'attributeRegistryDispatcher', $processor, array(
            $registry,
            'bundle'
        ));
        
        for($i=0; $i<2; $i++) {
            $this->assertSame($processor, $this->configuration->httpProcessor());
        }
    }
    
    /**
     * @covers ::routeResolver
     * @covers ::<protected>
     */
    public function testRouteResolver()
    {
        $route   = $this->prepareComponent('route');
        $bundles = $this->prepareComponent('bundles');
        
        $configData = $this->getSliceData();
        $this->method($this->configStorage, 'slice', $configData, array('route.resolver'), 0);
        
        $registry = $this->quickMock('\PHPixie\Route\Resolvers\Registry');
        $this->method($bundles, 'routeResolvers', $registry, array());
        
        $resolver = $this->quickMock('\PHPixie\Route\Resolvers\Resolver');
        $this->method($route, 'buildResolver', $resolver, array(
            $configData,
            $registry
        ));
        
        for($i=0; $i<2; $i++) {
            $this->assertSame($resolver, $this->configuration->routeResolver());
        }
    }
    
    /**
     * @covers ::templateLocator
     * @covers ::<protected>
     */
    public function testTemplateLocator()
    {
        $filesystem = $this->prepareComponent('filesystem');
        $bundles    = $this->prepareComponent('bundles');
        
        $configData = $this->getSliceData();
        $this->method($this->configStorage, 'slice', $configData, array('template.locator'), 0);
        
        $registry = $this->quickMock('\PHPixie\Filesystem\Locators\Registry');
        $this->method($bundles, 'templateLocators', $registry, array());
        
        $locator = $this->quickMock('\PHPixie\Filesystem\Locators\Locator');
        $this->method($filesystem, 'buildLocator', $locator, array(
            $configData,
            $registry
        ));
        
        for($i=0; $i<2; $i++) {
            $this->assertSame($locator, $this->configuration->templateLocator());
        }
    }
    
    protected function prepareConfigurationOrm()
    {
        $orm = $this->quickMock('\PHPixie\BundleFramework\Configuration\ORM');
        $this->method($this->configuration, 'orm', $orm, array(), 0);
        return $orm;
    }
    
    protected function configSliceTest($name, $key = null)
    {
        if($key === null) {
            $key = $name;
        }
        
        $slice = $this->getSliceData();
        $this->method($this->configStorage, 'slice', $slice, array($key), 0);
        
        $method = $name.'Config';
        for($i=0; $i<2; $i++) {
            $this->assertSame($slice, $this->configuration->$method());
        }
    }
    
    protected function prepareComponent($name)
    {
        if($name === 'httpProcessors') {
            $class = '\PHPixie\HTTPProcessors';
        }else{
            $class = '\PHPixie\\'.ucfirst($name);
        }
        
        $mock = $this->quickMock($class);
        $this->method($this->components, $name, $mock, array());
        return $mock;
    }
    
    protected function getSliceData()
    {
        return $this->quickMock('\PHPixie\Slice\Data');
    }
    
    protected function assets()
    {
        return $this->abstractMock('\PHPixie\BundleFramework\Assets');
    }
    
    protected function components()
    {
        return $this->abstractMock('\PHPixie\BundleFramework\Components');
    }
    
    protected function builder()
    {
        return $this->abstractMock('\PHPixie\BundleFramework\Builder');
    }
    
    protected function configurationMock($methods = null)
    {
        return $this->getMock(
            '\PHPixie\BundleFramework\Configuration',
            $methods,
            array($this->builder)
        );
    }
}