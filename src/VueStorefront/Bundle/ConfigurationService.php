<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\Bundle;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin\Event\PluginPostActivateEvent;
use Shopware\Core\Framework\Plugin\Event\PluginPostDeactivateEvent;
use Shopware\Core\Framework\Plugin\PluginCollection;
use Shopware\Core\Framework\Plugin\PluginEntity;
use Shopware\Core\Kernel;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use SwagVueStorefront\VueStorefront\Bundle\Helper\FormattingHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class ConfigurationService implements EventSubscriberInterface
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
     * @var EntityRepositoryInterface
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

    public function __construct(
        Kernel $kernel,
        EntityRepositoryInterface $pluginRepository,
        SystemConfigService $configService,
        FormattingHelper $helper)
    {
        $this->kernel = $kernel;
        $this->pluginRepository = $pluginRepository;
        $this->configService = $configService;
        $this->helper = $helper;
    }

    public static function getSubscribedEvents()
    {
        return [
            PluginPostActivateEvent::class => 'dumpBundles',
            PluginPostDeactivateEvent::class => 'dumpBundles'
        ];
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

        /** @var PluginEntity[] $pluginsAssoc */
        $pluginsAssoc = [];

        /** @var PluginEntity $plugin */
        foreach($plugins as $plugin)
        {
            $pluginsAssoc[$plugin->getName()] = $plugin;
        }

        /** @var BundleInterface[] $kernelBundles */
        $kernelBundles = $this->kernel->getBundles();

        foreach($kernelBundles as $kernelBundle)
        {
            if(!key_exists($kernelBundle->getName(), $pluginsAssoc)) {
                continue;
            }

            $currentPlugin = $pluginsAssoc[$kernelBundle->getName()];

            $bundleInfos[$this->helper->convertToDashCase($kernelBundle->getName())] = [
                'configuration' => $this->getPluginConfiguration($kernelBundle->getName()),
                'installedAt' => date('Y-m-d H:i:s', $currentPlugin->getInstalledAt()->getTimestamp()),
                'version' => $currentPlugin->getVersion()
            ];
        }

        return $bundleInfos;
    }

    private function getPluginConfiguration(string $pluginName)
    {
        return $this->configService->getDomain($pluginName);
    }
}
