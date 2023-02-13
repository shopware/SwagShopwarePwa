<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\Bundle;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin\PluginCollection;
use Shopware\Core\Framework\Plugin\PluginEntity;
use Shopware\Core\Kernel;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use SwagShopwarePwa\Pwa\Bundle\Helper\FormattingHelper;

class ConfigurationService
{
    /**
     * @var string
     */
    private $artifactPath = 'pwa-bundles.json';

    /**
     * @var Kernel
     */
    private $kernel;
    /**
     * @var EntityRepository
     */
    private $pluginRepository;

    /**
     * @var SystemConfigService
     */
    private $configService;

    /**
     * @var FormattingHelper
     */
    private $helper;

    /**
     * @var FilesystemOperator
     */
    private $fileSystem;

    public function __construct(
        Kernel $kernel,
        EntityRepository $pluginRepository,
        SystemConfigService $configService,
        FormattingHelper $helper,
        FilesystemOperator $fileSystem
    ) {
        $this->kernel = $kernel;
        $this->pluginRepository = $pluginRepository;
        $this->configService = $configService;
        $this->helper = $helper;
        $this->fileSystem = $fileSystem;
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getBundleConfig(): array {
        return $this->getInfo();
    }

    /**
     * @deprecated will be removed with version 0.4.0 - pleas use bundleConfig() instead.
     * // TODO: Remove with 0.4
     */
    public function dumpBundles(): string
    {
        $bundleInformationSerialized = json_encode($this->getInfo(), JSON_PRETTY_PRINT);

        return $this->writeToPublicDirectory($bundleInformationSerialized, md5($bundleInformationSerialized));
    }

    /**
     * @return array<int|string, mixed>
     */
    private function getInfo(): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', true));

        /** @var PluginCollection $plugins */
        $plugins = $this->pluginRepository->search($criteria, Context::createDefaultContext());

        /** @var PluginEntity[] $pluginsAssoc */
        $pluginsAssoc = [];

        /** @var PluginEntity $plugin */
        foreach($plugins as $plugin)
        {
            $pluginsAssoc[$plugin->getName()] = $plugin;
        }

        $kernelBundles = $this->kernel->getBundles();
        $bundleInfos = [];
        $pluginConfigurations = $this->getPluginConfigurations();

        foreach($kernelBundles as $kernelBundle)
        {
            if(!key_exists($kernelBundle->getName(), $pluginsAssoc)) {
                continue;
            }

            $currentPlugin = $pluginsAssoc[$kernelBundle->getName()];

            $configuration = [];

            if(array_key_exists($kernelBundle->getName(), $pluginConfigurations))
            {
                $configuration = $pluginConfigurations[$kernelBundle->getName()];
            }

            $bundleInfos[$this->helper->convertToDashCase($kernelBundle->getName())] = [
                'configuration' => $configuration,
                'installedAt' => date('Y-m-d H:i:s', $currentPlugin->getInstalledAt()->getTimestamp()),
                'version' => $currentPlugin->getVersion()
            ];
        }

        return $bundleInfos;
    }

    /**
     * @return array<mixed>
     */
    private function getPluginConfigurations(): array
    {
        return $this->configService->all();
    }

    /**
     * TODO: Remove with 0.4
     */
    private function writeToPublicDirectory(string $content, string $checksum): string
    {
        try {
            $this->fileSystem->createDirectory('pwa');

            $output = $checksum ?? 'pwa_bundles';

            $outputPath = 'pwa/' . $output  . '.json';

            $this->fileSystem->delete($outputPath);

            $this->fileSystem->write($outputPath, $content);
        } catch (FilesystemException $e)
        {
            // Catch gracefully
            $outputPath = '';
        }


        return $outputPath;
    }
}
