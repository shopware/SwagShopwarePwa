<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\Bundle;

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
     * @param EntityRepository<PluginCollection> $pluginRepository
     */
    public function __construct(
        private readonly Kernel $kernel,
        private readonly EntityRepository $pluginRepository,
        private readonly SystemConfigService $configService,
        private readonly FormattingHelper $helper
    ) {
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getBundleConfig(): array {
        return $this->getInfo();
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
        foreach ($plugins as $plugin) {
            $pluginsAssoc[$plugin->getName()] = $plugin;
        }

        $kernelBundles = $this->kernel->getBundles();
        $bundleInfos = [];
        $pluginConfigurations = $this->getPluginConfigurations();

        foreach($kernelBundles as $kernelBundle)
        {
            if (!key_exists($kernelBundle->getName(), $pluginsAssoc)) {
                continue;
            }

            $currentPlugin = $pluginsAssoc[$kernelBundle->getName()];

            $configuration = [];

            if (array_key_exists($kernelBundle->getName(), $pluginConfigurations)) {
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
}
