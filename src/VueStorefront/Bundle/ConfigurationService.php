<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\Bundle;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin\PluginCollection;
use Shopware\Core\Framework\Plugin\PluginEntity;
use Shopware\Core\Kernel;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class ConfigurationService
{
    private $artifactPath = 'pwa-bundles.json';

    /**
     * @var Kernel
     */
    private $kernel;
    /**
     * @var EntityRepositoryInterface
     */
    private $pluginRepository;

    /**
     * @var SystemConfigService
     */
    private $configService;

    public function __construct(Kernel $kernel, EntityRepositoryInterface $pluginRepository, SystemConfigService $configService)
    {
        $this->kernel = $kernel;
        $this->pluginRepository = $pluginRepository;
        $this->configService = $configService;
    }

    public function dumpBundles(): string
    {
        $bundleInformation = $this->getInfo();

        $filePath = $this->kernel->getCacheDir() . '/../../' . $this->artifactPath;

        file_put_contents(
            $filePath,
            json_encode($bundleInformation, JSON_PRETTY_PRINT)
        );

        return $this->artifactPath;
    }

    private function getInfo()
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', true));

        /** @var PluginCollection $plugins */
        $plugins = $this->pluginRepository->search($criteria, Context::createDefaultContext());
        $pluginNames = $plugins->map(function (PluginEntity $plugin) {
            return $plugin->getName();
        });

        /** @var BundleInterface[] $kernelBundles */
        $kernelBundles = $this->kernel->getBundles();

        foreach($kernelBundles as $kernelBundle)
        {
            if(!in_array($kernelBundle->getName(), $pluginNames)) {
                continue;
            }

            $bundleInfos[$kernelBundle->getName()] = [
                'configuration' => $this->getPluginConfiguration($kernelBundle->getName())
            ];
        }

        return $bundleInfos;
    }

    private function getPluginConfiguration(string $pluginName)
    {
        return $this->configService->getDomain($pluginName);
    }
}
