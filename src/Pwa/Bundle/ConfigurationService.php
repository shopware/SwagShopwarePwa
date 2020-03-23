<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\Bundle;

use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
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
use SwagShopwarePwa\Pwa\Bundle\Helper\FormattingHelper;
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

    /**
     * @var FilesystemInterface
     */
    private $fileSystem;

    public function __construct(
        Kernel $kernel,
        EntityRepositoryInterface $pluginRepository,
        SystemConfigService $configService,
        FormattingHelper $helper,
        FilesystemInterface $fileSystem)
    {
        $this->kernel = $kernel;
        $this->pluginRepository = $pluginRepository;
        $this->configService = $configService;
        $this->helper = $helper;
        $this->fileSystem = $fileSystem;
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
        $bundleInformationSerialized = json_encode($this->getInfo(), JSON_PRETTY_PRINT);

        return $this->writeToPublicDirectory($bundleInformationSerialized, md5($bundleInformationSerialized));
    }

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

        /** @var BundleInterface[] $kernelBundles */
        $kernelBundles = $this->kernel->getBundles();

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

    private function getPluginConfigurations()
    {
        return $this->configService->all();
    }

    private function writeToPublicDirectory(string $content, string $checksum): string
    {
        $this->fileSystem->createDir('pwa');

        $output = $checksum ?? 'pwa_bundles';

        $outputPath = 'pwa/' . $output  . '.json';

        try {
            $this->fileSystem->delete($outputPath);
        } catch (FileNotFoundException $e)
        {
            // Catch gracefully
        }

        $this->fileSystem->write($outputPath, $content);

        return $outputPath;
    }
}
